<?php

namespace App\Livewire\Concerns;

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
