<?php

return [
    'modules' => [
        'project' => [
            'name' => 'Project',
            'icon' => 'folder-open',
            'url' => '/project-management',
            'sidebar' => [
                ['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/dashboard', 'active' => 'dashboard', 'badge' => null],
                ['name' => 'Home', 'icon' => 'home', 'url' => '/project-management', 'active' => 'project-management', 'badge' => null],
                ['name' => 'My Tasks', 'icon' => 'check-circle', 'url' => '/project-management/my-tasks', 'active' => 'project-management/my-tasks*', 'badge' => null],
            ],
            'favorites' => [],
        ],

        'hr' => [
            'name' => 'HR',
            'icon' => 'users',
            'url' => '/hr',
            'sidebar' => [
                ['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/dashboard', 'active' => 'dashboard', 'badge' => null],
                ['name' => 'Home', 'icon' => 'home', 'url' => '/hr', 'active' => 'hr*', 'badge' => null],
                ['name' => 'Karyawan', 'icon' => 'users', 'url' => '#', 'active' => 'hr/employees*', 'badge' => null],
                ['name' => 'Payroll', 'icon' => 'banknotes', 'url' => '#', 'active' => 'hr/payroll*', 'badge' => null],
                ['name' => 'Cuti', 'icon' => 'calendar-days', 'url' => '#', 'active' => 'hr/leaves*', 'badge' => '3'],
            ],
            'favorites' => [],
        ],

        'finance' => [
            'name' => 'Finance',
            'icon' => 'banknotes',
            'url' => '/finance',
            'sidebar' => [
                ['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/finance', 'active' => 'finance', 'badge' => null],
                ['name' => 'Home', 'icon' => 'home', 'url' => '/finance', 'active' => 'finance*', 'badge' => null],
                ['name' => 'Invoice', 'icon' => 'document-duplicate', 'url' => '#', 'active' => 'finance/invoices*', 'badge' => null],
                ['name' => 'Pengeluaran', 'icon' => 'credit-card', 'url' => '#', 'active' => 'finance/expenses*', 'badge' => null],
                ['name' => 'Laporan', 'icon' => 'chart-bar', 'url' => '#', 'active' => 'finance/reports*', 'badge' => null],
            ],
            'favorites' => [],
        ],

        'users' => [
            'name' => 'Users',
            'icon' => 'users',
            'url' => '/user',
            'sidebar' => [
                ['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/user', 'active' => 'user', 'badge' => null],
                ['name' => 'Home', 'icon' => 'home', 'url' => '/user', 'active' => 'user*', 'badge' => null],
                ['name' => 'Profile', 'icon' => 'user', 'url' => '#', 'active' => 'user/profile*', 'badge' => null],
                ['name' => 'Settings', 'icon' => 'cog', 'url' => '#', 'active' => 'user/settings*', 'badge' => null],
                ['name' => 'Admin Panel', 'icon' => 'shield-check', 'url' => '#', 'active' => 'user/admin*', 'badge' => null],
            ],
            'favorites' => [],
        ],
    ],
];
