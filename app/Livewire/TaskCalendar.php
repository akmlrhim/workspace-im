<?php

namespace App\Livewire;

use App\Events\TaskListUpdated;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskList;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TaskCalendar extends Component
{
    public Space $space;

    public TaskList $taskList;

    public int $currentYear;

    public int $currentMonth;

    public ?int $selectedTaskId = null;

    public bool $showTaskDetail = false;

    public bool $showCreateTask = false;

    public string $newTaskTitle = '';

    public string $createTaskDate = '';

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;
        $this->currentYear = now()->year;
        $this->currentMonth = now()->month;
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        return [
            "echo:task-list.{$this->taskList->id},TaskListUpdated" => 'onBroadcastUpdate',
            "echo:task-list.{$this->taskList->id},TaskUpdated" => 'onBroadcastUpdate',
            'close-task-detail' => 'closeTaskDetail',
            'task-updated' => 'onTaskUpdated',
            'task-deleted' => 'onTaskDeleted',
        ];
    }

    public function onBroadcastUpdate(array $event): void
    {
        if (($event['triggeredBy'] ?? null) == auth()->id()) {
            $this->skipRender();

            return;
        }
    }

    public function getTitle(): string
    {
        return $this->taskList->name.' — Calendar';
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

    public function onTaskDeleted(int $taskId): void
    {
        if ($this->selectedTaskId === $taskId) {
            $this->selectedTaskId = null;
        }
        $this->showTaskDetail = false;
    }

    public function closeTaskDetail(): void
    {
        $this->showTaskDetail = false;
    }

    public function onTaskUpdated(): void
    {
    }

    public function openCreateTask(string $date): void
    {
        $this->createTaskDate = $date;
        $this->newTaskTitle = '';
        $this->showCreateTask = true;
    }

    public function storeTask(): void
    {
        $this->validate(
            ['newTaskTitle' => 'required|min:2|max:500'],
            ['newTaskTitle.required' => 'Nama tugas wajib diisi.', 'newTaskTitle.min' => 'Minimal 2 karakter.']
        );

        $status = $this->taskList->statuses()->orderBy('position')->first();

        if (! $status) {
            Flux::toast('List belum memiliki status. Buka board untuk mengaturnya.', variant: 'danger');

            return;
        }

        $maxPosition = Task::where('task_list_id', $this->taskList->id)
            ->where('task_status_id', $status->id)
            ->max('position') ?? -1;

        $task = Task::create([
            'task_list_id' => $this->taskList->id,
            'task_status_id' => $status->id,
            'title' => trim($this->newTaskTitle),
            'priority' => 'normal',
            'position' => $maxPosition + 1,
            'due_date' => $this->createTaskDate,
            'created_by' => auth()->id(),
        ]);

        $this->taskList->members()->syncWithoutDetaching([auth()->id()]);
        $task->assignees()->sync([auth()->id()]);

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'created',
            'new_value' => $task->title,
        ]);

        $this->reset(['newTaskTitle', 'createTaskDate', 'showCreateTask']);

        TaskListUpdated::dispatch($this->taskList->id, auth()->id(), $this->taskList->space->workspace_id);

        Flux::toast('Tugas berhasil ditambahkan.', variant: 'success');
    }

    public function render()
    {
        $monthStart = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();

        $tasks = $this->taskList->tasks()
            ->with(['status', 'assignee'])
            ->whereNull('parent_id')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$monthStart, $monthEnd])
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));

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

        return view('livewire.task-calendar', [
            'weeks' => $weeks,
            'monthLabel' => $monthStart->format('F Y'),
        ]);
    }
}
