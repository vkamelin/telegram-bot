<?php

$menu = [
    [
        'url'   => '/dashboard',
        'title' => 'Главная',
        'icon'  => 'bi bi-speedometer2',
    ],
    [
        'url'   => '/dashboard/tg-users',
        'title' => 'Пользователи',
        'icon'  => 'bi bi-people-fill',
    ],
    [
        'url'   => '/dashboard/updates',
        'title' => 'Обновления',
        'icon'  => 'bi bi-arrow-repeat',
    ],
    [
        'url'   => '/dashboard/messages',
        'title' => 'Telegram Сообщения',
        'icon'  => 'bi bi-chat-right-text',
    ],
    [
        'url'   => '/dashboard/scheduled',
        'title' => 'Расписание сообщений',
        'icon'  => 'bi bi-clock',
    ],
    [
        'url'   => '/dashboard/pre-checkout',
        'title' => 'Пред. заказ',
        'icon'  => 'bi bi-receipt',
    ],
    [
        'url'   => '/dashboard/shipping',
        'title' => 'Доставка',
        'icon'  => 'bi bi-truck',
    ],
    [
        'url'   => '/dashboard/invoices/create',
        'title' => 'Счета',
        'icon'  => 'bi bi-file-earmark-text',
    ],
    [
        'url'   => '/dashboard/join-requests',
        'title' => 'Запросы на вступление',
        'icon'  => 'bi bi-person-plus',
    ],
    [
        'url'   => '/dashboard/chat-members',
        'title' => 'Подписчики',
        'icon'  => 'bi bi-people',
    ],
    [
        'url'   => '/dashboard/sessions',
        'title' => 'Сессии',
        'icon'  => 'bi bi-clock-history',
    ],
    [
        'url'   => '/dashboard/users',
        'title' => 'Администраторы',
        'icon'  => 'bi bi-person-gear',
    ],
    [
        'url'   => '/dashboard/tokens',
        'title' => 'Токены',
        'icon'  => 'bi bi-key',
    ],
    [
        'url'   => '/dashboard/system',
        'title' => 'Система',
        'icon'  => 'bi bi-gear',
    ],
];

if (is_dir(__DIR__ . '/dashboard/metrics') || file_exists(__DIR__ . '/dashboard/metrics.php')) {
    $menu[] = [
        'url'   => '/dashboard/metrics',
        'title' => 'Метрики',
        'icon'  => 'bi bi-graph-up',
    ];
}

return $menu;
