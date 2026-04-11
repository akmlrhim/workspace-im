<?php

return [
	'modules' => [
		'project' => [
			'name' => 'Project',
			'icon' => 'folder-open',
			'url' => '/project-management',
			'sidebar' => [
				['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/dashboard', 'active' => 'dashboard', 'badge' => null],
				['name' => 'Spaces', 'icon' => 'folder', 'url' => '/project-management', 'active' => 'project-management', 'badge' => null],
				['name' => 'My Tasks', 'icon' => 'check-circle', 'url' => '/project-management/my-tasks', 'active' => 'project-management/my-tasks*', 'badge' => null],
			],

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
		],

		'users' => [
			'name' => 'Users',
			'icon' => 'users',
			'url' => '/users',
			'sidebar' => [
				['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/dashboard', 'active' => 'dashboard', 'badge' => null],
				['name' => 'Manajemen Pengguna', 'icon' => 'users', 'url' => '/users', 'active' => 'users*', 'badge' => null],
			],
		],
	],
];
