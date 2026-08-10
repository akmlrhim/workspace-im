<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\Task;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * Month grid and navigation for the general taskboard's calendar tab.
 */
trait BuildsTaskboardCalendar
{
    public int $calYear;

    public int $calMonth;

    /** Null = semua space ditampilkan */
    public ?int $calSelectedSpaceId = null;

    public function calPrevMonth(): void
    {
        $this->setCalendarMonth(Carbon::create($this->calYear, $this->calMonth, 1)->subMonth());
    }

    public function calNextMonth(): void
    {
        $this->setCalendarMonth(Carbon::create($this->calYear, $this->calMonth, 1)->addMonth());
    }

    public function calToday(): void
    {
        $this->setCalendarMonth(now());
    }

    /**
     * Calendar grid + tasks — used by the "calendar" tab only.
     *
     * @return array{weeks: array<int, array<int, array<string, mixed>>>, label: string}
     */
    #[Computed]
    public function calendarData(): array
    {
        $monthStart = Carbon::create($this->calYear, $this->calMonth, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();

        $calTasks = $this->tasksDueBetween($monthStart, $monthEnd);

        return [
            'weeks' => $this->buildWeeks($monthStart, $monthEnd, $calTasks),
            'label' => $monthStart->isoFormat('MMMM Y'),
        ];
    }

    private function setCalendarMonth(Carbon $date): void
    {
        $this->calYear = $date->year;
        $this->calMonth = $date->month;
    }

    /**
     * Root tasks with a deadline in the range, grouped by Y-m-d.
     *
     * @return Collection<string, Collection<int, Task>>
     */
    private function tasksDueBetween(Carbon $from, Carbon $to)
    {
        $selectedId = $this->calSelectedSpaceId;

        return Task::with(['status', 'taskList.space', 'assignees'])
            ->whereNull('parent_id')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$from, $to])
            ->whereHas('taskList.space', function ($q) use ($selectedId) {
                $q->accessibleBy(auth()->id());
                if ($selectedId !== null) {
                    $q->where('id', $selectedId);
                }
            })
            ->orderBy('due_date')
            ->limit(500)
            ->get()
            ->groupBy(fn (Task $task) => $task->due_date->format('Y-m-d'));
    }

    /**
     * Monday-first weeks spanning the whole month, padded into complete rows.
     *
     * @param  Collection<string, Collection<int, Task>>  $tasksByDate
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function buildWeeks(Carbon $monthStart, Carbon $monthEnd, $tasksByDate): array
    {
        $cur = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];

        while ($cur <= $calEnd) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $week[] = [
                    'date' => $cur->copy(),
                    'isCurrentMonth' => $cur->month === $this->calMonth,
                    'isToday' => $cur->isToday(),
                    'tasks' => $tasksByDate->get($cur->format('Y-m-d'), collect()),
                ];
                $cur->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }
}
