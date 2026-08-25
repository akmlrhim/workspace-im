<?php

namespace App\Livewire;

use App\Models\Space;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskList;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class TaskFormModal extends Component
{
    public Space $space;

    public TaskList $taskList;

    public bool $showTaskForm = false;

    public ?int $editingTaskId = null;

    public string $formTaskTitle = '';

    public string $formTaskPriority = 'normal';

    public ?int $formTaskStatusId = null;

    public array $formTaskAssignees = [];

    #[On('open-create-task-form')]
    public function openCreateForm(?int $statusId = null): void
    {
        $this->resetForm();

        if ($statusId !== null && $this->taskList->statuses()->whereKey($statusId)->exists()) {
            $this->formTaskStatusId = $statusId;
        }

        $this->showTaskForm = true;
    }

    #[On('open-edit-task-form')]
    public function openEditForm(int $taskId): void
    {
        $task = Task::with('assignees')->findOrFail($taskId);

        if (! $task->canBeManagedBy(auth()->user())) {
            Flux::toast('Anda tidak memiliki izin untuk mengedit tugas ini.', variant: 'danger');

            return;
        }

        $this->editingTaskId = $task->id;
        $this->formTaskTitle = $task->title;
        $this->formTaskPriority = $task->priority;
        $this->formTaskStatusId = $task->task_status_id;
        $this->formTaskAssignees = $task->assignees->pluck('id')->toArray();
        $this->showTaskForm = true;
    }

    public function saveTask(): void
    {
        $this->validate([
            'formTaskTitle' => 'required|min:1|max:500',
            'formTaskStatusId' => 'required|exists:task_statuses,id',
            'formTaskPriority' => 'required|in:urgent,high,normal,low',
        ], [
            'formTaskTitle.required' => 'Judul task wajib diisi.',
            'formTaskTitle.min' => 'Judul task minimal 1 karakter.',
            'formTaskTitle.max' => 'Judul task maksimal 500 karakter.',
            'formTaskStatusId.required' => 'Status wajib dipilih.',
            'formTaskStatusId.exists' => 'Status yang dipilih tidak valid.',
            'formTaskPriority.in' => 'Prioritas tidak valid.',
        ]);

        if ($this->editingTaskId) {
            $task = Task::with('assignees')->findOrFail($this->editingTaskId);

            if (! $task->canBeManagedBy(auth()->user())) {
                Flux::toast('Anda tidak memiliki izin untuk mengedit tugas ini.', variant: 'danger');

                return;
            }

            $listMemberIds = $this->taskList->members()->pluck('users.id')->toArray();
            $validAssignees = array_values(array_intersect($this->formTaskAssignees, $listMemberIds));

            $task->update([
                'title' => $this->formTaskTitle,
                'task_status_id' => $this->formTaskStatusId,
                'priority' => $this->formTaskPriority,
            ]);
            $task->assignees()->sync($validAssignees);
            Flux::toast('Tugas berhasil diperbarui.', variant: 'success');
        } else {
            $maxPosition = $this->taskList->tasks()->max('position') ?? -1;

            $task = $this->taskList->tasks()->create([
                'title' => $this->formTaskTitle,
                'task_status_id' => $this->formTaskStatusId,
                'priority' => $this->formTaskPriority,
                'position' => $maxPosition + 1,
                'created_by' => auth()->id(),
            ]);

            $this->taskList->members()->syncWithoutDetaching([auth()->id()]);

            $listMemberIds = $this->taskList->members()->pluck('users.id')->toArray();
            $toSync = array_values(array_unique(array_merge($this->formTaskAssignees, [auth()->id()])));
            $task->assignees()->sync(array_values(array_intersect($toSync, $listMemberIds)));

            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'created',
                'new_value' => $task->title,
            ]);

            Flux::toast('Tugas berhasil dibuat.', variant: 'success');
        }

        $this->resetForm();
        $this->showTaskForm = false;
        $this->dispatch('task-updated');
    }

    private function resetForm(): void
    {
        $this->editingTaskId = null;
        $this->formTaskTitle = '';
        $this->formTaskPriority = 'normal';
        $this->formTaskAssignees = [];
        $statuses = $this->statuses;
        if ($statuses->isNotEmpty()) {
            $this->formTaskStatusId = $statuses->first()->id;
        }
    }

    #[Computed]
    public function statuses()
    {
        return $this->taskList->statuses()->orderBy('position')->get();
    }

    #[Computed]
    public function workspaceUsers()
    {
        return $this->taskList->members()->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.task-form-modal', [
            'statuses' => $this->statuses,
            'workspaceUsers' => $this->workspaceUsers,
        ]);
    }
}
