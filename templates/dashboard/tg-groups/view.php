<?php
/** @var array $group */
/** @var array $members */
/** @var string $csrfToken */
?>
<h1 class="mb-3">Group <?= htmlspecialchars($group['name']) ?></h1>

<form method="post" class="mb-4" action="<?= url('/dashboard/tg-groups/' . $group['id']) ?>">
    <input type="hidden" name="<?= env('CSRF_TOKEN_NAME', '_csrf_token') ?>" value="<?= $csrfToken ?>">
    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($group['name']) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
</form>

<h2 class="h5">Add user</h2>
<form method="post" class="mb-4" action="<?= url('/dashboard/tg-groups/' . $group['id'] . '/add-user') ?>">
    <input type="hidden" name="<?= env('CSRF_TOKEN_NAME', '_csrf_token') ?>" value="<?= $csrfToken ?>">
    <div class="input-group">
        <input type="number" class="form-control" name="user_id" placeholder="Telegram user ID">
        <button type="submit" class="btn btn-secondary">Add</button>
    </div>
</form>

<h2 class="h5">Members</h2>
<?php if (empty($members)): ?>
    <p>No members</p>
<?php else: ?>
<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Telegram ID</th>
        <th>Username</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($members as $m): ?>
        <tr>
            <td><?= $m['id'] ?></td>
            <td><?= $m['user_id'] ?></td>
            <td><?= htmlspecialchars($m['username'] ?? '') ?></td>
            <td>
                <form method="post" class="d-inline" action="<?= url('/dashboard/tg-groups/' . $group['id'] . '/remove-user') ?>">
                    <input type="hidden" name="<?= env('CSRF_TOKEN_NAME', '_csrf_token') ?>" value="<?= $csrfToken ?>">
                    <input type="hidden" name="user_id" value="<?= $m['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Remove user?')">Remove</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
