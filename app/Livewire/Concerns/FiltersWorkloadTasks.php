<?php

namespace App\Livewire\Concerns;

use App\Models\Task;
use Illuminate\Database\Eloquent\Builder;

trait FiltersWorkloadTasks
{
    private function baseTaskQuery(): Builder
    {
        return Task::whereDoesntHave('status', fn ($q) => $q->whereIn('name', ['Note', 'note']))
            ->tap(fn ($q) => $this->applyMonthFilter($q))
            ->tap(fn ($q) => $this->applySpaceFilter($q));
    }

    private function scopedTaskQuery(): Builder
    {
        return $this->baseTaskQuery()->tap(fn ($q) => $this->applyMemberFilter($q));
    }

    private function applySpaceFilter(Builder $query): void
    {
        if ($this->selectedSpaceId === null) {
            return;
        }

        $query->whereHas('taskList', fn (Builder $q) => $q->where('space_id', $this->selectedSpaceId));
    }

    private function applyMemberFilter(Builder $query): void
    {
        if ($this->selectedMemberId === null) {
            return;
        }

        $query->whereHas('assignees', fn (Builder $q) => $q->where('users.id', $this->selectedMemberId));
    }

    private function applyMonthFilter(Builder $query): void
    {
        if (preg_match('/^(\d{4})-(\d{2})$/', $this->selectedMonth, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
        } else {
            $year = now()->year;
            $month = now()->month;
        }

        $isCurrentMonth = $year === now()->year && $month === now()->month;

        $query->where(function (Builder $q) use ($year, $month, $isCurrentMonth): void {
            $q->where(function (Builder $sub) use ($year, $month): void {
                $sub->whereNotNull('due_date')
                    ->whereYear('due_date', $year)
                    ->whereMonth('due_date', $month);
            });

            $q->orWhere(function (Builder $sub) use ($year, $month): void {
                $sub->whereNull('due_date')
                    ->whereNotNull('completed_at')
                    ->whereYear('completed_at', $year)
                    ->whereMonth('completed_at', $month);
            });

            if ($isCurrentMonth) {
                $q->orWhere(function (Builder $sub): void {
                    $sub->whereNull('due_date')->whereNull('completed_at');
                });
            }
        });
    }
}
