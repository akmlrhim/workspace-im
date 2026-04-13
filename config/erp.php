<?php

return [
	'modules' => [
		'project' => [
			'name' => 'Project',
			'icon' => 'clipboard-document-list',
			'url' => '/project-management',
			'allowed_roles' => [],
			'allowed_positions' => [],
			'sidebar' => [
				['name' => 'Dashboard', 'icon' => 'squares-2x2', 'url' => '/dashboard?module=project', 'active' => 'dashboard', 'badge' => null],
				['name' => 'General', 'icon' => 'star', 'url' => '/project-management/general-taskboard', 'active' => 'project-management/general-taskboard*', 'badge' => null],
				['name' => 'Spaces', 'icon' => 'rectangle-stack', 'url' => '/project-management/spaces', 'active' => 'project-management/spaces*', 'badge' => null],
				['name' => 'My Tasks', 'icon' => 'clipboard-document-check', 'url' => '/project-management/my-tasks', 'active' => 'project-management/my-tasks*', 'badge' => null],
			],
		],

		'hr' => [
			'name' => 'HR',
			'icon' => 'users',
			'url' => '/hr',
			'allowed_roles' => [],
			'allowed_positions' => ['CEO', 'HR'],
			'sidebar' => [
				['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/dashboard?module=hr', 'active' => 'dashboard', 'badge' => null],
				['name' => 'Home', 'icon' => 'home', 'url' => '/hr', 'active' => 'hr/*', 'badge' => null],
				['name' => 'Karyawan', 'icon' => 'users', 'url' => '#', 'active' => 'hr/employees*', 'badge' => null],
				['name' => 'Payroll', 'icon' => 'banknotes', 'url' => '#', 'active' => 'hr/payroll*', 'badge' => null],
				['name' => 'Cuti', 'icon' => 'calendar-days', 'url' => '#', 'active' => 'hr/leaves*', 'badge' => '3'],
			],
		],

		'finance' => [
			'name' => 'Finance',
			'icon' => 'banknotes',
			'url' => '/finance',
			'allowed_roles' => [],
			'allowed_positions' => ['CEO', 'Finance'],
			'sidebar' => [
				['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/dashboard?module=finance', 'active' => 'dashboard', 'badge' => null],
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
			'allowed_roles' => [],
			'allowed_positions' => ['CEO'],
			'sidebar' => [
				['name' => 'Dashboard', 'icon' => 'layout-grid', 'url' => '/dashboard?module=users', 'active' => 'dashboard', 'badge' => null],
				['name' => 'Users', 'icon' => 'users', 'url' => '/users', 'active' => 'users*', 'badge' => null],
			],
		],
	],
];
