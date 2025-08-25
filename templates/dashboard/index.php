<?php
/**
 * Dashboard view
 *
 * @var int        $pendingMessages
 * @var int        $processingMessages
 * @var int        $scheduledMessages
 * @var int        $lastHourSuccess
 * @var int        $lastHourFailed
 * @var float      $lastHourSuccessShare
 * @var float      $lastHourFailedShare
 * @var int        $distinctUsers24h
 * @var int        $activeSessions24h
 * @var array      $lastErrors
 * @var array      $chartLabels
 * @var array      $chartSuccess
 * @var array      $chartFailed
 * @var array{db: bool, redis: bool, worker: bool, status: string} $health
 * @var array|null $queueSizes
 * @var int|null   $sendSpeed
 */

?>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h4>Pending</h4>
                <p class="lead"><?= $pendingMessages ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
            <h4>Processing</h4>
            <p class="lead"><?= $processingMessages ?></p>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
            <h4>К отправке</h4>
            <p class="lead"><?= $scheduledMessages ?></p>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
            <h4>Health</h4>
            <p class="lead">
                <?= $health['db'] ? 'DB OK' : 'DB FAIL' ?>,
                <?= $health['redis'] ? 'Redis OK' : 'Redis FAIL' ?>,
                <?= $health['worker'] ? 'Worker OK' : 'Worker FAIL' ?>
            </p>
        </div></div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
            <h4>Success/Failed (1ч)</h4>
            <p class="lead"><?= $lastHourSuccess ?> / <?= $lastHourFailed ?></p>
            <small><?= $lastHourSuccessShare ?>% / <?= $lastHourFailedShare ?>%</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
            <h4>Уникальных пользователей 24ч</h4>
            <p class="lead"><?= $distinctUsers24h ?></p>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
            <h4>Активные сессии 24ч</h4>
            <p class="lead"><?= $activeSessions24h ?></p>
        </div></div>
    </div>
    <?php
    if (!empty($queueSizes)): ?>
        <div class="col-md-3">
            <div class="card">
            <div class="card-body">
                <h4>Очереди</h4>
                <p class="lead">p2: <?= $queueSizes['p2'] ?>, p1: <?= $queueSizes['p1'] ?>,
                    p0: <?= $queueSizes['p0'] ?></p>
                <small>DLQ: <?= $queueSizes['dlq'] ?>; RPS: <?= $sendSpeed ?></small>
            </div></div>
        </div>
    <?php
    endif; ?>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <canvas id="statusChart"
                data-labels='<?= htmlspecialchars(json_encode($chartLabels), ENT_QUOTES) ?>'
                data-success='<?= htmlspecialchars(json_encode($chartSuccess), ENT_QUOTES) ?>'
                data-failed='<?= htmlspecialchars(json_encode($chartFailed), ENT_QUOTES) ?>'></canvas>
    </div>
</div>

<div class="d-flex justify-content-between"></div>

<div class="row mt-3">
    <div class="col-md-12">
        <h4>Последние ошибки</h4>
        <table class="table table-dark table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Code</th>
                <th>Error</th>
                <th>Время</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($lastErrors as $err): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$err['id']) ?></td>
                    <td><?= htmlspecialchars((string)$err['user_id']) ?></td>
                    <td><?= htmlspecialchars((string)$err['code']) ?></td>
                    <td><?= htmlspecialchars((string)$err['error']) ?></td>
                    <td><?= htmlspecialchars((string)$err['processed_at']) ?></td>
                </tr>
            <?php
            endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= url('/assets/js/dashboard-chart.js') ?>"></script>
