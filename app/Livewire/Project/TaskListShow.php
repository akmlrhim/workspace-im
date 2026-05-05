<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskActivity;
use App\Models\Project\TaskList;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class TaskListShow extends Component
{
    public Space $space;

    public TaskList $taskList;

    // Filters
    public string $filterPriority = '';

    public string $filterStatus = '';

    public string $searchQuery = '';

    public ?int $selectedTaskId = null;

    public bool $showTaskDetail = false;

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;
    }

    public function getTitle(): string
    {
        return $this->taskList->name.' — '.$this->space->name;
    }

    public function updateTaskStatus(int $taskId, int $statusId): void
    {
        $task = Task::findOrFail($taskId);
        $oldStatus = $task->status->name;
        $task->update(['task_status_id' => $statusId]);
        $task->refresh();

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $task->status->name,
        ]);

        Flux::toast('Status diperbarui.', variant: 'success');
    }

    public function updateTaskPriority(int $taskId, string $priority): void
    {
        $task = Task::findOrFail($taskId);
        $old = $task->priority;
        $task->update(['priority' => $priority]);

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'priority_changed',
            'old_value' => $old,
            'new_value' => $priority,
        ]);

        Flux::toast('Prioritas diperbarui.', variant: 'success');
    }

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    #[On('task-updated')]
    public function refreshList(): void
    {
        // Re-render
    }

    #[On('task-deleted')]
    public function onTaskDeleted(int $taskId): void
    {
        if ($this->selectedTaskId === $taskId) {
            $this->selectedTaskId = null;
        }
        $this->showTaskDetail = false;
    }

    #[On('close-task-detail')]
    public function closeTaskDetail(): void
    {
        $this->showTaskDetail = false;
    }

    #[Computed]
    public function tasks()
    {
        $query = $this->taskList->tasks()
            ->with(['status', 'assignee', 'assignees', 'labels', 'subtasks'])
            ->whereNull('parent_id');

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterStatus) {
            $query->where('task_status_id', $this->filterStatus);
        }

        if ($this->searchQuery) {
            $query->where('title', 'like', '%'.$this->searchQuery.'%');
        }

        return $query->orderBy('position')->get();
    }

    #[Computed]
    public function statuses()
    {
        return $this->taskList->statuses()->orderBy('position')->get();
    }

    public function render()
    {
        return view('livewire.project.task-list-show');
    }
}
