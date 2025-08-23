<h1>User <?= htmlspecialchars($user['username'] ?? $user['user_id']) ?></h1>

<div class="card mb-4">
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-3">User ID</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string)$user['user_id']) ?></dd>
            <dt class="col-sm-3">Username</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string)$user['username']) ?></dd>
            <dt class="col-sm-3">First name</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string)$user['first_name']) ?></dd>
            <dt class="col-sm-3">Last name</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string)$user['last_name']) ?></dd>
            <dt class="col-sm-3">Language</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string)$user['language_code']) ?></dd>
            <dt class="col-sm-3">Premium</dt>
            <dd class="col-sm-9"><?= (int)$user['is_premium'] ?></dd>
            <dt class="col-sm-3">User banned</dt>
            <dd class="col-sm-9"><?= (int)$user['is_user_banned'] ?></dd>
            <dt class="col-sm-3">Bot banned</dt>
            <dd class="col-sm-9"><?= (int)$user['is_bot_banned'] ?></dd>
            <dt class="col-sm-3">Subscribed</dt>
            <dd class="col-sm-9"><?= (int)$user['is_subscribed'] ?></dd>
            <dt class="col-sm-3">Referral code</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string)$user['referral_code']) ?></dd>
            <dt class="col-sm-3">UTM</dt>
            <dd class="col-sm-9"><?= htmlspecialchars((string)$user['utm']) ?></dd>
        </dl>
    </div>
</div>

<h2>Последние сообщения</h2>
<table class="table table-center table-striped table-hover mb-4">
    <thead>
    <tr>
        <th>ID</th>
        <th>Method</th>
        <th>Type</th>
        <th>Status</th>
        <th>Processed at</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($messages as $msg): ?>
        <tr>
            <td><?= htmlspecialchars((string)$msg['id']) ?></td>
            <td><?= htmlspecialchars((string)$msg['method']) ?></td>
            <td><?= htmlspecialchars((string)$msg['type']) ?></td>
            <td><?= htmlspecialchars((string)$msg['status']) ?></td>
            <td><?= htmlspecialchars((string)$msg['processed_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Последние обновления</h2>
<table class="table table-center table-striped table-hover">
    <thead>
    <tr>
        <th>ID</th>
        <th>Update ID</th>
        <th>Type</th>
        <th>Created at</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($updates as $upd): ?>
        <tr>
            <td><?= htmlspecialchars((string)$upd['id']) ?></td>
            <td><?= htmlspecialchars((string)$upd['update_id']) ?></td>
            <td><?= htmlspecialchars((string)$upd['type']) ?></td>
            <td><?= htmlspecialchars((string)$upd['created_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
