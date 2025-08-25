<?php
/**
 * @var array $invoice
 * @var array $errors
 * @var string $csrfToken
 */
?>
<h1 class="mb-3">Send Invoice</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <?= implode('<br>', $errors) ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= url('/dashboard/invoices') ?>">
    <input type="hidden" name="<?= env('CSRF_TOKEN_NAME', '_csrf_token') ?>" value="<?= $csrfToken ?>">
    <div class="mb-3">
        <label for="chat_id" class="form-label">Chat ID</label>
        <input type="text" class="form-control" id="chat_id" name="chat_id" value="<?= htmlspecialchars($invoice['chat_id'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($invoice['title'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($invoice['description'] ?? '') ?></textarea>
    </div>
    <div class="mb-3">
        <label for="payload" class="form-label">Payload</label>
        <input type="text" class="form-control" id="payload" name="payload" value="<?= htmlspecialchars($invoice['payload'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label for="provider_token" class="form-label">Provider Token</label>
        <input type="text" class="form-control" id="provider_token" name="provider_token" value="<?= htmlspecialchars($invoice['provider_token'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label for="currency" class="form-label">Currency</label>
        <input type="text" class="form-control" id="currency" name="currency" value="<?= htmlspecialchars($invoice['currency'] ?? '') ?>">
    </div>
    <div class="mb-3">
        <label for="prices" class="form-label">Prices (JSON)</label>
        <textarea class="form-control" id="prices" name="prices" rows="3"><?= htmlspecialchars($invoice['prices'] ?? '') ?></textarea>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="1" id="need_name" name="need_name" <?= !empty($invoice['need_name']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="need_name">Need name</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="1" id="need_phone_number" name="need_phone_number" <?= !empty($invoice['need_phone_number']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="need_phone_number">Need phone number</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="1" id="need_email" name="need_email" <?= !empty($invoice['need_email']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="need_email">Need email</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="checkbox" value="1" id="need_shipping_address" name="need_shipping_address" <?= !empty($invoice['need_shipping_address']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="need_shipping_address">Need shipping address</label>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" value="1" id="is_flexible" name="is_flexible" <?= !empty($invoice['is_flexible']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="is_flexible">Is flexible</label>
    </div>
    <button type="submit" class="btn btn-primary">Send</button>
</form>
