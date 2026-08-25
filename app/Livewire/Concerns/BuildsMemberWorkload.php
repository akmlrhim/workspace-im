<?php

namespace App\Livewire\Concerns;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

trait BuildsMemberWorkload
{
    /**
     * @return array<string, mixed>
     */
    private function getMemberViewData(): array
    {
        $tasks = $this->baseTaskQuery()
            ->with(['assignees', 'status', 'taskList.space', 'timeTrackings'])
            ->get();

        $allAssignees = $tasks->flatMap->assignees->unique('id');
        $memberStats = $this->buildMemberStats($tasks, $allAssignees);

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
            'completedTasks' => $tasks->filter(fn (Task $t) => $this->isClosed($t))->count(),
            'overdueTasks' => $tasks->filter(fn (Task $t) => $this->isOverdue($t))->count(),
        ];
    }

    /**
     * @param  Collection<int, Task>  $tasks
     * @param  Collection<int, User>  $assignees
     * @return Collection<int, array<string, mixed>>
     */
    private function buildMemberStats(Collection $tasks, Collection $assignees): Collection
    {
        return $assignees
            ->map(function ($user) use ($tasks) {
                $userTasks = $tasks->filter(fn (Task $t) => $t->assignees->contains('id', $user->id));
                $total = $userTasks->count();
                $completed = $userTasks->filter(fn (Task $t) => $this->isClosed($t))->count();

                $totalSeconds = $userTasks->flatMap->timeTrackings
                    ->where('user_id', $user->id)
                    ->sum('duration_seconds');

                $statusBreakdown = $userTasks->groupBy(fn (Task $t) => $t->status?->type ?? 'open');

                return [
                    'user' => $user,
                    'total' => $total,
                    'completed' => $completed,
                    'overdue' => $userTasks->filter(fn (Task $t) => $this->isOverdue($t))->count(),
                    'progress' => $this->percentOf($completed, $total),
                    'hours' => round($totalSeconds / 3600, 1),
                    'open' => $statusBreakdown->get('open', collect())->count(),
                    'active' => $statusBreakdown->get('active', collect())->count(),
                    'closed' => $statusBreakdown->get('closed', collect())->count(),
                ];
            })
            ->sort(function ($a, $b) {
                if ($b['completed'] !== $a['completed']) {
                    return $b['completed'] <=> $a['completed'];
                }

                return $b['progress'] <=> $a['progress'];
            })
            ->values();
    }
}
