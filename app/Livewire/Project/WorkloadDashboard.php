<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Task;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Workload Dashboard')]
class WorkloadDashboard extends Component
{
    public string $view = 'task';

    public string $selectedMonth = '';

    public ?int $selectedSpaceId = null;

    public function mount(): void
    {
        $this->selectedMonth = now()->format('Y-m');
    }

    public function getListeners(): array
    {
        return [
            'echo:project,TaskListUpdated' => 'onBroadcastUpdate',
            'echo:project,TaskUpdated' => 'onBroadcastUpdate',
            'echo:project,SpaceUpdated' => 'onBroadcastUpdate',
        ];
    }

    public function onBroadcastUpdate(): void
    {
        // Always re-render — this is a read-only summary dashboard that must
        // reflect current state regardless of who triggered the change.
    }

    #[Computed]
    public function spaces()
    {
        return Space::orderBy('position')->get();
    }

    public function switchView(string $view): void
    {
        $this->view = $view;
    }

    public function selectSpace(?int $spaceId): void
    {
        $this->selectedSpaceId = $spaceId;
    }

    public function render()
    {
        $defaults = [
            'totalTasks' => 0,
            'completedTasks' => 0,
            'completedOnTime' => 0,
            'completedLate' => 0,
            'completedNoDeadline' => 0,
            'latePercent' => 0,
            'noDeadlinePercent' => 0,
            'overdueTasks' => 0,
            'overduePercent' => 0,
            'progressPercent' => 0,

            'memberStats' => collect(),
            'memberTraffic' => [],
            'totalMembers' => 0,
            'taskGroups' => collect(),
            'spaces' => $this->spaces,
        ];

        $data = match ($this->view) {
            'member' => array_merge($defaults, $this->getMemberViewData()),
            'task' => array_merge($defaults, $this->getTaskViewData()),
            default => $defaults,
        };

        return view('livewire.project.workload-dashboard', $data);
    }

