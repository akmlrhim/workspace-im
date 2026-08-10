<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\Task;
use Illuminate\Support\Collection;

/**
 * Completion/overdue totals and per-list grouping for the dashboard's "task" view.
 */
trait BuildsTaskWorkload
{
    private const PRIORITY_RANK = ['urgent' => 0, 'high' => 1, 'normal' => 2, 'low' => 3];

    /** @return array<string, mixed> */
    private function getTaskViewData(): array
    {
        $tasks = $this->scopedTaskQuery()
            ->with(['assignees', 'status', 'taskList.space', 'timeTrackings'])
            ->get();

        return array_merge(
            $this->summariseCompletion($tasks),
            ['taskGroups' => $this->groupTasksByList($tasks)],
        );
    }

    /**
     * Headline counters: how much is done, how much was late, how much is overdue.
     *
     * @param  Collection<int, Task>  $tasks
     * @return array<string, int>
     */
    private function summariseCompletion(Collection $tasks): array
    {
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->filter(fn (Task $t) => $this->isClosed($t))->count();

        // Late means finished after the deadline — compare completion time to the
        // deadline, not the deadline to today (a task finished on time must not
        // flip to "late" once its due date passes).
        $completedLate = $tasks->filter(fn (Task $t) => $this->isClosed($t)
            && $t->due_date
            && $t->completed_at
            && $t->completed_at->toDateString() > $t->due_date->toDateString())->count();

        $completedNoDeadline = $tasks->filter(fn (Task $t) => $this->isClosed($t) && $t->due_date === null)->count();
        $overdueTasks = $tasks->filter(fn (Task $t) => $this->isOverdue($t))->count();

        return [
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'completedOnTime' => $completedTasks - $completedLate - $completedNoDeadline,
            'completedLate' => $completedLate,
            'completedNoDeadline' => $completedNoDeadline,
            'latePercent' => $this->percentOf($completedLate, $completedTasks),
            'noDeadlinePercent' => $this->percentOf($completedNoDeadline, $completedTasks),
            'overdueTasks' => $overdueTasks,
            'overduePercent' => $this->percentOf($overdueTasks, $totalTasks),
            'progressPercent' => $this->percentOf($completedTasks, $totalTasks),
        ];
    }

    /**
     * One row per task list, most-overdue first.
     *
     * @param  Collection<int, Task>  $tasks
     * @return Collection<int, array<string, mixed>>
     */
    private function groupTasksByList(Collection $tasks): Collection
    {
        return $tasks->groupBy(fn (Task $task) => $task->task_list_id)
            ->map(function (Collection $groupTasks) {
                $first = $groupTasks->first();
                $total = $groupTasks->count();
                $completed = $groupTasks->filter(fn (Task $t) => $this->isClosed($t))->count();

                return [
                    'id' => $first->task_list_id,
                    'name' => $first->taskList?->name ?? 'Tanpa Proyek',
                    'space' => $first->taskList?->space?->name,
                    'total' => $total,
                    'completed' => $completed,
                    'overdue' => $groupTasks->filter(fn (Task $t) => $this->isOverdue($t))->count(),
                    'progress' => $this->percentOf($completed, $total),
                    'tasks' => $this->mapTaskRows($groupTasks),
                ];
            })
            ->sortByDesc('overdue')
            ->values();
    }

    /**
     * Flatten tasks to view rows, overdue first then by priority.
     *
     * @param  Collection<int, Task>  $tasks
     * @return Collection<int, array<string, mixed>>
     */
    private function mapTaskRows(Collection $tasks): Collection
    {
        return $tasks->map(fn (Task $task) => [
            'id' => $task->id,
            'title' => $task->title,
            'priority' => $task->priority,
            'status_type' => $task->status?->type ?? 'open',
            'status_name' => $task->status?->name ?? 'Terbuka',
            'due_date' => $task->due_date,
            'is_overdue' => $this->isOverdue($task),
            'assignees' => $task->assignees,
            'hours' => round($task->timeTrackings->sum('duration_seconds') / 3600, 1),
        ])->sort(function ($a, $b) {
            return ($b['is_overdue'] <=> $a['is_overdue'])
                ?: ((self::PRIORITY_RANK[$a['priority']] ?? 99) <=> (self::PRIORITY_RANK[$b['priority']] ?? 99));
        })->values();
    }
}
