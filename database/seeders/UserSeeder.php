<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class UserSeeder extends Seeder
{
	public function run(): void
	{
		User::factory()->create([
			'name' => 'Super User',
			'email' => 'superuser@erp.test',
			'role' => 'super_user',
			'email_verified_at' => Carbon::now(),
		]);

		User::factory()->create([
			'name' => 'Administrator',
			'email' => 'admin@erp.test',
			'role' => 'administrator',
			'email_verified_at' => Carbon::now(),
		]);

		User::factory()->create([
			'name' => 'Manager',
			'email' => 'manager@erp.test',
			'role' => 'manager',
			'email_verified_at' => Carbon::now(),
		]);

		User::factory()->create([
			'name' => 'Member',
			'email' => 'member@erp.test',
			'role' => 'member',
			'email_verified_at' => Carbon::now(),
		]);
	}
}
