<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">

<!-- Buttons CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.3/css/buttons.bootstrap5.min.css">

<h1>Список пользователей</h1>

<table id="usersTable" class="table table-center table-striped table-hover">
    <thead>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Имя</th>
        <th>Фамилия</th>
        <th>Псевдоним</th>
        <th>UTM</th>
        <th>Зарегистрировался</th>
        <th class="text-end">Действия</th>
    </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Имя</th>
        <th>Фамилия</th>
        <th>Псевдоним</th>
        <th>UTM</th>
        <th>Зарегистрировался</th>
        <th class="text-end">Действия</th>
    </tr>
    </tfoot>
</table>

<!-- jQuery и DataTables JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>

<!-- Buttons core и HTML5-экспорт -->
<script src="https://cdn.datatables.net/buttons/2.3.3/js/dataTables.buttons.min.js"></script>  <!-- Buttons extension framework :contentReference[oaicite:5]{index=5} -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>         <!-- JSZip для XLSX :contentReference[oaicite:6]{index=6} -->
<script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.html5.min.js"></script>          <!-- Экспорт HTML5 (excelHtml5, csvHtml5, pdfHtml5) :contentReference[oaicite:7]{index=7} -->
<script src="https://cdn.datatables.net/buttons/2.3.3/js/buttons.bootstrap5.min.js"></script>     <!-- Стили для Bootstrap 5 -->

<script src="<?= url('/assets/js/datatable.common.js') ?>"></script>
<script src="<?= url('/assets/js/datatable.users.js') ?>"></script>
