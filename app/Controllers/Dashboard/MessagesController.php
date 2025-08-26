<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Dashboard;

use App\Helpers\Flash;
use App\Helpers\Push;
use App\Helpers\MediaBuilder;
use App\Helpers\Response;
use App\Helpers\View;
use PDO;
use Psr\Http\Message\ResponseInterface as Res;
use Psr\Http\Message\ServerRequestInterface as Req;

/**
 * Контроллер для просмотра и управления отправленными сообщениями.
 */
final class MessagesController
{
    public function __construct(private PDO $db) {}

    /**
     * Отображает таблицу сообщений.
     */
    public function index(Req $req, Res $res): Res
    {
        $data = [
            'title' => 'Messages',
        ];

        return View::render($res, 'dashboard/messages/index.php', $data, 'layouts/main.php');
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

        if (($p['status'] ?? '') !== '') {
            $conds[] = 'status = :status';
            $params['status'] = $p['status'];
        }
        if (($p['method'] ?? '') !== '') {
            $conds[] = 'method = :method';
            $params['method'] = $p['method'];
        }
        if (($p['type'] ?? '') !== '') {
            $conds[] = '`type` = :type';
            $params['type'] = $p['type'];
        }
        if (($p['user_id'] ?? '') !== '') {
            $conds[] = 'user_id = :user_id';
            $params['user_id'] = $p['user_id'];
        }
        if (($p['priority'] ?? '') !== '') {
            $conds[] = 'priority = :priority';
            $params['priority'] = (int)$p['priority'];
        }
        if (($p['processed_from'] ?? '') !== '') {
            $conds[] = 'processed_at >= :processed_from';
            $params['processed_from'] = $p['processed_from'];
        }
        if (($p['processed_to'] ?? '') !== '') {
            $conds[] = 'processed_at <= :processed_to';
            $params['processed_to'] = $p['processed_to'];
        }
        $searchValue = $p['search']['value'] ?? '';
        if ($searchValue !== '') {
            $conds[] = '(CAST(id AS CHAR) LIKE :search OR CAST(user_id AS CHAR) LIKE :search OR method LIKE :search OR `type` LIKE :search OR status LIKE :search OR error LIKE :search OR CAST(code AS CHAR) LIKE :search)';
            $params['search'] = '%' . $searchValue . '%';
        }
        $whereSql = $conds ? ('WHERE ' . implode(' AND ', $conds)) : '';

        $sql = "SELECT id, user_id, method, `type`, status, priority, error, code, processed_at FROM telegram_messages {$whereSql} ORDER BY id DESC";
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

        $recordsTotal = (int)$this->db->query('SELECT COUNT(*) FROM telegram_messages')->fetchColumn();

        return Response::json($res, 200, [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $rows,
        ]);
    }

