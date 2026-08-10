<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\DailyTaskLog;
use Flux\Flux;

/**
 * Per-user completion logging for a daily task on the selected date,
 * including the "why not done" reason flow.
 */
trait LogsDailyTaskCompletion
{
    public array $reasonInputs = [];

    public ?int $reasonModalFor = null;

    /**
     * Membalik status selesai sebuah daily task.
     *
     * Mengembalikan status BARU menurut database (true = selesai, false = belum),
     * atau null bila aksi ditolak. UI selalu menyelaraskan diri ke nilai ini,
     * sehingga tebakan optimistis yang meleset terkoreksi sendiri tanpa refresh.
     */
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

        // Baris sudah diperbarui sendiri oleh browser dari nilai balikan ini, jadi
        // render ulang seluruh list tidak perlu. Selain lebih ringan, ini mencegah
        // hasil render yang sudah usang menimpa baris lain yang requestnya masih
        // berjalan saat user mencentang beberapa task beruntun.
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

    /** The current user's log row for that task on the selected date. */
    private function logFor(int $dailyTaskId): DailyTaskLog
    {
        return DailyTaskLog::firstOrNew([
            'daily_task_id' => $dailyTaskId,
            'user_id' => auth()->id(),
            'date' => $this->selectedDate,
        ]);
    }
}
