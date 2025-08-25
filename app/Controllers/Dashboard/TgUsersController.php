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
 * Контроллер для просмотра и управления пользователями Telegram.
 */
final class TgUsersController
{
    public function __construct(private PDO $db) {}

    /**
     * Отображает таблицу пользователей.
     */
    public function index(Req $req, Res $res): Res
    {
        $data = [
            'title' => 'Telegram Users',
        ];

        return View::render($res, 'dashboard/tg-users/index.php', $data, 'layouts/main.php');
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

        foreach (['is_premium', 'is_user_banned', 'is_bot_banned', 'is_subscribed'] as $flag) {
            if (($p[$flag] ?? '') !== '') {
                $conds[] = "$flag = :$flag";
                $params[$flag] = (int)$p[$flag];
            }
        }
        if (($p['language_code'] ?? '') !== '') {
            $conds[] = 'language_code = :language_code';
            $params['language_code'] = $p['language_code'];
        }
        $searchValue = $p['search']['value'] ?? '';
        if ($searchValue !== '') {
            $conds[] = '(
                CAST(user_id AS CHAR) LIKE :search OR
                username LIKE :search OR
                utm LIKE :search OR
                referral_code LIKE :search
            )';
            $params['search'] = '%' . $searchValue . '%';
        }
        $whereSql = $conds ? ('WHERE ' . implode(' AND ', $conds)) : '';

        $sql = "SELECT id, user_id, username, first_name, last_name, language_code, is_premium, is_user_banned, is_bot_banned, is_subscribed, utm, referral_code FROM telegram_users {$whereSql} ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $length, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $start, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM telegram_users {$whereSql}");
        foreach ($params as $key => $val) {
            $countStmt->bindValue(':' . $key, $val);
        }
        $countStmt->execute();
        $recordsFiltered = (int)$countStmt->fetchColumn();

        $recordsTotal = (int)$this->db->query('SELECT COUNT(*) FROM telegram_users')->fetchColumn();

        return Response::json($res, 200, [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    /**
     * Отображает карточку пользователя с последними сообщениями и обновлениями.
     */
    public function view(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT * FROM telegram_users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        if (!$user) {
            return $res->withStatus(404);
        }

        $msgStmt = $this->db->prepare('SELECT id, method, `type`, status, processed_at FROM telegram_messages WHERE user_id = :uid ORDER BY id DESC LIMIT 10');
        $msgStmt->execute(['uid' => $user['user_id']]);
        $messages = $msgStmt->fetchAll();

        $updStmt = $this->db->prepare('SELECT id, update_id, `type`, created_at FROM telegram_updates WHERE user_id = :uid ORDER BY id DESC LIMIT 10');
        $updStmt->execute(['uid' => $user['user_id']]);
        $updates = $updStmt->fetchAll();

        $data = [
            'title' => 'User ' . ($user['username'] ?: $user['user_id']),
            'user' => $user,
            'messages' => $messages,
            'updates' => $updates,
        ];

        return View::render($res, 'dashboard/tg-users/view.php', $data, 'layouts/main.php');
    }
}
