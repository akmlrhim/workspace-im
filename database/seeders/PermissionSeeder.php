<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->createPermissions();
        $this->createRoles();
        $this->assignFirstUserAsSuperAdmin();
    }

    /**
     * Create all permissions grouped by module.
     */
    private function createPermissions(): void
    {
        $permissions = [
            // ── Dashboard ──
            ['name' => 'dashboard.view', 'display_name' => 'Lihat Dashboard', 'group' => 'Dashboard', 'module' => 'dashboard'],

            // ── Project Management ──
            ['name' => 'project.view', 'display_name' => 'Lihat Project', 'group' => 'Project', 'module' => 'project'],
            ['name' => 'project.create', 'display_name' => 'Buat Project', 'group' => 'Project', 'module' => 'project'],
            ['name' => 'project.edit', 'display_name' => 'Edit Project', 'group' => 'Project', 'module' => 'project'],
            ['name' => 'project.delete', 'display_name' => 'Hapus Project', 'group' => 'Project', 'module' => 'project'],
            ['name' => 'project.manage-members', 'display_name' => 'Kelola Member Project', 'group' => 'Project', 'module' => 'project'],
            ['name' => 'project.tasks.view', 'display_name' => 'Lihat Tasks', 'group' => 'Tasks', 'module' => 'project'],
            ['name' => 'project.tasks.create', 'display_name' => 'Buat Task', 'group' => 'Tasks', 'module' => 'project'],
            ['name' => 'project.tasks.edit', 'display_name' => 'Edit Task', 'group' => 'Tasks', 'module' => 'project'],
            ['name' => 'project.tasks.delete', 'display_name' => 'Hapus Task', 'group' => 'Tasks', 'module' => 'project'],
            ['name' => 'project.tasks.assign', 'display_name' => 'Assign Task ke User', 'group' => 'Tasks', 'module' => 'project'],

            // ── HR ──
            ['name' => 'hr.view', 'display_name' => 'Akses Module HR', 'group' => 'HR Umum', 'module' => 'hr'],
            ['name' => 'hr.employees.view', 'display_name' => 'Lihat Data Karyawan', 'group' => 'Karyawan', 'module' => 'hr'],
            ['name' => 'hr.employees.create', 'display_name' => 'Tambah Karyawan', 'group' => 'Karyawan', 'module' => 'hr'],
            ['name' => 'hr.employees.edit', 'display_name' => 'Edit Karyawan', 'group' => 'Karyawan', 'module' => 'hr'],
            ['name' => 'hr.employees.delete', 'display_name' => 'Hapus Karyawan', 'group' => 'Karyawan', 'module' => 'hr'],
            ['name' => 'hr.payroll.view', 'display_name' => 'Lihat Payroll', 'group' => 'Payroll', 'module' => 'hr'],
            ['name' => 'hr.payroll.manage', 'display_name' => 'Kelola Payroll', 'group' => 'Payroll', 'module' => 'hr'],
            ['name' => 'hr.leaves.view', 'display_name' => 'Lihat Cuti', 'group' => 'Cuti', 'module' => 'hr'],
            ['name' => 'hr.leaves.manage', 'display_name' => 'Kelola Cuti', 'group' => 'Cuti', 'module' => 'hr'],
            ['name' => 'hr.leaves.approve', 'display_name' => 'Approve/Reject Cuti', 'group' => 'Cuti', 'module' => 'hr'],

            // ── Finance ──
            ['name' => 'finance.view', 'display_name' => 'Akses Module Finance', 'group' => 'Finance Umum', 'module' => 'finance'],
            ['name' => 'finance.invoices.view', 'display_name' => 'Lihat Invoice', 'group' => 'Invoice', 'module' => 'finance'],
            ['name' => 'finance.invoices.create', 'display_name' => 'Buat Invoice', 'group' => 'Invoice', 'module' => 'finance'],
            ['name' => 'finance.invoices.edit', 'display_name' => 'Edit Invoice', 'group' => 'Invoice', 'module' => 'finance'],
            ['name' => 'finance.invoices.delete', 'display_name' => 'Hapus Invoice', 'group' => 'Invoice', 'module' => 'finance'],
            ['name' => 'finance.expenses.view', 'display_name' => 'Lihat Pengeluaran', 'group' => 'Pengeluaran', 'module' => 'finance'],
            ['name' => 'finance.expenses.create', 'display_name' => 'Buat Pengeluaran', 'group' => 'Pengeluaran', 'module' => 'finance'],
            ['name' => 'finance.expenses.edit', 'display_name' => 'Edit Pengeluaran', 'group' => 'Pengeluaran', 'module' => 'finance'],
            ['name' => 'finance.expenses.approve', 'display_name' => 'Approve Pengeluaran', 'group' => 'Pengeluaran', 'module' => 'finance'],
            ['name' => 'finance.reports.view', 'display_name' => 'Lihat Laporan Keuangan', 'group' => 'Laporan', 'module' => 'finance'],
            ['name' => 'finance.reports.export', 'display_name' => 'Export Laporan Keuangan', 'group' => 'Laporan', 'module' => 'finance'],

            // ── Settings ──
            ['name' => 'settings.view', 'display_name' => 'Akses Settings', 'group' => 'Settings Umum', 'module' => 'settings'],
            ['name' => 'settings.roles.view', 'display_name' => 'Lihat Roles', 'group' => 'Roles & Permissions', 'module' => 'settings'],
            ['name' => 'settings.roles.create', 'display_name' => 'Buat Role', 'group' => 'Roles & Permissions', 'module' => 'settings'],
            ['name' => 'settings.roles.edit', 'display_name' => 'Edit Role', 'group' => 'Roles & Permissions', 'module' => 'settings'],
            ['name' => 'settings.roles.delete', 'display_name' => 'Hapus Role', 'group' => 'Roles & Permissions', 'module' => 'settings'],
            ['name' => 'settings.roles.assign-permissions', 'display_name' => 'Assign Permission ke Role', 'group' => 'Roles & Permissions', 'module' => 'settings'],
            ['name' => 'settings.users.view', 'display_name' => 'Lihat Daftar User', 'group' => 'Manajemen User', 'module' => 'settings'],
            ['name' => 'settings.users.assign-roles', 'display_name' => 'Assign Role ke User', 'group' => 'Manajemen User', 'module' => 'settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'description' => $permission['description'] ?? null,
                'group' => $permission['group'],
                'module' => $permission['module'],
                'guard_name' => 'web',
            ]);
        }
    }

    /**
     * Create default roles with permissions based on jabatan/position.
     */
    private function createRoles(): void
    {
        $allPermissions = Permission::all();

        // ── Super Admin (full access) ──
        $superAdmin = Role::create([
            'name' => 'super-admin',
            'display_name' => 'Super Admin',
            'description' => 'Akses penuh ke seluruh sistem tanpa batasan',
            'is_default' => false,
            'guard_name' => 'web',
        ]);
        $superAdmin->givePermissionTo($allPermissions);

        // ── CEO (akses semua module, lihat & approve, tidak manage settings teknis) ──
        $ceo = Role::create([
            'name' => 'ceo',
            'display_name' => 'CEO',
            'description' => 'Chief Executive Officer - akses penuh ke semua module bisnis',
            'is_default' => false,
            'guard_name' => 'web',
        ]);
        $ceo->givePermissionTo($allPermissions->filter(fn ($p) => ! str_starts_with($p->name, 'settings.roles.')));

        // ── BDS (Business Development Specialist) ──
        $bds = Role::create([
            'name' => 'bds',
            'display_name' => 'Business Development',
            'description' => 'Business Development Specialist - fokus project & finance',
            'is_default' => false,
            'guard_name' => 'web',
        ]);
        $bds->givePermissionTo($allPermissions->filter(fn ($p) => in_array($p->module, ['dashboard', 'project', 'finance'])
            || $p->name === 'hr.view'
            || $p->name === 'settings.view'));

        // ── Manager ──
        $manager = Role::create([
            'name' => 'manager',
            'display_name' => 'Manager',
            'description' => 'Manager departemen - kelola tim, approve & review',
            'is_default' => false,
            'guard_name' => 'web',
        ]);
        $manager->givePermissionTo($allPermissions->filter(fn ($p) => in_array($p->module, ['dashboard', 'project', 'hr'])
            || $p->name === 'finance.view'
            || $p->name === 'finance.reports.view'
            || $p->name === 'settings.view'));

        // ── Web Developer ──
        $webdev = Role::create([
            'name' => 'webdev',
            'display_name' => 'Web Developer',
            'description' => 'Web Developer - fokus project management & tasks',
            'is_default' => false,
            'guard_name' => 'web',
        ]);
        $webdev->givePermissionTo($allPermissions->filter(fn ($p) => in_array($p->module, ['dashboard', 'project'])
            || $p->name === 'hr.view'
            || $p->name === 'hr.leaves.view'
            || $p->name === 'settings.view'));

        // ── Staff (default role untuk karyawan baru) ──
        $staff = Role::create([
            'name' => 'staff',
            'display_name' => 'Staff',
            'description' => 'Karyawan biasa - akses dasar yang terbatas',
            'is_default' => true,
            'guard_name' => 'web',
        ]);
        $staff->givePermissionTo($allPermissions->filter(fn ($p) => in_array($p->name, [
            'dashboard.view',
            'project.view',
            'project.tasks.view',
            'project.tasks.create',
            'project.tasks.edit',
            'hr.view',
            'hr.leaves.view',
            'settings.view',
        ])));

        // ── HRD ──
        $hrd = Role::create([
            'name' => 'hrd',
            'display_name' => 'HRD',
            'description' => 'Human Resource Department - kelola karyawan & payroll',
            'is_default' => false,
            'guard_name' => 'web',
        ]);
        $hrd->givePermissionTo($allPermissions->filter(fn ($p) => $p->module === 'hr'
            || $p->module === 'dashboard'
            || $p->name === 'settings.view'));

        // ── Finance/Accounting ──
        $finance = Role::create([
            'name' => 'finance-staff',
            'display_name' => 'Finance / Accounting',
            'description' => 'Staff finance - kelola invoice, pengeluaran & laporan',
            'is_default' => false,
            'guard_name' => 'web',
        ]);
        $finance->givePermissionTo($allPermissions->filter(fn ($p) => $p->module === 'finance'
            || $p->module === 'dashboard'
            || $p->name === 'settings.view'));
    }

    /**
     * Assign the first user as super-admin if exists.
     */
    private function assignFirstUserAsSuperAdmin(): void
    {
        $user = \App\Models\User::first();

        if ($user) {
            $user->assignRole('super-admin');
        }
    }
}