    /**
     * Создаёт копию указанной записи.
     */
    public function resend(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $stmt = $this->db->prepare("INSERT INTO telegram_messages (user_id, method, `type`, data, priority) SELECT user_id, method, `type`, data, priority FROM telegram_messages WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $res->withHeader('Location', '/dashboard/messages')->withStatus(302);
    }

    /**
     * Отдаёт содержимое поля response как файл JSON.
     */
    public function download(Req $req, Res $res, array $args): Res
    {
        $id = (int)($args['id'] ?? 0);
        $stmt = $this->db->prepare('SELECT response FROM telegram_messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if (!$row || $row['response'] === null) {
            return $res->withStatus(404);
        }
        $filename = "response_{$id}.json";
        $res->getBody()->write((string)$row['response']);
        return $res->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Shows form for sending new message.
     */
    public function create(Req $req, Res $res): Res
    {
        $groups = $this->db->query('SELECT id,name FROM telegram_user_groups ORDER BY name')->fetchAll();
        $params = [
            'title' => 'Send message',
            'groups' => $groups,
            'errors' => [],
            'data' => [],
        ];
        return View::render($res, 'dashboard/messages/create.php', $params, 'layouts/main.php');
    }

    /**
     * Sends text message to selected users.
     */
    public function send(Req $req, Res $res): Res
    {
        $p = (array)$req->getParsedBody();
        $type = (string)($p['type'] ?? 'text');
        $mode = (string)($p['mode'] ?? '');

        $data = [
            'type' => $type,
            'text' => trim((string)($p['text'] ?? '')),
            'caption' => trim((string)($p['caption'] ?? '')),
            'parse_mode' => trim((string)($p['parse_mode'] ?? '')),
            'has_spoiler' => !empty($p['has_spoiler']),
            'duration' => trim((string)($p['duration'] ?? '')),
            'performer' => trim((string)($p['performer'] ?? '')),
            'title' => trim((string)($p['title'] ?? '')),
            'width' => trim((string)($p['width'] ?? '')),
            'height' => trim((string)($p['height'] ?? '')),
            'length' => trim((string)($p['length'] ?? '')),
            'mode' => $mode,
            'user' => trim((string)($p['user'] ?? '')),
            'users' => $p['users'] ?? [],
            'group_id' => $p['group_id'] ?? '',
        ];

        $errors = [];
        $sender = null;

        switch ($type) {
            case 'text':
                $text = $data['text'];
                if ($text === '') {
                    $errors[] = 'text is required';
                }
                $sender = static fn(int $cid) => Push::text($cid, $text);
                break;
            case 'photo':
                $file = $_FILES['photo']['tmp_name'] ?? '';
                if ($file === '') {
                    $errors[] = 'photo is required';
                    break;
                }
                $options = [];
                if ($data['parse_mode'] !== '') {
                    $options['parse_mode'] = $data['parse_mode'];
                }
                if ($data['has_spoiler']) {
                    $options['has_spoiler'] = true;
                }
                $caption = $data['caption'];
                $sender = static fn(int $cid) => Push::photo($cid, $file, $caption, 'photo', 2, $options);
                break;
            case 'audio':
                $file = $_FILES['audio']['tmp_name'] ?? '';
                if ($file === '') {
                    $errors[] = 'audio is required';
                    break;
                }
                $options = [];
                if ($data['parse_mode'] !== '') {
                    $options['parse_mode'] = $data['parse_mode'];
                }
                if ($data['duration'] !== '') {
                    $options['duration'] = (int)$data['duration'];
                }
                if ($data['performer'] !== '') {
                    $options['performer'] = $data['performer'];
                }
                if ($data['title'] !== '') {
                    $options['title'] = $data['title'];
                }
                $caption = $data['caption'];
                $sender = static fn(int $cid) => Push::audio($cid, $file, $caption, 'audio', 2, $options);
                break;
            case 'video':
                $file = $_FILES['video']['tmp_name'] ?? '';
                if ($file === '') {
                    $errors[] = 'video is required';
                    break;
                }
                $options = [];
                if ($data['parse_mode'] !== '') {
                    $options['parse_mode'] = $data['parse_mode'];
                }
                if ($data['width'] !== '') {
                    $options['width'] = (int)$data['width'];
                }
                if ($data['height'] !== '') {
                    $options['height'] = (int)$data['height'];
                }
                if ($data['duration'] !== '') {
                    $options['duration'] = (int)$data['duration'];
                }
                if ($data['has_spoiler']) {
                    $options['has_spoiler'] = true;
                }
                $caption = $data['caption'];
                $sender = static fn(int $cid) => Push::video($cid, $file, $caption, 'video', 2, $options);
                break;
            case 'document':
                $file = $_FILES['document']['tmp_name'] ?? '';
                if ($file === '') {
                    $errors[] = 'document is required';
                    break;
                }
                $options = [];
                if ($data['parse_mode'] !== '') {
                    $options['parse_mode'] = $data['parse_mode'];
                }
                $caption = $data['caption'];
                $sender = static fn(int $cid) => Push::document($cid, $file, $caption, 'document', 2, $options);
                break;
            case 'sticker':
                $file = $_FILES['sticker']['tmp_name'] ?? '';
                if ($file === '') {
                    $errors[] = 'sticker is required';
                    break;
                }
                $sender = static fn(int $cid) => Push::sticker($cid, $file);
                break;
            case 'animation':
                $file = $_FILES['animation']['tmp_name'] ?? '';
                if ($file === '') {
                    $errors[] = 'animation is required';
                    break;
                }
                $options = [];
                if ($data['parse_mode'] !== '') {
                    $options['parse_mode'] = $data['parse_mode'];
                }
                if ($data['width'] !== '') {
                    $options['width'] = (int)$data['width'];
                }
                if ($data['height'] !== '') {
                    $options['height'] = (int)$data['height'];
                }
                if ($data['duration'] !== '') {
                    $options['duration'] = (int)$data['duration'];
                }
                if ($data['has_spoiler']) {
                    $options['has_spoiler'] = true;
                }
                $caption = $data['caption'];
                $sender = static fn(int $cid) => Push::animation($cid, $file, $caption, 'animation', 2, $options);
                break;
            case 'voice':
                $file = $_FILES['voice']['tmp_name'] ?? '';
                if ($file === '') {
                    $errors[] = 'voice is required';
                    break;
                }
                $options = [];
                if ($data['caption'] !== '') {
                    $options['caption'] = $data['caption'];
                }
                if ($data['parse_mode'] !== '') {
                    $options['parse_mode'] = $data['parse_mode'];
                }
                if ($data['duration'] !== '') {
                    $options['duration'] = (int)$data['duration'];
                }
                $sender = static fn(int $cid) => Push::voice($cid, $file, 'voice', 2, $options);
                break;
            case 'video_note':
                $file = $_FILES['video_note']['tmp_name'] ?? '';
                if ($file === '') {
                    $errors[] = 'video_note is required';
                    break;
                }
                $options = [];
                if ($data['length'] !== '') {
                    $options['length'] = (int)$data['length'];
                }
                if ($data['duration'] !== '') {
                    $options['duration'] = (int)$data['duration'];
                }
                $sender = static fn(int $cid) => Push::videoNote($cid, $file, 'video-note', 2, $options);
                break;
            case 'media_group':
                $uploads = $_FILES['media'] ?? null;
                $media = [];
                if (is_array($uploads) && isset($uploads['tmp_name']) && is_array($uploads['tmp_name'])) {
                    $caption = $data['caption'];
                    $parseMode = $data['parse_mode'];
                    foreach ($uploads['tmp_name'] as $idx => $tmp) {
                        if (($uploads['error'][$idx] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || $tmp === '') {
                            continue;
                        }
                        $opts = [];
                        if ($idx === 0 && $caption !== '') {
                            $opts['caption'] = $caption;
                            if ($parseMode !== '') {
                                $opts['parse_mode'] = $parseMode;
                            }
                        }
                        $media[] = MediaBuilder::buildInputMedia('photo', $tmp, $opts);
                    }
                }
                if (!$media) {
                    $errors[] = 'media is required';
                    break;
                }
                $sender = static fn(int $cid) => Push::mediaGroup($cid, $media);
                break;
            default:
                $errors[] = 'unknown message type';
        }

        if (!in_array($mode, ['all', 'single', 'selected', 'group'], true)) {
            $errors[] = 'mode is invalid';
        }

        $chatIds = [];
        if (!$errors) {
            switch ($mode) {
                case 'all':
                    $chatIds = $this->db->query('SELECT user_id FROM telegram_users')->fetchAll(PDO::FETCH_COLUMN) ?: [];
                    $chatIds = array_map('intval', $chatIds);
                    break;
                case 'single':
                    $q = $data['user'];
                    if ($q === '') {
                        $errors[] = 'user is required';
                        break;
                    }
                    if (ctype_digit($q)) {
                        $stmt = $this->db->prepare('SELECT user_id FROM telegram_users WHERE user_id = :uid');
                        $stmt->execute(['uid' => (int)$q]);
                    } else {
                        $stmt = $this->db->prepare('SELECT user_id FROM telegram_users WHERE username = :uname');
                        $stmt->execute(['uname' => $q]);
                    }
                    $id = $stmt->fetchColumn();
                    if ($id !== false) {
                        $chatIds[] = (int)$id;
                    } else {
                        $errors[] = 'User not found';
                    }
                    break;
                case 'selected':
                    $users = is_array($p['users'] ?? null) ? $p['users'] : [];
                    if (!$users) {
                        $errors[] = 'No users selected';
                        break;
                    }
                    foreach ($users as $u) {
                        $u = trim((string)$u);
                        if ($u === '') {
                            continue;
                        }
                        if (ctype_digit($u)) {
                            $stmt = $this->db->prepare('SELECT user_id FROM telegram_users WHERE user_id = :uid');
                            $stmt->execute(['uid' => (int)$u]);
                        } else {
                            $stmt = $this->db->prepare('SELECT user_id FROM telegram_users WHERE username = :uname');
                            $stmt->execute(['uname' => $u]);
                        }
                        $id = $stmt->fetchColumn();
                        if ($id !== false) {
                            $chatIds[] = (int)$id;
                        }
                    }
                    if (!$chatIds) {
                        $errors[] = 'No users found';
                    }
                    break;
                case 'group':
                    $gid = (int)($p['group_id'] ?? 0);
                    if ($gid <= 0) {
                        $errors[] = 'group_id is required';
                        break;
                    }
                    $stmt = $this->db->prepare('SELECT tu.user_id FROM telegram_user_group_user ugu JOIN telegram_users tu ON tu.id = ugu.user_id WHERE ugu.group_id = :gid');
                    $stmt->execute(['gid' => $gid]);
                    $chatIds = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
                    $chatIds = array_map('intval', $chatIds);
                    if (!$chatIds) {
                        $errors[] = 'Group is empty';
                    }
                    break;
            }
        }

        if (!$errors && $chatIds && $sender !== null) {
            $ok = true;
            foreach ($chatIds as $cid) {
                $ok = $sender($cid) && $ok;
            }
            if ($ok) {
                Flash::add('success', 'Message queued');
                return $res->withHeader('Location', '/dashboard/messages')->withStatus(302);
            }
            $errors[] = 'Failed to queue message';
        }

        $groups = $this->db->query('SELECT id,name FROM telegram_user_groups ORDER BY name')->fetchAll();
        $params = [
            'title' => 'Send message',
            'groups' => $groups,
            'errors' => $errors,
            'data' => $data,
        ];
        return View::render($res, 'dashboard/messages/create.php', $params, 'layouts/main.php');
    }
}
