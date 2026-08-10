<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\Task;

/**
 * Status predicates and percentage maths shared by the workload aggregations.
 */
trait SummarizesTaskStatus
{
    private function isClosed(Task $task): bool
    {
        return $task->status?->type === 'closed';
    }

    private function isOverdue(Task $task): bool
    {
        return ! $this->isClosed($task)
            && $task->due_date
            && $task->due_date->toDateString() < now()->toDateString();
    }

    /** Whole-percent share of $part in $total, guarding against division by zero. */
    private function percentOf(int $part, int $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }
}
