<?php

namespace Database\Seeders;

use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskLabel;
use App\Models\Project\TaskList;
use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
	public function run(): void
	{
		$user = User::first();

		if (! $user) {
			$this->command->warn('No user found. Skipping ProjectSeeder.');
			return;
		}

		// Create workspace
		$workspace = Workspace::firstOrCreate(
			['owner_id' => $user->id],
			['name' => $user->name . "'s Workspace"]
		);

		WorkspaceMember::firstOrCreate([
			'workspace_id' => $workspace->id,
			'user_id' => $user->id,
		], ['role' => 'owner']);

		// Create labels
		$labels = [];
		$labelData = [
			['name' => 'Bug', 'color' => '#ef4444'],
			['name' => 'Feature', 'color' => '#3b82f6'],
			['name' => 'Improvement', 'color' => '#10b981'],
			['name' => 'Design', 'color' => '#8b5cf6'],
			['name' => 'Documentation', 'color' => '#f59e0b'],
		];
		foreach ($labelData as $ld) {
			$labels[] = TaskLabel::firstOrCreate([
				'workspace_id' => $workspace->id,
				'name' => $ld['name'],
			], ['color' => $ld['color']]);
		}

		// --- Space: Engineering ---
		$engineering = Space::firstOrCreate([
			'workspace_id' => $workspace->id,
			'slug' => 'engineering',
		], [
			'name' => 'Engineering',
			'color' => '#6366f1',
			'icon' => 'code-bracket',
			'position' => 0,
		]);

		$sprint1 = TaskList::firstOrCreate([
			'space_id' => $engineering->id,
			'name' => 'Sprint 1',
		], ['position' => 0]);

		if ($sprint1->statuses()->count() === 0) {
			$sprint1->createDefaultStatuses();
		}
		$sprint1Statuses = $sprint1->statuses()->orderBy('position')->get();

		$backlog = TaskList::firstOrCreate([
			'space_id' => $engineering->id,
			'name' => 'Backlog',
		], ['position' => 1]);

		if ($backlog->statuses()->count() === 0) {
			$backlog->createDefaultStatuses();
		}
		$backlogStatuses = $backlog->statuses()->orderBy('position')->get();

		// Tasks for Sprint 1
		$sprint1Tasks = [
			['title' => 'Set up CI/CD pipeline', 'priority' => 'high', 'status_idx' => 3, 'due' => now()->addDays(2)],
			['title' => 'Implement user authentication', 'priority' => 'urgent', 'status_idx' => 2, 'due' => now()->addDay()],
			['title' => 'Design database schema', 'priority' => 'normal', 'status_idx' => 3, 'due' => null],
			['title' => 'Create API endpoints for tasks', 'priority' => 'high', 'status_idx' => 1, 'due' => now()->addDays(5)],
			['title' => 'Write unit tests for auth module', 'priority' => 'normal', 'status_idx' => 0, 'due' => now()->addDays(7)],
			['title' => 'Set up error tracking (Sentry)', 'priority' => 'low', 'status_idx' => 0, 'due' => now()->addDays(10)],
			['title' => 'Optimize database queries', 'priority' => 'high', 'status_idx' => 1, 'due' => now()->addDays(3)],
			['title' => 'Implement file upload system', 'priority' => 'normal', 'status_idx' => 0, 'due' => now()->addDays(8)],
		];

		foreach ($sprint1Tasks as $i => $t) {
			$task = Task::firstOrCreate([
				'task_list_id' => $sprint1->id,
				'title' => $t['title'],
			], [
				'task_status_id' => $sprint1Statuses[$t['status_idx']]->id,
				'priority' => $t['priority'],
				'assigned_to' => $user->id,
				'due_date' => $t['due'],
				'position' => $i,
				'created_by' => $user->id,
			]);

			// Attach random labels
			if ($i < 3) {
				$task->labels()->syncWithoutDetaching([$labels[array_rand($labels)]->id]);
			}
		}

		// Tasks for Backlog
		$backlogTasks = [
			['title' => 'Investigate WebSocket integration', 'priority' => 'low'],
			['title' => 'Research caching strategies', 'priority' => 'normal'],
			['title' => 'Plan mobile responsive design', 'priority' => 'normal'],
			['title' => 'Add dark mode support', 'priority' => 'low'],
			['title' => 'Create onboarding wizard', 'priority' => 'high'],
		];

		foreach ($backlogTasks as $i => $t) {
			Task::firstOrCreate([
				'task_list_id' => $backlog->id,
				'title' => $t['title'],
			], [
				'task_status_id' => $backlogStatuses[0]->id,
				'priority' => $t['priority'],
				'position' => $i,
				'created_by' => $user->id,
			]);
		}

		// --- Space: Marketing ---
		$marketing = Space::firstOrCreate([
			'workspace_id' => $workspace->id,
			'slug' => 'marketing',
		], [
			'name' => 'Marketing',
			'color' => '#ec4899',
			'icon' => 'megaphone',
			'position' => 1,
		]);

		$campaigns = TaskList::firstOrCreate([
			'space_id' => $marketing->id,
			'name' => 'Q2 Campaigns',
		], ['position' => 0]);

		if ($campaigns->statuses()->count() === 0) {
			$campaigns->createDefaultStatuses();
		}
		$campaignStatuses = $campaigns->statuses()->orderBy('position')->get();

		$marketingTasks = [
			['title' => 'Create social media content calendar', 'priority' => 'high', 'status_idx' => 1],
			['title' => 'Design landing page for product launch', 'priority' => 'urgent', 'status_idx' => 1],
			['title' => 'Write blog post: Getting Started Guide', 'priority' => 'normal', 'status_idx' => 0],
			['title' => 'Prepare email newsletter', 'priority' => 'normal', 'status_idx' => 2],
			['title' => 'Analyze competitor SEO strategies', 'priority' => 'low', 'status_idx' => 0],
		];

		foreach ($marketingTasks as $i => $t) {
			Task::firstOrCreate([
				'task_list_id' => $campaigns->id,
				'title' => $t['title'],
			], [
				'task_status_id' => $campaignStatuses[$t['status_idx']]->id,
				'priority' => $t['priority'],
				'assigned_to' => $user->id,
				'due_date' => now()->addDays(rand(2, 14)),
				'position' => $i,
				'created_by' => $user->id,
			]);
		}

		// --- Space: Design ---
		$design = Space::firstOrCreate([
			'workspace_id' => $workspace->id,
			'slug' => 'design',
		], [
			'name' => 'Design',
			'color' => '#f59e0b',
			'icon' => 'paint-brush',
			'position' => 2,
		]);

		$uiKit = TaskList::firstOrCreate([
			'space_id' => $design->id,
			'name' => 'UI Kit v2',
		], ['position' => 0]);

		if ($uiKit->statuses()->count() === 0) {
			$uiKit->createDefaultStatuses();
		}
		$uiKitStatuses = $uiKit->statuses()->orderBy('position')->get();

		$designTasks = [
			['title' => 'Create button component variants', 'priority' => 'high', 'status_idx' => 3],
			['title' => 'Design modal patterns', 'priority' => 'normal', 'status_idx' => 2],
			['title' => 'Create form input styles', 'priority' => 'normal', 'status_idx' => 1],
			['title' => 'Design notification system', 'priority' => 'high', 'status_idx' => 0],
			['title' => 'Create illustration library', 'priority' => 'low', 'status_idx' => 0],
			['title' => 'Design empty state patterns', 'priority' => 'normal', 'status_idx' => 0],
		];

		foreach ($designTasks as $i => $t) {
			$task = Task::firstOrCreate([
				'task_list_id' => $uiKit->id,
				'title' => $t['title'],
			], [
				'task_status_id' => $uiKitStatuses[$t['status_idx']]->id,
				'priority' => $t['priority'],
				'assigned_to' => $user->id,
				'due_date' => now()->addDays(rand(3, 20)),
				'position' => $i,
				'created_by' => $user->id,
			]);

			if ($i < 2) {
				$task->labels()->syncWithoutDetaching([$labels[3]->id]); // Design label
			}
		}

		$this->command->info('Project management demo data seeded successfully!');
	}
}
