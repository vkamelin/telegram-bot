<?php
/**
 * @var array $push
 */

$fileUrl = url('/uploads/pushes/' . $push['media_filename']);
$scheduledAt = !empty($push['scheduled_at']) ? date('H:i d.m.Y', strtotime($push['scheduled_at'])) : '&mdash;';
$sentAt = !empty($push['sent_at']) ? date('H:i d.m.Y', strtotime($push['sent_at'])) : '&mdash;';
$status = match ($push['status']) {
    'draft' => 'Черновик',
    'sending' => 'Отправляется',
    'sent' => 'Отправлен',
    default => 'Неизвестный статус',
};
$createdAt = date('H:i d.m.Y', strtotime($push['created_at']));
$updatedAt = date('H:i d.m.Y', strtotime($push['updated_at']));
?>

<div class="row">
    <div class="col-4">
        <div class="card">
            <?php if ($push['media_type'] !== 'text'): ?>
                <?php if ($push['media_type'] === 'photo'): ?>
                    <img src="<?= $fileUrl ?>" class="card-img-top" alt="...">
                <?php else: ?>
                    <video src="<?= $fileUrl ?>" controls class="card-img-top">
                        <source src="<?= $fileUrl ?>" type="video/mp4">
                    </video>
                <?php endif; ?>
            <?php endif; ?>
            <div class="card-body editor">
                <?= nl2br(html_entity_decode($push['text'])) ?>

                <div class="d-grid gap-2 mt-3">
                    <?php if (!empty($push['button_url']) && !empty($push['button_text'])): ?>
                        <a href="<?= $push['button_url'] ?>" target="_blank" class="btn btn-primary">
                            <?= $push['button_text'] ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-8">
        <table class="table table-borderless">
            <tr>
                <th>Текст</th>
                <td><?= $push['text'] ?></td>
            </tr>
            <?php if ($push['media_type'] !== 'text'): ?>
            <tr>
                <th>Медиа</th>
                <td>
                    <?= $fileUrl ?>
                    <a href="<?= $fileUrl ?>" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Текст кнопки</th>
                <td><?= $push['button_text'] ?></td>
            </tr>
            <tr>
                <th>Ссылка кнопки</th>
                <td>
                    <?= $push['button_url'] ?>
                    <a href="<?= $push['button_url'] ?>" target="_blank"><i class="bi bi-box-arrow-up-right"></i></a>
                </td>
            </tr>
            <tr>
                <th>Дата отправки</th>
                <td><?= $push['scheduled_at'] ?></td>
            </tr>
            <tr>
                <th>Отправлено</th>
                <td><?= $sentAt ?></td>
            </tr>
            <tr>
                <th>Всего получателей</th>
                <td><?= $push['total_recipients'] ?></td>
            </tr>
            <tr>
                <th>Получено</th>
                <td><?= $push['delivered_count'] ?></td>
            </tr>
            <tr>
                <th>Отписавшиеся</th>
                <td><?= $push['failed_count'] ?></td>
            </tr>
            <tr>
                <th>Статус</th>
                <td><?= $status ?></td>
            </tr>
            <tr>
                <th>Создано</th>
                <td><?= $createdAt ?></td>
            </tr>
            <tr>
                <th>Обновлено</th>
                <td><?= $updatedAt ?></td>
            </tr>
        </table>
    </div>
</div>
