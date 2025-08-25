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
 * Контроллер для просмотра активных сессий.
 */
final class SessionsController
{
    public function __construct(private PDO $db) {}

    /**
     * Отображает таблицу сессий.
     */
    public function index(Req $req, Res $res): Res
    {
        $data = [
            'title' => 'Sessions',
        ];

        return View::render($res, 'dashboard/sessions/index.php', $data, 'layouts/main.php');
    }

    /**
     * Возвращает данные для DataTables с серверной пагинацией и фильтрами.
     */
    public function data(Req $req, Res $res): Res
    {
        $p = (array)$req->getParsedBody();
        $start  = max(0, (int)($p['start'] ?? 0));
        $length = (int)($p['length'] ?? 10);
        $draw   = (int)($p['draw'] ?? 0);
        if ($length === -1) {
            $start = 0;
        }

        $conds = [];
        $params = [];

        if (($p['state'] ?? '') !== '') {
            $conds[] = 'state = :state';
            $params['state'] = $p['state'];
        }
        if (($p['period'] ?? '') !== '') {
            $parts = explode(',', (string)$p['period']);
            if (count($parts) === 2) {
                $conds[] = 'updated_at BETWEEN :updated_from AND :updated_to';
                $params['updated_from'] = $parts[0];
                $params['updated_to'] = $parts[1];
            }
        }
        $searchValue = $p['search']['value'] ?? '';
        if ($searchValue !== '') {
            $conds[] = '(CAST(user_id AS CHAR) LIKE :search OR state LIKE :search)';
            $params['search'] = '%' . $searchValue . '%';
        }
        $whereSql = $conds ? ('WHERE ' . implode(' AND ', $conds)) : '';

        $sql = "SELECT user_id, state, created_at, updated_at FROM telegram_sessions {$whereSql} ORDER BY updated_at DESC";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        if ($length > 0) {
            $stmt->bindValue(':limit', $length, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $start, PDO::PARAM_INT);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM telegram_sessions {$whereSql}");
        foreach ($params as $key => $val) {
            $countStmt->bindValue(':' . $key, $val);
        }
        $countStmt->execute();
        $recordsFiltered = (int)$countStmt->fetchColumn();

        $recordsTotal = (int)$this->db->query('SELECT COUNT(*) FROM telegram_sessions')->fetchColumn();

        return Response::json($res, 200, [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }
}
