<?php
/** @var array $item */
/** @var array $errors */
/** @var string $csrfToken */
?>
<h1 class="mb-3">Редактировать сообщение</h1>
<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $e): ?>
            <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= url('/dashboard/scheduled/' . urlencode((string)($item['id'] ?? '')) . '/update') ?>">
    <input type="hidden" name="<?= $_ENV['CSRF_TOKEN_NAME'] ?? '_csrf_token' ?>" value="<?= $csrfToken ?>">

    <div class="mb-3">
        <label class="form-label">ID</label>
        <div class="form-control-plaintext">#<?= htmlspecialchars((string)($item['id'] ?? '')) ?></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Приоритет</label>
        <input type="number" class="form-control" name="priority" value="<?= htmlspecialchars((string)($item['priority'] ?? 2)) ?>" min="0" max="2">
    </div>
    <div class="mb-3">
        <label class="form-label">Время отправки</label>
        <input type="datetime-local" class="form-control" name="send_after" value="<?= htmlspecialchars((string)($item['send_after'] ?? '')) ?>">
    </div>
    <button type="submit" class="btn btn-outline-success">Сохранить</button>
    <a href="<?= url('/dashboard/scheduled') ?>" class="btn btn-outline-secondary ms-2">Отменить</a>
</form>
