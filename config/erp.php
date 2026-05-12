<?php

return [
    'modules' => [
        'project' => [
            'name' => 'Project',
            'icon' => 'clipboard-document-list',
            'url' => '/general-taskboard',
            'color' => 'indigo',
            'description' => 'Kelola space, task list, dan tugas tim Anda.',
            'allowed_positions' => [],
            'sidebar' => [
                ['name' => 'General', 'icon' => 'star', 'url' => '/general-taskboard', 'active' => 'general-taskboard*', 'badge' => null],
                ['name' => 'My Tasks', 'icon' => 'clipboard-document-check', 'url' => '/my-tasks', 'active' => 'my-tasks*', 'badge' => null],
                ['name' => 'Workload', 'icon' => 'chart-bar-square', 'url' => '/workload', 'active' => 'workload*', 'badge' => null],
            ],
        ],

        'users' => [
            'name' => 'Users',
            'icon' => 'users',
            'url' => '/users',
            'color' => 'sky',
            'description' => 'Kelola data admin dan permission sistem.',
            'allowed_positions' => ['CEO'],
            'sidebar' => [
                ['name' => 'Users', 'icon' => 'users', 'url' => '/users', 'active' => 'users*', 'badge' => null],
            ],
        ],
    ],
];
