<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use App\Models\Project\TaskStatus;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('My Tasks')]
class MyTasks extends Component
{
    public string $filterPriority = '';

    public ?int $selectedTaskId = null;

    public bool $showTaskDetail = false;

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    #[On('close-task-detail')]
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
        $userId = auth()->id();

        $query = Task::where(function ($q) use ($userId) {
            $q->where('assigned_to', $userId)
                ->orWhereHas('assignees', fn ($sub) => $sub->where('user_id', $userId));
        })
            ->with(['status', 'taskList.space', 'assignees'])
            ->whereNull('parent_id');

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        $tasks = $query->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END, due_date ASC')->get();

        $statuses = TaskStatus::orderBy('position')->get();

        $grouped = $statuses->map(function (TaskStatus $status) use ($tasks) {
            return [
                'status' => $status,
                'tasks' => $tasks->filter(fn ($t) => $t->task_status_id === $status->id)->values(),
            ];
        })->filter(fn ($group) => $group['tasks']->isNotEmpty());

        $ungrouped = $tasks->filter(fn ($t) => $t->task_status_id === null)->values();

        return view('livewire.project.my-tasks', [
            'grouped' => $grouped,
            'ungrouped' => $ungrouped,
            'totalCount' => $tasks->count(),
        ]);
    }
}
