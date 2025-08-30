<?php
/**
 * Submenu partial
 *
 * @var array $submenu Подменю
 */
?>

<ul class="nav nav-pills mb-3">
    <?php foreach ($submenu as $item): ?>
        <?php
            $itemClass = (string)($item['class'] ?? '');
            $isActive = str_contains($itemClass, 'active');
            $icon = (string)($item['icon'] ?? 'bi bi-chevron-right');
            // Для активного пункта — стандартная pill-навигация,
            // для неактивного — обводка как у outline-кнопок
            $linkClass = $isActive
                ? 'nav-link active'
                : 'btn btn-sm btn-outline-secondary';
        ?>
        <li class="nav-item me-2 mb-2">
            <a href="<?= url($item['url']) ?>" class="<?= $linkClass ?>">
                <i class="<?= $icon ?>"></i>
                <span class="ms-1"><?= $item['title'] ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

