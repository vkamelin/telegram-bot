<?php
/**
 * Детали рассылки/отложенного сообщения
 * @var array $item
 */
?>

<h1>Рассылка #<?= (int)$item['id'] ?></h1>

<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="fw-bold text-secondary">Выбрано получателей</div>
        <div class="display-6"><?= (int)($item['selected_count'] ?? 0) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="fw-bold text-success">Успешно</div>
        <div class="display-6"><?= (int)($item['success_count'] ?? 0) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="fw-bold text-danger">Ошибки</div>
        <div class="display-6"><?= (int)($item['failed_count'] ?? 0) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card h-100">
      <div class="card-body">
        <div class="fw-bold text-secondary">Статус</div>
        <div class="h4 mb-0"><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars((string)$item['status']) ?></span></div>
        <div class="small text-muted mt-1">Начало: <?= htmlspecialchars((string)($item['started_at'] ?? '—')) ?></div>
      </div>
    </div>
  </div>
</div>

<?php
  $selected = (int)($item['selected_count'] ?? 0);
  $succ = (int)($item['success_count'] ?? 0);
  $fail = (int)($item['failed_count'] ?? 0);
  $done = max(0, min(100, $selected > 0 ? (int)round((($succ + $fail) / $selected) * 100) : 0));
?>

<div class="card mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="fw-bold">Прогресс отправки</div>
      <div class="text-muted small"><?= $succ + $fail ?>/<?= $selected ?> (<?= $done ?>%)</div>
    </div>
    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $done ?>">
      <div class="progress-bar bg-success" style="width: <?= $selected > 0 ? (100 * $succ / $selected) : 0 ?>%"></div>
      <div class="progress-bar bg-danger" style="width: <?= $selected > 0 ? (100 * $fail / $selected) : 0 ?>%"></div>
    </div>
  </div>
  <div class="card-footer text-muted small">
    Метод: <code><?= htmlspecialchars((string)$item['method']) ?></code>
    · Тип: <code><?= htmlspecialchars((string)$item['type']) ?></code>
    · Приоритет: <code><?= (int)$item['priority'] ?></code>
    · Отправлять после: <code><?= htmlspecialchars((string)$item['send_after']) ?></code>
  </div>
</div>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">

<table id="scheduledMessagesTable" class="table table-center table-striped table-hover">
  <thead>
    <tr>
      <th>ID</th>
      <th>User ID</th>
      <th>Метод</th>
      <th>Статус</th>
      <th>Ошибка</th>
      <th>Код</th>
      <th>Msg ID</th>
      <th>Обработано</th>
    </tr>
  </thead>
  <tbody></tbody>
  <tfoot>
    <tr>
      <th>ID</th>
      <th>User ID</th>
      <th>Метод</th>
      <th>Статус</th>
      <th>Ошибка</th>
      <th>Код</th>
      <th>Msg ID</th>
      <th>Обработано</th>
    </tr>
  </tfoot>
  </table>

<!-- jQuery и DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>

<script src="<?= url('/assets/js/datatable.common.js') ?>"></script>
<script>
  $(document).ready(function(){
    createDatatable('#scheduledMessagesTable', '/dashboard/scheduled/<?= (int)$item['id'] ?>/messages', [
      { data: 'id' },
      { data: 'user_id' },
      { data: 'method' },
      { data: 'status', render: function(s){
          if (s === 'success') return '<span class="badge bg-success">success</span>';
          if (s === 'failed') return '<span class="badge bg-danger">failed</span>';
          if (s === 'pending') return '<span class="badge bg-warning text-dark">pending</span>';
          return s;
      }},
      { data: 'error', defaultContent: '' },
      { data: 'code', defaultContent: '' },
      { data: 'message_id', defaultContent: '' },
      { data: 'processed_at', defaultContent: '' },
    ]);
  });
</script>

