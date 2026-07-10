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

    public ?int $selectedMemberId = null;

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
        return Space::accessibleBy(auth()->id())->orderBy('position')->get();
    }

    /**
     * Distinct assignees who have tasks in the current month/space scope.
     * Computed without the member filter so the dropdown always lists everyone.
     */
    #[Computed]
    public function members()
    {
        return Task::whereDoesntHave('status', fn ($q) => $q->whereIn('name', ['Note', 'note']))
            ->tap(fn ($q) => $this->applyMonthFilter($q))
            ->tap(fn ($q) => $this->applySpaceFilter($q))
            ->with('assignees:id,name')
            ->get()
            ->flatMap
            ->assignees
            ->unique('id')
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    public function switchView(string $view): void
    {
        $this->view = $view;
    }

    public function selectSpace(?int $spaceId): void
    {
        $this->selectedSpaceId = $spaceId;

        // The member list is scoped to the space, so a previously chosen member
        // may no longer be present — clear the filter to avoid an empty result.
        $this->selectedMemberId = null;
    }

    public function updatedSelectedMonth(): void
    {
        // The native month input can emit transient/empty values mid-edit;
        // snap back to the current month instead of rendering unfiltered.
        if (! preg_match('/^\d{4}-\d{2}$/', $this->selectedMonth)) {
            $this->selectedMonth = now()->format('Y-m');
        }

        // Drop a selected member that has no tasks in the new month's scope,
        // otherwise the filter stays active while its banner disappears.
        unset($this->members);

        if ($this->selectedMemberId !== null && ! $this->members->contains('id', $this->selectedMemberId)) {
            $this->selectedMemberId = null;
        }
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
            ->tap(fn ($q) => $this->applyMemberFilter($q))
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
            ->tap(fn ($q) => $this->applyMemberFilter($q))
            ->with(['assignees', 'status', 'taskList.space', 'timeTrackings'])
            ->get();

        $today = now()->toDateString();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->filter(fn (Task $t) => $t->status?->type === 'closed')->count();
        // Late means finished after the deadline — compare completion time to the
        // deadline, not the deadline to today (a task finished on time must not
        // flip to "late" once its due date passes).
        $completedLate = $tasks->filter(fn (Task $t) => $t->status?->type === 'closed'
            && $t->due_date
            && $t->completed_at
            && $t->completed_at->toDateString() > $t->due_date->toDateString())->count();
        $completedNoDeadline = $tasks->filter(fn (Task $t) => $t->status?->type === 'closed' && $t->due_date === null)->count();
        $completedOnTime = $completedTasks - $completedLate - $completedNoDeadline;
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
                    'id' => $first->task_list_id,
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

    private function applyMemberFilter(Builder $query): void
    {
        if ($this->selectedMemberId === null) {
            return;
        }

        $query->whereHas('assignees', fn (Builder $q) => $q->where('users.id', $this->selectedMemberId));
    }

    private function applyMonthFilter(Builder $query): void
    {
        // Never skip the month scope: an invalid/transient value (e.g. while the
        // native month input is mid-edit) falls back to the current month.
        // Skipping would briefly render every task ever, ballooning the page
        // height and yanking the user's scroll position to the top.
        if (preg_match('/^(\d{4})-(\d{2})$/', $this->selectedMonth, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
        } else {
            $year = now()->year;
            $month = now()->month;
        }

        $isCurrentMonth = $year === now()->year && $month === now()->month;

        // A task without a deadline has no natural month. We place it by activity:
        //   - completed no-deadline tasks land in the month they were completed;
        //   - still-open no-deadline tasks are live backlog, shown only for the
        //     current month so they don't pollute past-month reports forever.
        // (completed_at is non-null exactly while a task sits in a closed status.)
        $query->where(function (Builder $q) use ($year, $month, $isCurrentMonth): void {
            $q->where(function (Builder $sub) use ($year, $month): void {
                $sub->whereNotNull('due_date')
                    ->whereYear('due_date', $year)
                    ->whereMonth('due_date', $month);
            });

            $q->orWhere(function (Builder $sub) use ($year, $month): void {
                $sub->whereNull('due_date')
                    ->whereNotNull('completed_at')
                    ->whereYear('completed_at', $year)
                    ->whereMonth('completed_at', $month);
            });

            if ($isCurrentMonth) {
                $q->orWhere(function (Builder $sub): void {
                    $sub->whereNull('due_date')->whereNull('completed_at');
                });
            }
        });
    }
}
