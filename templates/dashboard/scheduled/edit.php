<?php
/** @var array $item */
/** @var array $errors */
/** @var string $csrfToken */
?>
<h1 class="mb-3">Edit Scheduled</h1>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= url('/dashboard/scheduled/' . $item['id'] . '/update') ?>">
    <input type="hidden" name="<?= $_ENV['CSRF_TOKEN_NAME'] ?? '_csrf_token' ?>" value="<?= $csrfToken ?>">

    <div class="mb-3">
        <label class="form-label">ID</label>
        <div class="form-control-plaintext">#<?= (int)$item['id'] ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Priority</label>
        <input type="number" class="form-control" name="priority" value="<?= htmlspecialchars((string)($item['priority'] ?? 2)) ?>" min="0" max="2">
    </div>
    <div class="mb-3">
        <label class="form-label">Send after</label>
        <input type="datetime-local" class="form-control" name="send_after" value="<?= htmlspecialchars((string)($item['send_after'] ?? '')) ?>">
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="<?= url('/dashboard/scheduled') ?>" class="btn btn-secondary ms-2">Cancel</a>
</form>

