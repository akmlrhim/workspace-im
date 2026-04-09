<?php

namespace Database\Seeders;

use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating 50 dummy users...');

        // Generate 50 users
        $users = User::factory(50)->create();

        // Assign them to the first workspace if it exists
        $workspace = Workspace::first();

        if ($workspace) {
            $this->command->info("Assigning users to workspace: {$workspace->name}");

            $membersData = $users->map(function ($user) use ($workspace) {
                return [
                    'workspace_id' => $workspace->id,
                    'user_id' => $user->id,
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            WorkspaceMember::insert($membersData);
        }

        $this->command->info('50 users created successfully!');
    }
}
