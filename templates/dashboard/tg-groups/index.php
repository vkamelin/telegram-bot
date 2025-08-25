<h1>Telegram Groups</h1>

<form method="post" class="mb-3" action="<?= url('/dashboard/tg-groups') ?>">
    <input type="hidden" name="<?= env('CSRF_TOKEN_NAME', '_csrf_token') ?>" value="<?= $csrfToken ?>">
    <div class="input-group">
        <input type="text" name="name" class="form-control" placeholder="Group name" required>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>

<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Members</th>
        <th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($groups as $g): ?>
        <tr>
            <td><?= $g['id'] ?></td>
            <td><?= htmlspecialchars($g['name']) ?></td>
            <td><?= $g['members'] ?></td>
            <td><a class="btn btn-sm btn-secondary" href="<?= url('/dashboard/tg-groups/' . $g['id']) ?>">View</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
