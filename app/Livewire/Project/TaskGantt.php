<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\TaskList;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

#[Layout('layouts.app')]
class TaskGantt extends Component
{
    public Space $space;
    public TaskList $taskList;

    public ?int $selectedTaskId = null;
    public bool $showTaskDetail = false;

    // Timeline range
    public string $startDate;
    public string $endDate;

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;

        // Default: show 4 weeks from today
        $this->startDate = now()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->startOfWeek()->addWeeks(4)->format('Y-m-d');
    }

    public function getTitle(): string
    {
        return $this->taskList->name . ' — Gantt';
    }

    public function previousPeriod(): void
    {
        $this->startDate = Carbon::parse($this->startDate)->subWeeks(2)->format('Y-m-d');
        $this->endDate = Carbon::parse($this->endDate)->subWeeks(2)->format('Y-m-d');
    }

    public function nextPeriod(): void
    {
        $this->startDate = Carbon::parse($this->startDate)->addWeeks(2)->format('Y-m-d');
        $this->endDate = Carbon::parse($this->endDate)->addWeeks(2)->format('Y-m-d');
    }

    public function resetToToday(): void
    {
        $this->startDate = now()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->startOfWeek()->addWeeks(4)->format('Y-m-d');
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

    #[On('task-updated')]
    public function onTaskUpdated(): void
    {
        // Re-render
    }

    public function render()
    {
        $tasks = $this->taskList->tasks()
            ->with(['status', 'assignee'])
            ->whereNull('parent_id')
            ->whereNotNull('due_date')
            ->orderBy('due_date')
            ->get();

        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $days = collect(CarbonPeriod::create($start, $end));
        $totalDays = $start->diffInDays($end) + 1;

        return view('livewire.project.task-gantt', [
            'tasks' => $tasks,
            'days' => $days,
            'timelineStart' => $start,
            'timelineEnd' => $end,
            'totalDays' => $totalDays,
        ]);
    }
}
