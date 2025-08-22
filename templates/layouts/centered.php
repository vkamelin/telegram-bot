<?php
/**
 * Centered layout
 *
 * @author Vitaliy Kamelin <v.kamelin@gmail.com>
 *
 * @var string $content Контент страницы
 * @var string $title Заголовок страницы
 * @var string $csrfToken Токен CSRF
 * @var string $currentPath Текущий путь (без параметров запроса)
 * @var array $menu Меню
 */
?>
<!doctype html>
<html lang="ru" data-bs-theme="auto">
<head>
    <title><?= $title ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?= url('/assets/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= url('/assets/css/login.css') ?>" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center py-4 bg-body-tertiary">

<?= $content ?>

<script src="<?= url('/assets/js/bootstrap.bundle.min.js') ?>"></script>

</body>
</html>