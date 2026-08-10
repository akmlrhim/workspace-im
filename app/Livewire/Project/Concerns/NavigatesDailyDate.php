<?php

namespace App\Livewire\Project\Concerns;

use Carbon\Carbon;
use Livewire\Attributes\Computed;

/**
 * Day-by-day navigation for the daily task view. The future is never
 * reachable — there is nothing to log for a day that has not happened.
 */
trait NavigatesDailyDate
{
    public string $selectedDate = '';

    #[Computed]
    public function isToday(): bool
    {
        return $this->selectedDate === today()->toDateString();
    }

    #[Computed]
    public function selectedCarbon(): Carbon
    {
        return Carbon::parse($this->selectedDate);
    }

    public function updatedSelectedDate(): void
    {
        // Clamp future dates to today
        if ($this->selectedDate > today()->toDateString()) {
            $this->selectedDate = today()->toDateString();
        }

        $this->clearDateCache();
    }

    public function previousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->toDateString();
        $this->clearDateCache();
    }

    public function nextDay(): void
    {
        if ($this->isToday) {
            return;
        }

        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->toDateString();
        $this->clearDateCache();
    }

    public function goToToday(): void
    {
        $this->selectedDate = today()->toDateString();
        $this->clearDateCache();
    }

    /** Moving the date invalidates the tasks *and* the date-derived computeds. */
    private function clearDateCache(): void
    {
        $this->clearTaskCache();
        unset($this->isToday, $this->selectedCarbon);
    }
}
