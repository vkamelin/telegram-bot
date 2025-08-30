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
    public function __construct(private PDO $db)
    {
    }

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
        $start = max(0, (int)($p['start'] ?? 0));
        $length = (int)($p['length'] ?? 10);
        $draw = (int)($p['draw'] ?? 0);
        if ($length === -1) {
            $start = 0;
        }

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

        $sql = "SELECT id, user_id, method, `type`, priority, send_after, status, selected_count, success_count, failed_count, created_at, started_at FROM telegram_scheduled_messages {$whereSql} ORDER BY id DESC";
        if ($length > 0) {
            $sql .= ' LIMIT :limit OFFSET :offset';
        }
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

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM telegram_scheduled_messages {$whereSql}");
        foreach ($params as $key => $val) {
            $countStmt->bindValue(':' . $key, $val);
        }
        $countStmt->execute();
        $recordsFiltered = (int)$countStmt->fetchColumn();

        $recordsTotal = (int)$this->db->query('SELECT COUNT(*) FROM telegram_scheduled_messages')->fetchColumn();

        return Response::json($res, 200, [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    /**
     * Детальная страница конкретной рассылки/отложенного сообщения.
     */
    public function show(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT id, user_id, method, `type`, priority, send_after, status, selected_count, success_count, failed_count, created_at, started_at FROM telegram_scheduled_messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return $res->withStatus(404);
        }

        return View::render($res, 'dashboard/scheduled/view.php', [
            'title' => 'Scheduled details',
            'item' => $row,
        ], 'layouts/main.php');
    }

    /**
     * Данные сообщений для DataTables по scheduled_id
     */
    public function messages(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $p = (array)$req->getParsedBody();
        $start = max(0, (int)($p['start'] ?? 0));
        $length = (int)($p['length'] ?? 10);
        $draw = (int)($p['draw'] ?? 0);
        if ($length === -1) {
            $start = 0;
        }

        $conds = ['scheduled_id = :sid'];
        $params = ['sid' => $id];

        if (($p['status'] ?? '') !== '') {
            $conds[] = 'status = :status';
            $params['status'] = $p['status'];
        }

        $whereSql = 'WHERE ' . implode(' AND ', $conds);

        $sql = "SELECT id, user_id, method, status, error, code, priority, message_id, processed_at FROM telegram_messages {$whereSql} ORDER BY id DESC";
        if ($length > 0) {
            $sql .= ' LIMIT :limit OFFSET :offset';
        }
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

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM telegram_messages {$whereSql}");
        foreach ($params as $key => $val) {
            $countStmt->bindValue(':' . $key, $val);
        }
        $countStmt->execute();
        $recordsFiltered = (int)$countStmt->fetchColumn();

        $recordsTotalStmt = $this->db->prepare('SELECT COUNT(*) FROM telegram_messages WHERE scheduled_id = :sid');
        $recordsTotalStmt->execute(['sid' => $id]);
        $recordsTotal = (int)$recordsTotalStmt->fetchColumn();

        return Response::json($res, 200, [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    /**
     * Показывает форму редактирования (только для не начатых).
     */
    public function edit(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT id, user_id, method, `type`, priority, send_after, status FROM telegram_scheduled_messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return $res->withStatus(404);
        }
        if ($row['status'] !== 'pending') {
            return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
        }
        return View::render($res, 'dashboard/scheduled/edit.php', [
            'title' => 'Edit scheduled',
            'item' => $row,
        ], 'layouts/main.php');
    }

    /**
     * Сохраняет изменения (только для не начатых).
     */
    public function update(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $p = (array)$req->getParsedBody();
        $priority = (int)($p['priority'] ?? 2);
        $sendAfter = trim((string)($p['send_after'] ?? ''));
        $errors = [];
        if ($sendAfter === '' || strtotime($sendAfter) === false) {
            $errors[] = 'send_after is invalid';
        }
        if ($errors) {
            $stmt = $this->db->prepare('SELECT id, user_id, method, `type`, priority, send_after, status FROM telegram_scheduled_messages WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            return View::render($res, 'dashboard/scheduled/edit.php', [
                'title' => 'Edit scheduled',
                'item' => array_merge($row ?: [], ['priority' => $priority, 'send_after' => $sendAfter]),
                'errors' => $errors,
            ], 'layouts/main.php');
        }
        $stmt = $this->db->prepare("UPDATE telegram_scheduled_messages SET priority = :priority, send_after = :send_after WHERE id = :id AND status = 'pending'");
        $stmt->execute(['priority' => $priority, 'send_after' => $sendAfter, 'id' => $id]);
        return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
    }

    /**
     * Отменяет отправку (только для не начатых): переводит в canceled.
     */
    public function cancel(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $stmt = $this->db->prepare("UPDATE telegram_scheduled_messages SET status = 'canceled', canceled_at = NOW() WHERE id = :id AND status = 'pending'");
        $stmt->execute(['id' => $id]);
        return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
    }

    /**
     * Немедленно отправляет запланированное сообщение.
     */
    public function sendNow(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        // Only for pending
        $lock = $this->db->prepare("UPDATE telegram_scheduled_messages SET status = 'processing', started_at = NOW() WHERE id = :id AND status = 'pending'");
        $lock->execute(['id' => $id]);

        if ($lock->rowCount() === 0) {
            return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
        }

        // Load scheduled record
        $select = $this->db->prepare('SELECT user_id, method, `type`, data, priority FROM telegram_scheduled_messages WHERE id = :id');
        $select->execute(['id' => $id]);
        $msg = $select->fetch();
        if (!$msg) {
            return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
        }

        // Decode payload and enqueue via Push helper
        try {
            $payload = json_decode((string)$msg['data'], true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            \App\Helpers\Logger::error('Invalid scheduled message payload', ['id' => $id, 'exception' => $e]);
            // rollback status so it can be retried
            $this->db->prepare("UPDATE telegram_scheduled_messages SET status = 'pending' WHERE id = :id")
                ->execute(['id' => $id]);
            return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
        }

        $ok = \App\Helpers\Push::custom(
            (string)$msg['method'],
            is_array($payload) ? $payload : [],
            isset($msg['user_id']) ? (int)$msg['user_id'] : null,
            (string)$msg['type'],
            (int)$msg['priority'],
            null
        );

        if (!$ok) {
            // Return to pending so a user can retry later
            $this->db->prepare("UPDATE telegram_scheduled_messages SET status = 'pending' WHERE id = :id")
                ->execute(['id' => $id]);
        }

        return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
    }

    /**
     * Удаляет запланированное сообщение.
     */
    public function delete(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        // Allow delete only if not started
        $stmt = $this->db->prepare("DELETE FROM telegram_scheduled_messages WHERE id = :id AND status = 'pending'");
        $stmt->execute(['id' => $id]);

        return $res->withHeader('Location', '/dashboard/scheduled')->withStatus(302);
    }
}
