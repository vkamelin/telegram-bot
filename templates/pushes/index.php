<?php
/**
 * @var array $pushes
 */

?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css">

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1>Список рассылок</h1>
    <a href="/dashboard/pushes/create" class="btn btn-outline-success">
        <i class="bi bi-plus-square"></i>
        Новый пуш
    </a>
</div>

<table class="table table-striped" id="pushesTable">
    <thead>
    <tr>
        <td>ID</td>
        <td data-sortable="false">Текст</td>
        <td>Создана</td>
        <td>Отправлена</td>
        <td>База</td>
        <td>Получено</td>
        <td>Отписавшиеся</td>
        <td data-sortable="false">Статус</td>
        <td data-sortable="false" data-searchable="false">Действия</td>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($pushes as $push): ?>
        <tr>
            <td><?= $push['id'] ?></td>
            <td><?= $push['text'] ?></td>
            <td><?= $push['created_at'] ?></td>
            <td><?= $push['sent_at'] ?></td>
            <td><?= $push['total_recipients'] ?></td>
            <td><?= $push['delivered_count'] ?></td>
            <td><?= $push['failed_count'] ?></td>
            <td><?= $push['status'] ?></td>
            <td>
                <div class="btn-group" role="group">
                    <?php if ($push['actions'] === true): ?>
                        <a href="/dashboard/pushes/edit/<?= $push['id'] ?>" class="btn btn-outline-secondary">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    <?php endif; ?>
                    <a href="/dashboard/pushes/view/<?= $push['id'] ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-file-earmark-richtext"></i>
                    </a>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
      $('#pushesTable').DataTable({
        stateSave: true,
        language: {
          url: 'https://cdn.datatables.net/plug-ins/2.2.2/i18n/ru.json',
        },
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Все"]]
      });
    });
</script>
