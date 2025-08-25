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
<div class="mb-4">
    <input type="text" class="form-control mb-2" id="userSearchInput" placeholder="Search by username or ID">
    <ul class="list-group" id="userSearchResults"></ul>
</div>

<h2 class="h5">Members</h2>
<p id="noMembers" class="<?= empty($members) ? '' : 'd-none' ?>">No members</p>
<table class="table table-striped <?= empty($members) ? 'd-none' : '' ?>" id="membersTable">
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
        <tr data-member-id="<?= $m['id'] ?>">
            <td><?= $m['id'] ?></td>
            <td><?= $m['user_id'] ?></td>
            <td><?= htmlspecialchars($m['username'] ?? '') ?></td>
            <td><button type="button" class="btn btn-sm btn-danger remove-member-btn" data-user-id="<?= $m['id'] ?>">Remove</button></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script>
    window.tgUserSearchUrl = '<?= url('/dashboard/tg-users/search') ?>';
    window.tgGroupAddUrl = '<?= url('/dashboard/tg-groups/' . $group['id'] . '/add-user') ?>';
    window.tgGroupRemoveUrl = '<?= url('/dashboard/tg-groups/' . $group['id'] . '/remove-user') ?>';
</script>
<script src="<?= url('/assets/js/tg-groups-edit.js') ?>"></script>
