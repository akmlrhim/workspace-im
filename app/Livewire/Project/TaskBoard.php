<?php

namespace App\Livewire\Project;

use App\Events\TaskListUpdated;
use App\Events\TaskUpdatedGlobal;
use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskActivity;
use App\Models\Project\TaskList;
use App\Models\Project\TaskStatus;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
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

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo:task-list.{$this->taskList->id},TaskListUpdated" => 'onBroadcastUpdate',
            'task-updated' => 'onTaskUpdated',
            'close-task-detail' => 'closeTaskDetail',
        ];
    }

    public function onBroadcastUpdate(array $event): void
    {
        if (($event['triggeredBy'] ?? null) == auth()->id()) {
            $this->skipRender();

            return;
        }

        unset($this->statuses);
    }

    private function broadcastChange(): void
    {
        TaskListUpdated::dispatch($this->taskList->id, auth()->id());
        TaskUpdatedGlobal::dispatch($this->taskList->space->workspace_id, auth()->id());
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

        $this->broadcastChange();
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
        $this->broadcastChange();
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
        $this->broadcastChange();
        Flux::toast('Kolom berhasil dihapus.', variant: 'success');
    }

    public function updateColumnOrder(array $orderedIds): void
    {
        if (empty($orderedIds)) {
            return;
        }

        $cases = [];
        $bindings = [];

        foreach ($orderedIds as $position => $columnId) {
            $cases[] = 'WHEN id = ? THEN ?';
            $bindings[] = $columnId;
            $bindings[] = $position;
        }

        $bindings[] = $this->taskList->id;
        $bindings = array_merge($bindings, $orderedIds);
        $placeholders = implode(',', array_fill(0, count($orderedIds), '?'));

        DB::update(
            'UPDATE task_statuses SET position = CASE '.implode(' ', $cases).' END WHERE task_list_id = ? AND id IN ('.$placeholders.')',
            $bindings
        );

        $this->broadcastChange();
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

        $this->broadcastChange();
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

        // Batch update: moved task status + all target column positions in one transaction
        DB::transaction(function () use ($task, $newStatusId, $oldStatusId, $orderedIds) {
            $task->update(['task_status_id' => $newStatusId]);

            // Batch position update for target column
            $this->batchUpdatePositions($orderedIds);

            // Re-index source column if cross-column move
            if ($oldStatusId !== $newStatusId) {
                $sourceIds = Task::where('task_list_id', $this->taskList->id)
                    ->where('task_status_id', $oldStatusId)
                    ->whereNull('parent_id')
                    ->orderBy('position')
                    ->pluck('id')
                    ->all();

                $this->batchUpdatePositions($sourceIds);
            }
        });

        if ($oldStatusId !== $newStatusId) {
            $newStatusName = TaskStatus::where('id', $newStatusId)->value('name') ?? '';

            TaskActivity::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'type' => 'status_changed',
                'old_value' => $oldStatusName,
                'new_value' => $newStatusName,
            ]);

            Flux::toast('Dipindah ke '.$newStatusName, variant: 'success');
        }

        $this->broadcastChange();
        $this->skipRender();
    }

    /**
     * Batch update positions using a single CASE query instead of N individual updates.
     *
     * @param  array<int, int>  $orderedIds
     */
    private function batchUpdatePositions(array $orderedIds): void
    {
        if (empty($orderedIds)) {
            return;
        }

        $cases = [];
        $bindings = [];

        foreach ($orderedIds as $position => $id) {
            $cases[] = 'WHEN id = ? THEN ?';
            $bindings[] = $id;
            $bindings[] = $position;
        }

        $bindings = array_merge($bindings, $orderedIds);
        $placeholders = implode(',', array_fill(0, count($orderedIds), '?'));

        DB::update(
            'UPDATE tasks SET position = CASE '.implode(' ', $cases).' END WHERE id IN ('.$placeholders.')',
            $bindings
        );
    }

    // ─── Task Detail ───────────────────────────────────────────────

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    public function onTaskUpdated(): void
    {
        unset($this->statuses);
        $this->broadcastChange();
    }

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
                    ->with(['assignees', 'labels', 'subtasks'])
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
