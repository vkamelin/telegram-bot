<?php
/**
 * Submenu partial
 *
 * @var array $submenu Подменю
 */
?>

<ul class="nav nav-pills mb-3">
    <?php foreach ($submenu as $item): ?>
        <li class="nav-item">
            <a href="<?= url($item['url']) ?>" class="nav-link <?= $item['class'] ?? '' ?>">
                <?= $item['title'] ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

