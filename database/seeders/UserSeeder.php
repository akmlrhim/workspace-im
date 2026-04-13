<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        // User::factory()->create([
        // 	'name' => 'Admin',
        // 	'email' => 'admin@example.com',
        // 	'role' => 'super_user',
        // 	'position' => 'CEO',
        // ]);

        // Manager
        // User::factory()->create([
        // 	'name' => 'Budi Santoso',
        // 	'email' => 'budi@example.com',
        // 	'role' => 'manager',
        // 	'position' => 'Admin',
        // ]);

        // Regular members
        // $members = [
        // 	['name' => 'Siti Rahayu', 'email' => 'siti@example.com', 'position' => 'Kreatif'],
        // 	['name' => 'Ahmad Pratama', 'email' => 'ahmad@example.com', 'position' => 'Admin'],
        // 	['name' => 'Dewi Lestari', 'email' => 'dewi@example.com', 'position' => 'Finance'],
        // 	['name' => 'Rizky Hidayat', 'email' => 'rizky@example.com', 'position' => 'Kreatif'],
        // 	['name' => 'Putri Wulandari', 'email' => 'putri@example.com', 'position' => 'HR'],
        // 	['name' => 'Fajar Nugroho', 'email' => 'fajar@example.com', 'position' => 'Admin'],
        // 	['name' => 'Indah Permata', 'email' => 'indah@example.com', 'position' => 'Kreatif'],
        // 	['name' => 'Dian Saputra', 'email' => 'dian@example.com', 'position' => 'Finance'],
        // ];

        // foreach ($members as $member) {
        // 	User::factory()->create(array_merge($member, ['role' => 'member']));
        // }

        // $this->command->info('10 users created (1 super_user, 1 manager, 8 members).');

        User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmadalfarizi@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::uuid(),
            'role' => 'member',
            'position' => 'CEO',
        ]);
    }
}
