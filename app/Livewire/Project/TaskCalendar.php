<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskList;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;

#[Layout('layouts.app')]
class TaskCalendar extends Component
{
    public Space $space;
    public TaskList $taskList;

    public int $currentYear;
    public int $currentMonth;

    public ?int $selectedTaskId = null;
    public bool $showTaskDetail = false;

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
    }

    public function getTitle(): string
    {
        return $this->taskList->name . ' — Calendar';
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentYear = $date->year;
        $this->currentMonth = $date->month;
    }

    public function goToToday(): void
    {
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
    }

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    public function closeTaskDetail(): void
    {
        $this->showTaskDetail = false;
        $this->selectedTaskId = null;
    }

    #[\Livewire\Attributes\On('task-updated')]
    public function onTaskUpdated(): void
    {
        // Re-render automatically
    }

    public function render()
    {
        $monthStart = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();

        // Get tasks with due dates in this month
        $tasks = $this->taskList->tasks()
            ->with(['status', 'assignee'])
            ->whereNull('parent_id')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$monthStart, $monthEnd])
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));

        // Build calendar grid
        $calendarStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];
        $currentDay = $calendarStart->copy();

        while ($currentDay <= $calendarEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $dayStr = $currentDay->format('Y-m-d');
                $week[] = [
                    'date' => $currentDay->copy(),
                    'isCurrentMonth' => $currentDay->month === $this->currentMonth,
                    'isToday' => $currentDay->isToday(),
                    'tasks' => $tasks->get($dayStr, collect()),
                ];
                $currentDay->addDay();
            }
            $weeks[] = $week;
        }

        return view('livewire.project.task-calendar', [
            'weeks' => $weeks,
            'monthLabel' => $monthStart->format('F Y'),
        ]);
    }
}
