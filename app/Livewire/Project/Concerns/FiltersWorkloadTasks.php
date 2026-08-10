<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\Task;
use Illuminate\Database\Eloquent\Builder;

/**
 * Month/space/member scoping shared by every workload dashboard view.
 */
trait FiltersWorkloadTasks
{
    /**
     * Tasks in the selected month and space, ignoring the member filter.
     * Used to build the member dropdown so it always lists everyone in scope.
     */
    private function baseTaskQuery(): Builder
    {
        return Task::whereDoesntHave('status', fn ($q) => $q->whereIn('name', ['Note', 'note']))
            ->tap(fn ($q) => $this->applyMonthFilter($q))
            ->tap(fn ($q) => $this->applySpaceFilter($q));
    }

    /** Tasks matching every active filter, including the selected member. */
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
        // Never skip the month scope: an invalid/transient value (e.g. while the
        // native month input is mid-edit) falls back to the current month.
        // Skipping would briefly render every task ever, ballooning the page
        // height and yanking the user's scroll position to the top.
        if (preg_match('/^(\d{4})-(\d{2})$/', $this->selectedMonth, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
        } else {
            $year = now()->year;
            $month = now()->month;
        }

        $isCurrentMonth = $year === now()->year && $month === now()->month;

        // A task without a deadline has no natural month. We place it by activity:
        //   - completed no-deadline tasks land in the month they were completed;
        //   - still-open no-deadline tasks are live backlog, shown only for the
        //     current month so they don't pollute past-month reports forever.
        // (completed_at is non-null exactly while a task sits in a closed status.)
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
