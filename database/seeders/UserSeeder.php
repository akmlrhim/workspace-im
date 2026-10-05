<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $password = Hash::make('password');

        $users = [
            // ── Super User ──────────────────────────────────────────
            [
                'name' => 'Super User',
                'email' => 'superuser@project.test',
                'role' => 'super_user',
                'position' => 'CEO',
            ],

            // ── Administrator ────────────────────────────────────────
            [
                'name' => 'Administrator',
                'email' => 'admin@project.test',
                'role' => 'administrator',
                'position' => 'Admin Operasional',
            ],

            // ── Manager ──────────────────────────────────────────────
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@project.test',
                'role' => 'manager',
                'position' => 'HR',
            ],
            [
                'name' => 'Siti Rahayu',
                'email' => 'siti.rahayu@project.test',
                'role' => 'manager',
                'position' => 'Finance',
            ],

            // ── Member ───────────────────────────────────────────────
            [
                'name' => 'Andi Firmansyah',
                'email' => 'andi.firmansyah@project.test',
                'role' => 'member',
                'position' => 'Web Developer',
            ],
            [
                'name' => 'Dewi Kurniawati',
                'email' => 'dewi.kurniawati@project.test',
                'role' => 'member',
                'position' => 'Desain Grafis',
            ],
            [
                'name' => 'Reza Pratama',
                'email' => 'reza.pratama@project.test',
                'role' => 'member',
                'position' => 'Video Editor',
            ],
            [
                'name' => 'Nisa Amalia',
                'email' => 'nisa.amalia@project.test',
                'role' => 'member',
                'position' => 'Performance Marketer',
            ],
            [
                'name' => 'Fajar Nugroho',
                'email' => 'fajar.nugroho@project.test',
                'role' => 'member',
                'position' => 'Advertiser',
            ],
            [
                'name' => 'Maya Sari',
                'email' => 'maya.sari@project.test',
                'role' => 'member',
                'position' => 'SMS',
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => $password,
                    'email_verified_at' => $now,
                ])
            );
        }
    }
}
