<?php
/**
 * System view
 *
 * @var array|null $health
 * @var array $env
 * @var array $workerCommands
 */
?>

<div class="row">
    <div class="col-md-3">
        <div class="h-100 p-3 text-bg-dark rounded-3">
            <h4>Health</h4>
            <p class="lead"><?= isset($health['status']) && $health['status'] === 'ok' ? 'OK' : 'FAIL' ?></p>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <h4>Env-переменные</h4>
        <table class="table table-dark table-striped">
            <tbody>
            <?php foreach ($env as $name => $value): ?>
                <tr>
                    <th><?= htmlspecialchars($name) ?></th>
                    <td><input type="text" readonly class="form-control-plaintext text-white" value="<?= htmlspecialchars((string)$value) ?>"></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="col-md-6">
        <h4>Воркеры</h4>
        <h6>Проверка статуса</h6>
        <pre class="bg-dark text-white p-2 rounded-3"><code><?= implode("\n", $workerCommands['status']) ?></code></pre>
        <h6>Перезапуск</h6>
        <pre class="bg-dark text-white p-2 rounded-3"><code><?= implode("\n", $workerCommands['restart']) ?></code></pre>
    </div>
</div>
