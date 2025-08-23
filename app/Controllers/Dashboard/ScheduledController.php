<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use App\Helpers\Response;
use App\Helpers\View;
use PDO;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;

/**
 * Контроллер для просмотра и управления запланированными сообщениями.
 */
final class ScheduledController
{
    public function __construct(private PDO $pdo) {}

    /**
     * Отображает таблицу запланированных сообщений.
     */
    public function index(Req $req, Res $res): Res
    {
        $data = [
            'title' => 'Scheduled',
        ];

        return View::render($res, 'dashboard/scheduled/index.php', $data, 'layouts/main.php');
    }

    /**
     * Возвращает данные для DataTables с серверной пагинацией и фильтрами.
     */
    public function data(Req $req, Res $res): Res
    {
        $p = (array)$req->getParsedBody();
        $start  = max(0, (int)($p['start'] ?? 0));
        $length = max(10, (int)($p['length'] ?? 10));
        $draw   = (int)($p['draw'] ?? 0);

        $conds = [];
        $params = [];

        $state = $p['state'] ?? '';
        if ($state === 'due') {
            $conds[] = 'send_after <= NOW()';
        } elseif ($state === 'future') {
            $conds[] = 'send_after > NOW()';
        }
        if (($p['type'] ?? '') !== '') {
            $conds[] = '`type` = :type';
            $params['type'] = $p['type'];
        }
        if (($p['priority'] ?? '') !== '') {
            $conds[] = 'priority = :priority';
            $params['priority'] = (int)$p['priority'];
        }
        if (($p['user_id'] ?? '') !== '') {
            $conds[] = 'user_id = :user_id';
            $params['user_id'] = $p['user_id'];
        }
        if (($p['send_after_from'] ?? '') !== '') {
            $conds[] = 'send_after >= :send_after_from';
            $params['send_after_from'] = $p['send_after_from'];
        }
        if (($p['send_after_to'] ?? '') !== '') {
            $conds[] = 'send_after <= :send_after_to';
            $params['send_after_to'] = $p['send_after_to'];
        }

        $searchValue = $p['search']['value'] ?? '';
        if ($searchValue !== '') {
            $conds[] = '(CAST(id AS CHAR) LIKE :search OR CAST(user_id AS CHAR) LIKE :search OR method LIKE :search OR `type` LIKE :search)';
            $params['search'] = '%' . $searchValue . '%';
        }
        $whereSql = $conds ? ('WHERE ' . implode(' AND ', $conds)) : '';

        $sql = "SELECT id, user_id, method, `type`, priority, send_after, created_at FROM telegram_scheduled_messages {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $start, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $countStmt = $this->pdo->prepare("SELECT COUNT(*) FROM telegram_scheduled_messages {$whereSql}");
        foreach ($params as $key => $val) {
            $countStmt->bindValue(':' . $key, $val);
        }
        $countStmt->execute();
        $recordsFiltered = (int)$countStmt->fetchColumn();

        $recordsTotal = (int)$this->pdo->query('SELECT COUNT(*) FROM telegram_scheduled_messages')->fetchColumn();

        return Response::json($res, 200, [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    /**
     * Немедленно отправляет запланированное сообщение.
     */
    public function sendNow(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $insert = $this->pdo->prepare(
            'INSERT INTO telegram_messages (user_id, method, `type`, data, priority) ' .
            'SELECT user_id, method, `type`, data, priority FROM telegram_scheduled_messages WHERE id = :id'
        );
        $insert->execute(['id' => $id]);

        $del = $this->pdo->prepare('DELETE FROM telegram_scheduled_messages WHERE id = :id');
        $del->execute(['id' => $id]);

        return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
    }

    /**
     * Удаляет запланированное сообщение.
     */
    public function delete(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $stmt = $this->pdo->prepare('DELETE FROM telegram_scheduled_messages WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
    }
}
