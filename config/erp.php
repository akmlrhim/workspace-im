<?php

return [
    'modules' => [
        'project' => [
            'name' => 'Project',
            'icon' => 'clipboard-document-list',
            'url' => '/project-management',
            'color' => 'indigo',
            'description' => 'Kelola space, task list, dan tugas tim Anda.',
            'allowed_positions' => [],
            'sidebar' => [
                ['name' => 'Dashboard', 'icon' => 'squares-2x2', 'url' => '/dashboard?module=project', 'active' => 'dashboard', 'badge' => null],
                ['name' => 'Workload', 'icon' => 'chart-bar-square', 'url' => '/project-management/workload', 'active' => 'project-management/workload*', 'badge' => null],
                ['name' => 'General', 'icon' => 'star', 'url' => '/project-management/general-taskboard', 'active' => 'project-management/general-taskboard*', 'badge' => null],
                ['name' => 'Spaces', 'icon' => 'rectangle-stack', 'url' => '/project-management/spaces', 'active' => 'project-management/spaces*', 'badge' => null],
                ['name' => 'My Tasks', 'icon' => 'clipboard-document-check', 'url' => '/project-management/my-tasks', 'active' => 'project-management/my-tasks*', 'badge' => null],
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
                ['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/dashboard?module=users', 'active' => 'dashboard', 'badge' => null],
                ['name' => 'Users', 'icon' => 'users', 'url' => '/users', 'active' => 'users*', 'badge' => null],
            ],
        ],
    ],
];
