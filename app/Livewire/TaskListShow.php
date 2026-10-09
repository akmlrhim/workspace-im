<?php

namespace App\Livewire;

use App\Models\Space;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskList;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class TaskListShow extends Component
{
    public Space $space;

    public TaskList $taskList;

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
        $task = $this->findAuthorizedTask($taskId);

        if (! $task) {
            return;
        }

        abort_unless($this->statuses->contains('id', $statusId), 403);

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
        $task = $this->findAuthorizedTask($taskId);

        if (! $task) {
            return;
        }

        abort_unless(in_array($priority, ['urgent', 'high', 'normal', 'low'], true), 422);

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

    private function findAuthorizedTask(int $taskId): ?Task
    {
        $task = Task::with('assignees')
            ->where('task_list_id', $this->taskList->id)
            ->findOrFail($taskId);

        $this->taskList->loadMissing('space.workspace');
        $task->setRelation('taskList', $this->taskList);

        if ($task->canBeManagedBy(auth()->user())) {
            return $task;
        }

        Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

        return null;
    }

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    #[On('task-updated')]
    public function refreshList(): void
    {
        unset($this->tasks);
        unset($this->statuses);
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
    public function tasks(): Collection
    {
        $query = $this->taskList->tasks()
            ->select([
                'id',
                'task_list_id',
                'task_status_id',
                'parent_id',
                'title',
                'description',
                'priority',
                'due_date',
                'position',
                'created_by',
                'assigned_to',
            ])
            ->with([
                'status:id,name,color,type',
                'assignee:id,name,avatar',
                'assignees:id,name,avatar',
                'labels:id,name,color',
                'subtasks:id,parent_id,title,is_completed,position',
            ])
            ->whereNull('parent_id')
            ->whereHas('status', fn ($q) => $q->whereRaw('LOWER(name) != ?', ['note']));

        if ($this->filterPriority) {
            $query->where('priority', $this->filterPriority);
        }

        if ($this->filterStatus) {
            $query->where('task_status_id', $this->filterStatus);
        }

        if ($this->searchQuery) {
            $query->whereRaw(
                "title like ? escape '\\'",
                ['%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $this->searchQuery).'%']
            );
        }

        return $query->orderBy('position')->limit(500)->get();
    }

    #[Computed]
    public function statuses(): Collection
    {
        return $this->taskList->statuses()
            ->whereRaw('LOWER(name) != ?', ['note'])
            ->orderBy('position')
            ->get();
    }

    public function render()
    {
        return view('livewire.task-list-show');
    }
}
