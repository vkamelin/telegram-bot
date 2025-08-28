<?php

$menu = [
    [
        'url'   => '/dashboard',
        'title' => 'Главная',
        'icon'  => 'bi bi-speedometer2',
    ],
    [
        'title'    => 'Сообщения',
        'icon'     => 'bi bi-telegram',
        'children' => [
            [
                'url'   => '/dashboard/messages',
                'title' => 'Исходящие',
                'icon'  => 'bi bi-telegram',
            ],
            [
                'url'   => '/dashboard/messages/create',
                'title' => 'Отправить сообщение',
                'icon'  => 'bi bi-envelope',
            ],
            [
                'url'   => '/dashboard/scheduled',
                'title' => 'Расписание сообщений',
                'icon'  => 'bi bi-clock',
            ],
        ],
    ],
    [
        'title'    => 'Платежи',
        'icon'     => 'bi bi-receipt',
        'children' => [
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
        ],
    ],
    [
        'title'    => 'Telegram',
        'icon'     => 'bi bi-telegram',
        'children' => [
            [
                'url'   => '/dashboard/tg-users',
                'title' => 'Пользователи',
                'icon'  => 'bi bi-people-fill',
            ],
            [
                'url'   => '/dashboard/tg-groups',
                'title' => 'Группы',
                'icon'  => 'bi bi-people',
            ],
            [
                'url'   => '/dashboard/updates',
                'title' => 'Входяшие',
                'icon'  => 'bi bi-telegram',
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
        ],
    ],
    [
        'title'    => 'Администрирование',
        'icon'     => 'bi bi-gear',
        'children' => [
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
        ],
    ],
];

if (is_dir(__DIR__ . '/dashboard/metrics') || file_exists(__DIR__ . '/dashboard/metrics.php')) {
    foreach ($menu as &$item) {
        if (($item['title'] ?? null) === 'Администрирование') {
            $item['children'][] = [
                'url'   => '/dashboard/metrics',
                'title' => 'Метрики',
                'icon'  => 'bi bi-graph-up',
            ];
            break;
        }
    }
    unset($item);
}

return $menu;
