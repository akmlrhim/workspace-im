<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskList;
use App\Models\Project\TaskActivity;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Flux\Flux;

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
    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showTaskForm = true;
    }

    #[On('open-edit-task-form')]
    public function openEditForm(int $taskId): void
    {
        $task = Task::with('assignees')->findOrFail($taskId);
        
        if (!$task->canBeManagedBy(auth()->user())) {
            Flux::toast('You do not have permission to edit this task.', variant: 'danger');
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
        ]);

        if ($this->editingTaskId) {
            // Update existing
            $task = Task::findOrFail($this->editingTaskId);
            $task->update([
                'title' => $this->formTaskTitle,
                'task_status_id' => $this->formTaskStatusId,
                'priority' => $this->formTaskPriority,
            ]);
            $task->assignees()->sync($this->formTaskAssignees);
            Flux::toast('Task updated successfully.', variant: 'success');
        } else {
            // Create new
            $maxPosition = $this->taskList->tasks()->max('position') ?? -1;

            $task = $this->taskList->tasks()->create([
                'title' => $this->formTaskTitle,
                'task_status_id' => $this->formTaskStatusId,
                'priority' => $this->formTaskPriority,
                'position' => $maxPosition + 1,
                'created_by' => auth()->id(),
            ]);

            if (!empty($this->formTaskAssignees)) {
                $task->assignees()->sync($this->formTaskAssignees);
            }

            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'created',
                'new_value' => $task->title,
            ]);

            Flux::toast('Task created successfully.', variant: 'success');
        }

        $this->resetForm();
        $this->showTaskForm = false;
        $this->dispatch('task-updated'); // Tell parent views to refresh
    }

    private function resetForm(): void
    {
        $this->editingTaskId = null;
        $this->formTaskTitle = '';
        $this->formTaskPriority = 'normal';
        $this->formTaskAssignees = [];
        $statuses = $this->taskList->statuses;
        if ($statuses->isNotEmpty()) {
            $this->formTaskStatusId = $statuses->first()->id;
        }
    }

    public function render()
    {
        return view('livewire.project.task-form-modal', [
            'statuses' => $this->taskList->statuses()->orderBy('position')->get(),
            'workspaceUsers' => User::all(),
        ]);
    }
}
