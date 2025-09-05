<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">

<!-- Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css">

<h1>Рефералы</h1>

<form method="get" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="text" name="inviter_user_id" value="<?= htmlspecialchars($_GET['inviter_user_id'] ?? '') ?>"
               class="form-control" placeholder="ID пригласившего">
    </div>
    <div class="col-auto">
        <input type="text" name="invitee_user_id" value="<?= htmlspecialchars($_GET['invitee_user_id'] ?? '') ?>"
               class="form-control" placeholder="ID приглашённого">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-outline-success">Фильтр</button>
    </div>
    <div class="col-auto">
        <a href="/dashboard/referrals" class="btn btn-outline-secondary">Сбросить</a>
    </div>
    <div class="col-auto">
        <a href="/dashboard/tg-users" class="btn btn-outline-primary">Пользователи</a>
    </div>
    <div class="col-auto">
        <a href="/dashboard" class="btn btn-outline-secondary">Главная</a>
    </div>
  </form>

<table id="referralsTable" class="table table-center table-striped table-hover">
    <thead>
    <tr>
        <th>ID</th>
        <th>Пригласивший</th>
        <th>Приглашённый</th>
        <th>Код</th>
        <th>Создано</th>
    </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
    <tr>
        <th>ID</th>
        <th>Пригласивший</th>
        <th>Приглашённый</th>
        <th>Код</th>
        <th>Создано</th>
    </tr>
    </tfoot>
  </table>

<!-- jQuery и DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons core и HTML5-экспорт -->
<script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>

<script src="<?= url('/assets/js/datatable.common.js') ?>"></script>
<script src="<?= url('/assets/js/datatable.referrals.js') ?>"></script>

