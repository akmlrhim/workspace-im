<?php

namespace App\Livewire\Concerns;

use Carbon\Carbon;
use Livewire\Attributes\Computed;

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

    private function clearDateCache(): void
    {
        $this->clearTaskCache();
        unset($this->isToday, $this->selectedCarbon);
    }
}
