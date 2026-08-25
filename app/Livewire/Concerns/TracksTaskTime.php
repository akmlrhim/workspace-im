<?php

namespace App\Livewire\Concerns;

use App\Models\TimeTracking;
use Flux\Flux;

trait TracksTaskTime
{
    public ?int $activeTimerId = null;

    public function startTimer(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $timer = TimeTracking::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'started_at' => now(),
        ]);
        $this->activeTimerId = $timer->id;
        Flux::toast('Penghitung waktu dimulai.', variant: 'success');
    }

    public function stopTimer(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        if ($this->activeTimerId) {
            $timer = TimeTracking::findOrFail($this->activeTimerId);
            $now = now();
            $timer->update([
                'stopped_at' => $now,
                'duration_seconds' => $timer->started_at->diffInSeconds($now),
            ]);
            $this->activeTimerId = null;
            Flux::toast('Penghitung waktu dihentikan.', variant: 'success');
        }
    }
}
