<?php
/**
 * Copyright (c) 2025. Vitaliy Kamelin <v.kamelin@gmail.com>
 */

declare(strict_types=1);

namespace App\Controllers\Api;

use PDO;
use Psr\Http\Message\ServerRequestInterface as Req;
use Psr\Http\Message\ResponseInterface as Res;
use App\Helpers\Response;

final class UsersController
{
    public function __construct(private PDO $pdo) {}
    
    public function list(Req $req, Res $res): Res
    {
        $q = $this->pdo->query('SELECT id, email, created_at FROM users ORDER BY id DESC LIMIT 100');
        $rows = $q->fetchAll();
        return Response::json($res, 200, ['items' => $rows]);
    }
    
    public function create(Req $req, Res $res): Res
    {
        $data = (array)$req->getParsedBody();
        $email = trim((string)($data['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return Response::problem($res, 400, 'Validation error', ['errors' => ['email' => 'invalid']]);
        }
        $stmt = $this->pdo->prepare('INSERT INTO users(email, created_at) VALUES(?, NOW())');
        $stmt->execute([$email]);
        return Response::json($res, 201, ['id' => (int)$this->pdo->lastInsertId()]);
    }
}