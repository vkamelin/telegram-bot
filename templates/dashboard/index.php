<?php
/**
 * Dashboard view
 *
 * @var int      $pendingMessages
 * @var int      $processingMessages
 * @var int      $scheduledMessages
 * @var int      $lastHourSuccess
 * @var int      $lastHourFailed
 * @var float    $lastHourSuccessShare
 * @var float    $lastHourFailedShare
 * @var int      $distinctUsers24h
 * @var int      $activeSessions24h
 * @var array    $lastErrors
 * @var array    $chartLabels
 * @var array    $chartSuccess
 * @var array    $chartFailed
 * @var bool     $healthOk
 * @var array|null $queueSizes
 * @var int|null   $sendSpeed
 */
?>

<div class="row">
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>Pending</h4>
            <p class="lead"><?= $pendingMessages ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>Processing</h4>
            <p class="lead"><?= $processingMessages ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>К отправке</h4>
            <p class="lead"><?= $scheduledMessages ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>Health</h4>
            <p class="lead"><?= $healthOk ? 'OK' : 'FAIL' ?></p>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>Success/Failed (1ч)</h4>
            <p class="lead"><?= $lastHourSuccess ?> / <?= $lastHourFailed ?></p>
            <small><?= $lastHourSuccessShare ?>% / <?= $lastHourFailedShare ?>%</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>Уникальных пользователей 24ч</h4>
            <p class="lead"><?= $distinctUsers24h ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>Активные сессии 24ч</h4>
            <p class="lead"><?= $activeSessions24h ?></p>
        </div>
    </div>
    <?php if (!empty($queueSizes)): ?>
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>Очереди</h4>
            <p class="lead">p2: <?= $queueSizes['p2'] ?>, p1: <?= $queueSizes['p1'] ?>, p0: <?= $queueSizes['p0'] ?></p>
            <small>DLQ: <?= $queueSizes['dlq'] ?>; RPS: <?= $sendSpeed ?></small>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <canvas id="statusChart"></canvas>
    </div>
</div>

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
                <?php foreach ($lastErrors as $err): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$err['id']) ?></td>
                    <td><?= htmlspecialchars((string)$err['user_id']) ?></td>
                    <td><?= htmlspecialchars((string)$err['code']) ?></td>
                    <td><?= htmlspecialchars((string)$err['error']) ?></td>
                    <td><?= htmlspecialchars((string)$err['processed_at']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('statusChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [
            {
                label: 'success',
                data: <?= json_encode($chartSuccess) ?>,
                borderColor: 'rgb(25, 135, 84)',
                tension: 0.1
            },
            {
                label: 'failed',
                data: <?= json_encode($chartFailed) ?>,
                borderColor: 'rgb(220, 53, 69)',
                tension: 0.1
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
