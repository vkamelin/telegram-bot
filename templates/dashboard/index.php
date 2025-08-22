<?php
/**
 * @var int $totalTelegramUsers
 * @var int $promocodes
 */
?>

<div class="row">
    <div class="col-md-3">
        <div class="h-100 p-5 text-bg-dark rounded-3">
            <h4>Количество пользователей</h4>
            <p class="lead"><?= $totalTelegramUsers ?></p>
        </div>
    </div>

    <div class="col-md-3">
        <div class="h-100 p-5 text-bg-dark rounded-3">
            <h4>Промокодов не выдано</h4>
            <p class="lead"><?= $promocodes ?></p>
        </div>
    </div>
</div>