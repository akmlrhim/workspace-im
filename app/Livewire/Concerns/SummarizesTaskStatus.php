<?php

namespace App\Livewire\Concerns;

use App\Models\Task;

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

    private function percentOf(int $part, int $total): int
    {
        return $total > 0 ? (int) round(($part / $total) * 100) : 0;
    }
}
