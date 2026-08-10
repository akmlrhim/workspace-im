<?php

namespace App\Livewire\Project\Concerns;

/**
 * Slide-over task detail panel state, shared by the board-style components.
 */
trait OpensTaskDetailPanel
{
    public ?int $selectedTaskId = null;

    public bool $showTaskDetail = false;

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    public function onTaskDeleted(int $taskId): void
    {
        if ($this->selectedTaskId === $taskId) {
            $this->selectedTaskId = null;
        }

        $this->showTaskDetail = false;
    }

    public function closeTaskDetail(): void
    {
        $this->showTaskDetail = false;
    }
}
