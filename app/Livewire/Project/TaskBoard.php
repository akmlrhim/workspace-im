<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskActivity;
use App\Models\Project\TaskList;
use App\Models\Project\TaskStatus;
use Flux\Flux;
use Livewire\Attributes\Computed;
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

    private function canManageBoard(): bool
    {
        $user = auth()->user();

        return $user->canManageAllProjects()
            || $this->taskList->tasks()->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))->exists()
            || $this->taskList->tasks()->where('assigned_to', $user->id)->exists();
    }

    // ─── Column CRUD ───────────────────────────────────────────────

    public function addColumn(): void
    {
        if (! $this->canManageBoard()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

            return;
        }

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

        Flux::toast('Kolom baru berhasil ditambahkan.', variant: 'success');
    }

    public function startRenamingColumn(int $columnId): void
    {
        if (! $this->canManageBoard()) {
            return;
        }
        $column = TaskStatus::findOrFail($columnId);
        $this->renamingColumnId = $columnId;
        $this->renamingColumnName = $column->name;
    }

    public function saveColumnRename(): void
    {
        if (! $this->canManageBoard()) {
            return;
        }
        if (empty(trim($this->renamingColumnName)) || ! $this->renamingColumnId) {
            return;
        }

        TaskStatus::where('id', $this->renamingColumnId)->update([
            'name' => trim($this->renamingColumnName),
        ]);

        $this->reset(['renamingColumnId', 'renamingColumnName']);
        Flux::toast('Nama kolom berhasil diubah.', variant: 'success');
    }

    public function cancelColumnRename(): void
    {
        $this->reset(['renamingColumnId', 'renamingColumnName']);
    }

    public function confirmDeleteColumn(int $columnId): void
    {
        if (! $this->canManageBoard()) {
            return;
        }
        $this->deletingColumnId = $columnId;
        $this->showDeleteColumnConfirm = true;
    }

    public function deleteColumn(): void
    {
        if (! $this->canManageBoard() || ! $this->deletingColumnId) {
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
            Flux::toast('Tidak bisa menghapus kolom terakhir.', variant: 'danger');

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
        Flux::toast('Kolom berhasil dihapus.', variant: 'success');
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
        if (! $this->canManageBoard()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

            return;
        }
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

        // Ensure creator is a list member then auto-assign them to the task
        $this->taskList->members()->syncWithoutDetaching([auth()->id()]);
        $task->assignees()->sync([auth()->id()]);

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'created',
            'new_value' => $task->title,
        ]);

        $this->reset('newTaskTitle');
        $this->createInStatusId = null;

        $this->dispatch('task-created-on-board', taskId: $task->id, statusId: $statusId);

        Flux::toast('Tugas berhasil dibuat.', variant: 'success');
    }

    public function moveTask(int $taskId, int $newStatusId, array $orderedIds): void
    {
        $task = Task::with(['assignees', 'status'])->findOrFail($taskId);

        if (! $task->canBeManagedBy(auth()->user())) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

            return;
        }

        $oldStatusId = $task->task_status_id;
        $oldStatusName = $task->status->name;

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

            // Log status change activity
            $newStatus = TaskStatus::find($newStatusId);
            $newStatusName = $newStatus?->name ?? '';

            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'status_changed',
                'old_value' => $oldStatusName,
                'new_value' => $newStatusName,
            ]);

            Flux::toast('Dipindah ke '.$newStatusName, variant: 'success');
        }

        // Skip re-render: SortableJS already moved the card in the DOM.
        // This prevents the visual "bounce-back" delay caused by Livewire morph.
        $this->skipRender();
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

    #[Computed]
    public function statuses()
    {
        return $this->taskList->statuses()
            ->with(['tasks' => function ($q) {
                $q->whereNull('parent_id')
                    ->with(['assignees', 'assignee', 'labels', 'subtasks'])
                    ->withCount(['comments', 'attachments'])
                    ->orderBy('position');
            }])
            ->orderBy('position')
            ->get();
    }

    public function render()
    {
        return view('livewire.project.task-board', [
            'canManage' => $this->canManageBoard(),
        ]);
    }
}