    private function getMemberViewData(): array
    {
        $tasks = Task::whereDoesntHave('status', fn ($q) => $q->whereIn('name', ['Note', 'note']))
            ->tap(fn ($q) => $this->applyMonthFilter($q))
            ->tap(fn ($q) => $this->applySpaceFilter($q))
            ->with(['assignees', 'status', 'taskList.space', 'timeTrackings'])
            ->get();

        $memberStats = collect();
        $allAssignees = $tasks->flatMap->assignees->unique('id');

        foreach ($allAssignees as $user) {
            $userTasks = $tasks->filter(fn (Task $t) => $t->assignees->contains('id', $user->id));
            $total = $userTasks->count();
            $completed = $userTasks->filter(fn (Task $t) => $t->status?->type === 'closed')->count();
            $overdue = $userTasks->filter(fn (Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->toDateString() < now()->toDateString())->count();
            $progress = $total > 0 ? round(($completed / $total) * 100) : 0;

            $totalSeconds = $userTasks->flatMap->timeTrackings
                ->where('user_id', $user->id)
                ->sum('duration_seconds');

            $statusBreakdown = $userTasks->groupBy(fn (Task $t) => $t->status?->type ?? 'open');

            $memberStats->push([
                'user' => $user,
                'total' => $total,
                'completed' => $completed,
                'overdue' => $overdue,
                'progress' => $progress,
                'hours' => round($totalSeconds / 3600, 1),
                'open' => $statusBreakdown->get('open', collect())->count(),
                'active' => $statusBreakdown->get('active', collect())->count(),
                'closed' => $statusBreakdown->get('closed', collect())->count(),
            ]);
        }

        $memberStats = $memberStats->sort(function ($a, $b) {
            if ($b['completed'] !== $a['completed']) {
                return $b['completed'] <=> $a['completed'];
            }

            return $b['progress'] <=> $a['progress'];
        })->values();

        $memberTraffic = $memberStats->take(6)->map(fn ($m) => [
            'name' => $m['user']->name,
            'open' => $m['open'],
            'active' => $m['active'],
            'closed' => $m['closed'],
        ])->toArray();

        return [
            'memberStats' => $memberStats,
            'memberTraffic' => $memberTraffic,
            'totalMembers' => $allAssignees->count(),
            'totalTasks' => $tasks->count(),
            'completedTasks' => $tasks->filter(fn (Task $t) => $t->status?->type === 'closed')->count(),
            'overdueTasks' => $tasks->filter(fn (Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->toDateString() < now()->toDateString())->count(),
        ];
    }

    private function getTaskViewData(): array
    {
        $tasks = Task::whereDoesntHave('status', fn ($q) => $q->whereIn('name', ['Note', 'note']))
            ->tap(fn ($q) => $this->applyMonthFilter($q))
            ->tap(fn ($q) => $this->applySpaceFilter($q))
            ->with(['assignees', 'status', 'taskList.space', 'timeTrackings'])
            ->get();

        $today = now()->toDateString();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->filter(fn (Task $t) => $t->status?->type === 'closed')->count();
        $completedLate = $tasks->filter(fn (Task $t) => $t->status?->type === 'closed' && $t->due_date && $t->due_date->toDateString() < $today)->count();
        $completedOnTime = $tasks->filter(fn (Task $t) => $t->status?->type === 'closed' && $t->due_date && $t->due_date->toDateString() >= $today)->count();
        $completedNoDeadline = $tasks->filter(fn (Task $t) => $t->status?->type === 'closed' && $t->due_date === null)->count();
        $latePercent = $completedTasks > 0 ? round(($completedLate / $completedTasks) * 100) : 0;
        $noDeadlinePercent = $completedTasks > 0 ? round(($completedNoDeadline / $completedTasks) * 100) : 0;
        $overdueTasks = $tasks->filter(fn (Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->toDateString() < $today)->count();
        $overduePercent = $totalTasks > 0 ? round(($overdueTasks / $totalTasks) * 100) : 0;
        $progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $priorityRank = ['urgent' => 0, 'high' => 1, 'normal' => 2, 'low' => 3];

        $taskGroups = $tasks->groupBy(fn (Task $task) => $task->task_list_id)
            ->map(function ($groupTasks) use ($priorityRank) {
                $first = $groupTasks->first();
                $total = $groupTasks->count();
                $completed = $groupTasks->filter(fn (Task $t) => $t->status?->type === 'closed')->count();
                $overdue = $groupTasks->filter(fn (Task $t) => $t->status?->type !== 'closed' && $t->due_date && $t->due_date->toDateString() < now()->toDateString())->count();
                $progress = $total > 0 ? round(($completed / $total) * 100) : 0;

                $mappedTasks = $groupTasks->map(function (Task $task) {
                    $totalSeconds = $task->timeTrackings->sum('duration_seconds');

                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'priority' => $task->priority,
                        'status_type' => $task->status?->type ?? 'open',
                        'status_name' => $task->status?->name ?? 'Terbuka',
                        'due_date' => $task->due_date,
                        'is_overdue' => $task->status?->type !== 'closed' && $task->due_date && $task->due_date->toDateString() < now()->toDateString(),
                        'assignees' => $task->assignees,
                        'hours' => round($totalSeconds / 3600, 1),
                    ];
                })->sort(function ($a, $b) use ($priorityRank) {
                    return ($b['is_overdue'] <=> $a['is_overdue'])
                        ?: (($priorityRank[$a['priority']] ?? 99) <=> ($priorityRank[$b['priority']] ?? 99));
                })->values();

                return [
                    'name' => $first->taskList?->name ?? 'Tanpa Proyek',
                    'space' => $first->taskList?->space?->name,
                    'total' => $total,
                    'completed' => $completed,
                    'overdue' => $overdue,
                    'progress' => $progress,
                    'tasks' => $mappedTasks,
                ];
            })
            ->sortByDesc('overdue')
            ->values();

        return [
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'completedOnTime' => $completedOnTime,
            'completedLate' => $completedLate,
            'completedNoDeadline' => $completedNoDeadline,
            'latePercent' => $latePercent,
            'noDeadlinePercent' => $noDeadlinePercent,
            'overdueTasks' => $overdueTasks,
            'overduePercent' => $overduePercent,
            'progressPercent' => $progressPercent,
            'taskGroups' => $taskGroups,
        ];
    }

    private function applySpaceFilter(Builder $query): void
    {
        if ($this->selectedSpaceId === null) {
            return;
        }

        $query->whereHas('taskList', fn (Builder $q) => $q->where('space_id', $this->selectedSpaceId));
    }

    private function applyMonthFilter(Builder $query): void
    {
        if (! preg_match('/^(\d{4})-(\d{2})$/', $this->selectedMonth, $matches)) {
            return;
        }

        $query->where(function (Builder $q) use ($matches): void {
            $q->whereYear('due_date', (int) $matches[1])
                ->whereMonth('due_date', (int) $matches[2])
                ->orWhereNull('due_date');
        });
    }
}
