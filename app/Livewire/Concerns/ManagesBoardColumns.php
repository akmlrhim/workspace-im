<?php

namespace App\Livewire\Concerns;

use App\Livewire\Forms\TaskColumnForm;
use App\Models\Task;
use App\Models\TaskStatus;
use Flux\Flux;

trait ManagesBoardColumns
{
    /**
     * @var array<int, string>
     */
    private const COLUMN_COLORS = ['#6b7280', '#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];

    public bool $showNewColumnInput = false;

    public TaskColumnForm $newColumn;

    public bool $showDeleteColumnConfirm = false;

    public ?int $deletingColumnId = null;

    public function addColumn(): void
    {
        if (! $this->canManageBoard()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

            return;
        }

        $this->newColumn->validate();

        $maxPosition = TaskStatus::where('task_list_id', $this->taskList->id)
            ->max('position') ?? -1;

        TaskStatus::create([
            'task_list_id' => $this->taskList->id,
            'name' => trim($this->newColumn->name),
            'color' => $this->newColumn->color,
            'position' => $maxPosition + 1,
            'type' => 'active',
        ]);

        $this->newColumn->reset();
        $this->showNewColumnInput = false;

        $this->broadcastChange();
        Flux::toast('Kolom baru berhasil ditambahkan.', variant: 'success');
    }

    public function saveColumnRename(int $columnId, string $name, string $color): void
    {
        if (! $this->canManageBoard()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah kolom ini.', variant: 'danger');

            return;
        }

        $name = trim($name);

        if ($name === '' || strlen($name) > 100) {
            return;
        }

        if (! in_array($color, self::COLUMN_COLORS, true)) {
            $color = '#6b7280';
        }

        TaskStatus::where('id', $columnId)
            ->where('task_list_id', $this->taskList->id)
            ->update(['name' => $name, 'color' => $color]);

        $this->broadcastChange();
        unset($this->statuses);
        Flux::toast('Kolom berhasil diperbarui.', variant: 'success');
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

        if (TaskStatus::where('task_list_id', $this->taskList->id)->count() <= 1) {
            $this->reset(['showDeleteColumnConfirm', 'deletingColumnId']);
            Flux::toast('Tidak bisa menghapus kolom terakhir.', variant: 'danger');

            return;
        }

        $this->moveTasksOutOfColumn($columnId);
        $column->delete();

        $this->reset(['showDeleteColumnConfirm', 'deletingColumnId']);
        $this->broadcastChange();
        Flux::toast('Kolom berhasil dihapus.', variant: 'success');
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function updateColumnOrder(array $orderedIds): void
    {
        if (! $this->canManageBoard()) {
            Flux::toast('Anda tidak memiliki izin untuk mengatur ulang kolom.', variant: 'danger');

            return;
        }

        if (empty($orderedIds)) {
            return;
        }

        $this->applyPositionOrder('task_statuses', $orderedIds, ['task_list_id' => $this->taskList->id]);

        $this->broadcastChange();
    }

    private function moveTasksOutOfColumn(int $columnId): void
    {
        $firstOther = TaskStatus::where('task_list_id', $this->taskList->id)
            ->where('id', '!=', $columnId)
            ->orderBy('position')
            ->first();

        if (! $firstOther) {
            return;
        }

        Task::where('task_status_id', $columnId)
            ->update([
                'task_status_id' => $firstOther->id,
                'completed_at' => $firstOther->type === 'closed' ? now() : null,
            ]);
    }
}
