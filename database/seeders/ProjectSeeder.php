<?php

namespace Database\Seeders;

use App\Models\Project\Folder;
use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskLabel;
use App\Models\Project\TaskList;
use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();
        $manager = User::where('email', 'budi@example.com')->first();
        $allUsers = User::all();

        if (! $admin) {
            $this->command->warn('No admin user found. Run UserSeeder first.');

            return;
        }

        // ── Workspace ──────────────────────────────────────────
        $workspace = Workspace::firstOrCreate(
            ['owner_id' => $admin->id],
            ['name' => 'PT Inovasi Mandiri']
        );

        foreach ($allUsers as $user) {
            WorkspaceMember::firstOrCreate([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
            ], [
                'role' => $user->id === $admin->id ? 'owner' : 'member',
            ]);
        }

        // ── Labels ─────────────────────────────────────────────
        $labelData = [
            ['name' => 'Bug', 'color' => '#ef4444'],
            ['name' => 'Feature', 'color' => '#3b82f6'],
            ['name' => 'Improvement', 'color' => '#10b981'],
            ['name' => 'Design', 'color' => '#8b5cf6'],
            ['name' => 'Documentation', 'color' => '#f59e0b'],
            ['name' => 'Urgent', 'color' => '#dc2626'],
        ];

        $labels = [];
        foreach ($labelData as $ld) {
            $labels[$ld['name']] = TaskLabel::firstOrCreate(
                ['workspace_id' => $workspace->id, 'name' => $ld['name']],
                ['color' => $ld['color']]
            );
        }

        // ── Space: Engineering ─────────────────────────────────
        $engineering = Space::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'engineering'],
            ['name' => 'Engineering', 'color' => '#6366f1', 'icon' => 'code-bracket', 'position' => 0]
        );

        // Folder: Sprint Q2
        $sprintFolder = Folder::firstOrCreate(
            ['space_id' => $engineering->id, 'name' => 'Sprint Q2 2026'],
            ['position' => 0]
        );

        $sprint1 = $this->createList($engineering, 'Sprint 1', 0, $sprintFolder->id);
        $sprint2 = $this->createList($engineering, 'Sprint 2', 1, $sprintFolder->id);
        $backlog = $this->createList($engineering, 'Backlog', 2);

        // Assign members to lists
        $engMembers = $allUsers->whereIn('email', ['admin@example.com', 'budi@example.com', 'siti@example.com', 'ahmad@example.com', 'rizky@example.com']);
        $sprint1->members()->syncWithoutDetaching($engMembers->pluck('id'));
        $sprint2->members()->syncWithoutDetaching($engMembers->pluck('id'));
        $backlog->members()->syncWithoutDetaching($engMembers->pluck('id'));

        $s1Statuses = $sprint1->statuses()->orderBy('position')->get();
        $this->seedTasks($sprint1, $s1Statuses, $admin, [
            ['title' => 'Set up CI/CD pipeline', 'priority' => 'high', 'si' => 3, 'due' => 2, 'labels' => ['Feature']],
            ['title' => 'Implement user authentication', 'priority' => 'urgent', 'si' => 2, 'due' => 1, 'labels' => ['Feature', 'Urgent']],
            ['title' => 'Design database schema', 'priority' => 'normal', 'si' => 3, 'due' => null],
            ['title' => 'Create API endpoints for tasks', 'priority' => 'high', 'si' => 1, 'due' => 5, 'labels' => ['Feature']],
            ['title' => 'Write unit tests for auth module', 'priority' => 'normal', 'si' => 0, 'due' => 7],
            ['title' => 'Set up error tracking (Sentry)', 'priority' => 'low', 'si' => 0, 'due' => 10],
            ['title' => 'Optimize database queries', 'priority' => 'high', 'si' => 1, 'due' => 3, 'labels' => ['Improvement']],
            ['title' => 'Implement file upload system', 'priority' => 'normal', 'si' => 0, 'due' => 8, 'labels' => ['Feature']],
        ], $labels, $engMembers->values());

        $s2Statuses = $sprint2->statuses()->orderBy('position')->get();
        $this->seedTasks($sprint2, $s2Statuses, $admin, [
            ['title' => 'Build notification system', 'priority' => 'high', 'si' => 0, 'due' => 12, 'labels' => ['Feature']],
            ['title' => 'Add real-time updates via WebSocket', 'priority' => 'normal', 'si' => 0, 'due' => 15],
            ['title' => 'Implement role-based access control', 'priority' => 'urgent', 'si' => 1, 'due' => 10, 'labels' => ['Feature', 'Urgent']],
            ['title' => 'Create audit log module', 'priority' => 'normal', 'si' => 0, 'due' => 18],
            ['title' => 'Performance profiling & optimization', 'priority' => 'high', 'si' => 0, 'due' => 20, 'labels' => ['Improvement']],
        ], $labels, $engMembers->values());

        $blStatuses = $backlog->statuses()->orderBy('position')->get();
        $this->seedTasks($backlog, $blStatuses, $admin, [
            ['title' => 'Investigate caching strategies', 'priority' => 'low', 'si' => 0, 'due' => null],
            ['title' => 'Plan mobile responsive design', 'priority' => 'normal', 'si' => 0, 'due' => null],
            ['title' => 'Add dark mode support', 'priority' => 'low', 'si' => 0, 'due' => null, 'labels' => ['Design']],
            ['title' => 'Create onboarding wizard', 'priority' => 'high', 'si' => 0, 'due' => null, 'labels' => ['Feature']],
        ], $labels, $engMembers->values());

        // ── Space: Marketing ───────────────────────────────────
        $marketing = Space::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'marketing'],
            ['name' => 'Marketing', 'color' => '#ec4899', 'icon' => 'megaphone', 'position' => 1]
        );

        $campaigns = $this->createList($marketing, 'Q2 Campaigns', 0);
        $contentPlan = $this->createList($marketing, 'Content Plan', 1);

        $mktMembers = $allUsers->whereIn('email', ['admin@example.com', 'dewi@example.com', 'putri@example.com', 'indah@example.com']);
        $campaigns->members()->syncWithoutDetaching($mktMembers->pluck('id'));
        $contentPlan->members()->syncWithoutDetaching($mktMembers->pluck('id'));

        $cStatuses = $campaigns->statuses()->orderBy('position')->get();
        $this->seedTasks($campaigns, $cStatuses, $admin, [
            ['title' => 'Create social media content calendar', 'priority' => 'high', 'si' => 1, 'due' => 5],
            ['title' => 'Design landing page for product launch', 'priority' => 'urgent', 'si' => 1, 'due' => 3, 'labels' => ['Design', 'Urgent']],
            ['title' => 'Write blog post: Getting Started Guide', 'priority' => 'normal', 'si' => 0, 'due' => 10, 'labels' => ['Documentation']],
            ['title' => 'Prepare email newsletter', 'priority' => 'normal', 'si' => 2, 'due' => 7],
            ['title' => 'Analyze competitor SEO strategies', 'priority' => 'low', 'si' => 0, 'due' => 14],
        ], $labels, $mktMembers->values());

        $cpStatuses = $contentPlan->statuses()->orderBy('position')->get();
        $this->seedTasks($contentPlan, $cpStatuses, $admin, [
            ['title' => 'Draft product announcement article', 'priority' => 'high', 'si' => 1, 'due' => 4, 'labels' => ['Documentation']],
            ['title' => 'Create tutorial video script', 'priority' => 'normal', 'si' => 0, 'due' => 12],
            ['title' => 'Design infographic for features', 'priority' => 'normal', 'si' => 0, 'due' => 15, 'labels' => ['Design']],
        ], $labels, $mktMembers->values());

        // ── Space: Design ──────────────────────────────────────
        $design = Space::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'design'],
            ['name' => 'Design', 'color' => '#f59e0b', 'icon' => 'paint-brush', 'position' => 2]
        );

        // Folder: UI Components
        $uiFolder = Folder::firstOrCreate(
            ['space_id' => $design->id, 'name' => 'UI Components'],
            ['position' => 0]
        );

        $uiKit = $this->createList($design, 'UI Kit v2', 0, $uiFolder->id);
        $brandGuide = $this->createList($design, 'Brand Guidelines', 1);

        $dsMembers = $allUsers->whereIn('email', ['admin@example.com', 'siti@example.com', 'indah@example.com', 'rizky@example.com']);
        $uiKit->members()->syncWithoutDetaching($dsMembers->pluck('id'));
        $brandGuide->members()->syncWithoutDetaching($dsMembers->pluck('id'));

        $uiStatuses = $uiKit->statuses()->orderBy('position')->get();
        $this->seedTasks($uiKit, $uiStatuses, $admin, [
            ['title' => 'Create button component variants', 'priority' => 'high', 'si' => 3, 'due' => 3, 'labels' => ['Design']],
            ['title' => 'Design modal patterns', 'priority' => 'normal', 'si' => 2, 'due' => 7, 'labels' => ['Design']],
            ['title' => 'Create form input styles', 'priority' => 'normal', 'si' => 1, 'due' => 10, 'labels' => ['Design']],
            ['title' => 'Design notification system UI', 'priority' => 'high', 'si' => 0, 'due' => 12, 'labels' => ['Design', 'Feature']],
            ['title' => 'Create illustration library', 'priority' => 'low', 'si' => 0, 'due' => 20, 'labels' => ['Design']],
            ['title' => 'Design empty state patterns', 'priority' => 'normal', 'si' => 0, 'due' => 15, 'labels' => ['Design']],
        ], $labels, $dsMembers->values());

        $bgStatuses = $brandGuide->statuses()->orderBy('position')->get();
        $this->seedTasks($brandGuide, $bgStatuses, $admin, [
            ['title' => 'Define color palette', 'priority' => 'high', 'si' => 3, 'due' => null, 'labels' => ['Design']],
            ['title' => 'Create typography guidelines', 'priority' => 'normal', 'si' => 2, 'due' => null, 'labels' => ['Design', 'Documentation']],
            ['title' => 'Design logo variations', 'priority' => 'normal', 'si' => 1, 'due' => 10, 'labels' => ['Design']],
        ], $labels, $dsMembers->values());

        // ── Space: HR & Operations ─────────────────────────────
        $hr = Space::firstOrCreate(
            ['workspace_id' => $workspace->id, 'slug' => 'hr-operations'],
            ['name' => 'HR & Operations', 'color' => '#14b8a6', 'icon' => 'users', 'position' => 3]
        );

        $onboarding = $this->createList($hr, 'Onboarding Checklist', 0);
        $hrMembers = $allUsers->whereIn('email', ['admin@example.com', 'budi@example.com', 'putri@example.com', 'fajar@example.com']);
        $onboarding->members()->syncWithoutDetaching($hrMembers->pluck('id'));

        $obStatuses = $onboarding->statuses()->orderBy('position')->get();
        $this->seedTasks($onboarding, $obStatuses, $admin, [
            ['title' => 'Prepare offer letter template', 'priority' => 'high', 'si' => 3, 'due' => null],
            ['title' => 'Set up new employee workspace', 'priority' => 'normal', 'si' => 2, 'due' => 5],
            ['title' => 'Create training schedule', 'priority' => 'normal', 'si' => 1, 'due' => 7],
            ['title' => 'Review benefits documentation', 'priority' => 'low', 'si' => 0, 'due' => 14],
            ['title' => 'Organize team introduction meeting', 'priority' => 'normal', 'si' => 0, 'due' => 3],
        ], $labels, $hrMembers->values());

        $this->command->info('Project demo data seeded: 4 spaces, 8 lists, ~40 tasks with members and labels.');
    }

    private function createList(Space $space, string $name, int $position, ?int $folderId = null): TaskList
    {
        $list = TaskList::firstOrCreate(
            ['space_id' => $space->id, 'name' => $name],
            ['position' => $position, 'folder_id' => $folderId]
        );

        if ($list->statuses()->count() === 0) {
            $list->createDefaultStatuses();
        }

        return $list;
    }

    /**
     * @param  array<string, TaskLabel>  $allLabels
     * @param  Collection<int, User>  $members
     */
    private function seedTasks(TaskList $list, $statuses, User $creator, array $taskData, array $allLabels, $members): void
    {
        foreach ($taskData as $i => $t) {
            $task = Task::firstOrCreate(
                ['task_list_id' => $list->id, 'title' => $t['title']],
                [
                    'task_status_id' => $statuses[$t['si']]->id,
                    'priority' => $t['priority'],
                    'assigned_to' => $members->count() > 0 ? $members[$i % $members->count()]->id : $creator->id,
                    'due_date' => isset($t['due']) ? now()->addDays($t['due']) : null,
                    'position' => $i,
                    'created_by' => $creator->id,
                ]
            );

            // Assign 1-2 random members to the task
            $assigneeCount = min($members->count(), rand(1, 2));
            $task->assignees()->syncWithoutDetaching(
                $members->random($assigneeCount)->pluck('id')
            );

            // Attach labels
            if (! empty($t['labels'])) {
                $labelIds = collect($t['labels'])
                    ->map(fn ($name) => $allLabels[$name]->id ?? null)
                    ->filter()
                    ->toArray();
                $task->labels()->syncWithoutDetaching($labelIds);
            }
        }
    }
}
