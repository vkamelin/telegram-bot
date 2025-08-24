<?php

$menu = [
    [
        'url'   => '/dashboard',
        'title' => 'Overview',
        'icon'  => 'bi bi-speedometer2',
    ],
    [
        'url'   => '/dashboard/messages',
        'title' => 'Messages',
        'icon'  => 'bi bi-chat-right-text',
    ],
    [
        'url'   => '/dashboard/scheduled',
        'title' => 'Scheduled',
        'icon'  => 'bi bi-clock',
    ],
    [
        'url'   => '/dashboard/updates',
        'title' => 'Updates',
        'icon'  => 'bi bi-arrow-repeat',
    ],
    [
        'url'   => '/dashboard/tg-users',
        'title' => 'TG Users',
        'icon'  => 'bi bi-people-fill',
    ],
    [
        'url'   => '/dashboard/join-requests',
        'title' => 'Join Requests',
        'icon'  => 'bi bi-person-plus',
    ],
    [
        'url'   => '/dashboard/sessions',
        'title' => 'Sessions',
        'icon'  => 'bi bi-clock-history',
    ],
    [
        'url'   => '/dashboard/users',
        'title' => 'Users',
        'icon'  => 'bi bi-person-gear',
    ],
    [
        'url'   => '/dashboard/tokens',
        'title' => 'Tokens',
        'icon'  => 'bi bi-key',
    ],
    [
        'url'   => '/dashboard/system',
        'title' => 'System',
        'icon'  => 'bi bi-gear',
    ],
];

if (is_dir(__DIR__ . '/dashboard/metrics') || file_exists(__DIR__ . '/dashboard/metrics.php')) {
    $menu[] = [
        'url'   => '/dashboard/metrics',
        'title' => 'Metrics',
        'icon'  => 'bi bi-graph-up',
    ];
}

return $menu;
