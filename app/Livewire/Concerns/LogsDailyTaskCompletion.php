<?php

namespace App\Livewire\Concerns;

use App\Models\DailyTaskLog;
use Flux\Flux;

trait LogsDailyTaskCompletion
{
    public array $reasonInputs = [];

    public ?int $reasonModalFor = null;

    public function toggleComplete(int $dailyTaskId): ?bool
    {
        if (! $this->guardManage()) {
            return null;
        }

        $this->taskList->dailyTasks()->findOrFail($dailyTaskId);

        $log = $this->logFor($dailyTaskId);

        if ($log->is_completed) {
            $log->is_completed = false;
            $log->completed_at = null;
        } else {
            $log->is_completed = true;
            $log->completed_at = now();
        }

        $log->reason = null;
        $log->save();

        $this->clearTaskCache();
        $this->broadcastChange();

        $this->skipRender();

        return $log->is_completed;
    }

    public function openReasonModal(int $dailyTaskId): void
    {
        if (! $this->canManage()) {
            return;
        }

        $this->taskList->dailyTasks()->findOrFail($dailyTaskId);

        $this->reasonModalFor = $dailyTaskId;
        $this->reasonInputs[$dailyTaskId] = '';
    }

    public function submitReason(): void
    {
        $this->validate([
            "reasonInputs.{$this->reasonModalFor}" => 'required|string|max:500',
        ]);

        $dailyTaskId = $this->reasonModalFor;

        $log = $this->logFor($dailyTaskId);
        $log->is_completed = false;
        $log->reason = $this->reasonInputs[$dailyTaskId];
        $log->completed_at = null;
        $log->save();

        $this->reasonModalFor = null;
        $this->reasonInputs = [];

        $this->clearTaskCache();
        $this->broadcastChange();
        Flux::toast('Alasan disimpan.', variant: 'success');
    }

    private function logFor(int $dailyTaskId): DailyTaskLog
    {
        return DailyTaskLog::firstOrNew([
            'daily_task_id' => $dailyTaskId,
            'user_id' => auth()->id(),
            'date' => $this->selectedDate,
        ]);
    }
}
