<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskActivity;
use App\Models\Project\TaskList;
use App\Models\Project\TaskStatus;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.app')]
class TaskBoard extends Component
{
    public Space $space;

    public TaskList $taskList;

    // Create task
    public string $newTaskTitle = '';

    public ?int $createInStatusId = null;

    // Create column
    public bool $showNewColumnInput = false;

    public string $newColumnName = '';

    public string $newColumnColor = '#6b7280';

    // Rename column
    public ?int $renamingColumnId = null;

    public string $renamingColumnName = '';

    // Task detail
    public ?int $selectedTaskId = null;

    public bool $showTaskDetail = false;

    // Delete column confirm
    public bool $showDeleteColumnConfirm = false;

    public ?int $deletingColumnId = null;

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;
    }

    // ─── Column CRUD ───────────────────────────────────────────────

    public function addColumn(): void
    {
        if (empty(trim($this->newColumnName))) {
            return;
        }

        $maxPosition = TaskStatus::where('task_list_id', $this->taskList->id)
            ->max('position') ?? -1;

        TaskStatus::create([
            'task_list_id' => $this->taskList->id,
            'name' => trim($this->newColumnName),
            'color' => $this->newColumnColor,
            'position' => $maxPosition + 1,
            'type' => 'active',
        ]);

        $this->reset(['newColumnName', 'newColumnColor', 'showNewColumnInput']);
        $this->newColumnColor = '#6b7280';

        Flux::toast(__('messages.column_created'), variant: 'success');
    }

    public function startRenamingColumn(int $columnId): void
    {
        $column = TaskStatus::findOrFail($columnId);
        $this->renamingColumnId = $columnId;
        $this->renamingColumnName = $column->name;
    }

    public function saveColumnRename(): void
    {
        if (empty(trim($this->renamingColumnName)) || ! $this->renamingColumnId) {
            return;
        }

        TaskStatus::where('id', $this->renamingColumnId)->update([
            'name' => trim($this->renamingColumnName),
        ]);

        $this->reset(['renamingColumnId', 'renamingColumnName']);
        Flux::toast(__('messages.column_updated'), variant: 'success');
    }

    public function cancelColumnRename(): void
    {
        $this->reset(['renamingColumnId', 'renamingColumnName']);
    }

    public function confirmDeleteColumn(int $columnId): void
    {
        $this->deletingColumnId = $columnId;
        $this->showDeleteColumnConfirm = true;
    }

    public function deleteColumn(): void
    {
        if (! $this->deletingColumnId) {
            return;
        }

        $columnId = $this->deletingColumnId;
        $column = TaskStatus::where('id', $columnId)
            ->where('task_list_id', $this->taskList->id)
            ->firstOrFail();

        // Prevent deleting the last column
        $remainingCount = TaskStatus::where('task_list_id', $this->taskList->id)->count();
        if ($remainingCount <= 1) {
            $this->reset(['showDeleteColumnConfirm', 'deletingColumnId']);
            Flux::toast(__('messages.column_delete_error'), variant: 'danger');

            return;
        }

        // Move tasks in this column to the first remaining column
        $firstOther = TaskStatus::where('task_list_id', $this->taskList->id)
            ->where('id', '!=', $columnId)
            ->orderBy('position')
            ->first();

        if ($firstOther) {
            Task::where('task_status_id', $columnId)
                ->update(['task_status_id' => $firstOther->id]);
        }

        $column->delete();

        $this->reset(['showDeleteColumnConfirm', 'deletingColumnId']);
        Flux::toast(__('messages.column_deleted'), variant: 'success');
    }

    public function updateColumnOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $columnId) {
            TaskStatus::where('id', $columnId)
                ->where('task_list_id', $this->taskList->id)
                ->update(['position' => $index]);
        }
    }

    // ─── Task CRUD ─────────────────────────────────────────────────

    public function createTaskInStatus(int $statusId): void
    {
        if (empty($this->newTaskTitle)) {
            return;
        }

        $maxPosition = Task::where('task_list_id', $this->taskList->id)
            ->where('task_status_id', $statusId)
            ->max('position') ?? -1;

        $task = Task::create([
            'task_list_id' => $this->taskList->id,
            'task_status_id' => $statusId,
            'title' => $this->newTaskTitle,
            'priority' => 'normal',
            'position' => $maxPosition + 1,
            'created_by' => auth()->id(),
        ]);

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'created',
            'new_value' => $task->title,
        ]);

        $this->reset('newTaskTitle');
        $this->createInStatusId = null;

        $this->dispatch('task-created-on-board', taskId: $task->id, statusId: $statusId);

        Flux::toast(__('messages.task_created'), variant: 'success');
    }

    public function moveTask(int $taskId, int $newStatusId, array $orderedIds): void
    {
        $task = Task::findOrFail($taskId);
        $oldStatusName = $task->status->name;
        $oldStatusId = $task->task_status_id;

        // Update the moved task's status
        $task->update([
            'task_status_id' => $newStatusId,
        ]);

        // Re-index all tasks in the target column based on the new order
        foreach ($orderedIds as $index => $id) {
            Task::where('id', $id)->update(['position' => $index]);
        }

        // If the task moved to a different column, also re-index the source column
        if ($oldStatusId !== $newStatusId) {
            $sourceTasks = Task::where('task_list_id', $this->taskList->id)
                ->where('task_status_id', $oldStatusId)
                ->whereNull('parent_id')
                ->orderBy('position')
                ->pluck('id');

            foreach ($sourceTasks as $index => $id) {
                Task::where('id', $id)->update(['position' => $index]);
            }
        }

        $task->refresh();

        if ($oldStatusName !== $task->status->name) {
            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'status_changed',
                'old_value' => $oldStatusName,
                'new_value' => $task->status->name,
            ]);

            Flux::toast(__('messages.task_moved_to', ['status' => $task->status->name]), variant: 'success');
        }
    }

    // ─── Task Detail ───────────────────────────────────────────────

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    #[On('task-updated')]
    public function onTaskUpdated(): void
    {
        // Force full re-render to reflect changes
    }

    #[On('close-task-detail')]
    public function closeTaskDetail(): void
    {
        $this->showTaskDetail = false;
        $this->selectedTaskId = null;
    }

    public function render()
    {
        $statuses = $this->taskList->statuses()
            ->with(['tasks' => function ($q) {
                $q->whereNull('parent_id')
                    ->with(['assignees', 'assignee', 'labels', 'subtasks'])
                    ->withCount(['comments', 'attachments'])
                    ->orderBy('position');
            }])
            ->orderBy('position')
            ->get();

        return view('livewire.project.task-board', [
            'statuses' => $statuses,
        ]);
    }
}
