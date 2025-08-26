<?php
/** @var array $groups */
/** @var array $errors */
/** @var array $data */
/** @var string $csrfToken */
?>
<h1 class="mb-3">Send message</h1>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<form method="post" action="<?= url('/dashboard/messages/send') ?>">
    <input type="hidden" name="<?= $_ENV['CSRF_TOKEN_NAME'] ?? '_csrf_token' ?>" value="<?= $csrfToken ?>">
    <div class="mb-3">
        <textarea class="form-control" name="text" rows="3" placeholder="Text message"><?= htmlspecialchars($data['text'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeAll" value="all" <?= (($data['mode'] ?? '') === 'all') ? 'checked' : '' ?>>
            <label class="form-check-label" for="modeAll">All users</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeSingle" value="single" <?= (($data['mode'] ?? '') === 'single') ? 'checked' : '' ?>>
            <label class="form-check-label" for="modeSingle">Single user</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeSelected" value="selected" <?= (($data['mode'] ?? '') === 'selected') ? 'checked' : '' ?>>
            <label class="form-check-label" for="modeSelected">Selected users</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="mode" id="modeGroup" value="group" <?= (($data['mode'] ?? '') === 'group') ? 'checked' : '' ?>>
            <label class="form-check-label" for="modeGroup">Group</label>
        </div>
    </div>
    <div id="singleUserSection" class="mb-3 d-none">
        <input type="text" class="form-control" name="user" id="singleUserInput" placeholder="User ID or username" value="<?= htmlspecialchars($data['user'] ?? '') ?>">
    </div>
    <div id="selectedUsersSection" class="mb-3 d-none">
        <input type="text" class="form-control mb-2" id="userSearchInput" placeholder="Search user">
        <ul class="list-group mb-2" id="userSearchResults"></ul>
        <ul class="list-group" id="selectedUsers">
            <?php if (!empty($data['users']) && is_array($data['users'])): foreach ($data['users'] as $u): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center" data-user-id="<?= htmlspecialchars((string)$u) ?>">
                    <span><?= htmlspecialchars((string)$u) ?></span>
                    <input type="hidden" name="users[]" value="<?= htmlspecialchars((string)$u) ?>">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-user">Remove</button>
                </li>
            <?php endforeach; endif; ?>
        </ul>
    </div>
    <div id="groupSection" class="mb-3 d-none">
        <select class="form-select" name="group_id" id="groupSelect">
            <option value="">Select group</option>
            <?php foreach ($groups as $g): ?>
                <option value="<?= $g['id'] ?>" <?= (($data['group_id'] ?? '') == $g['id']) ? 'selected' : '' ?>><?= htmlspecialchars($g['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>
<script>
    window.tgUserSearchUrl = '<?= url('/dashboard/tg-users/search') ?>';
</script>
<script src="<?= url('/assets/js/message-send.js') ?>"></script>
