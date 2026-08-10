<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\TaskActivity;
use App\Models\User;
use Flux\Flux;

/**
 * Inline editing of a task's own columns: title, description, status,
 * priority, deadline, and assignees.
 */
trait EditsTaskFields
{
    /** @var array<int, string> */
    private const PRIORITIES = ['urgent', 'high', 'normal', 'low'];

    public string $taskTitle = '';

    public string $taskDescription = '';

    public string $taskPriority = 'normal';

    public ?int $taskStatusId = null;

    public ?string $taskDueDate = null;

    public array $taskAssigneeIds = [];

    public function saveTitle(): void
    {
        $task = $this->authorizeManageTask();

        if (! $task) {
            return;
        }

        if (! $this->validateWithToast(['taskTitle' => 'required|min:1|max:500'])) {
            return;
        }

        $task->update(['title' => $this->taskTitle]);
        $this->afterTaskChange();
    }

    public function saveDescription(): void
    {
        $task = $this->authorizeManageTask();

        if (! $task) {
            return;
        }

        $task->update(['description' => $this->taskDescription]);
        $this->afterTaskChange('Deskripsi disimpan.');
    }

    public function updateStatus(int $statusId): void
    {
        $task = $this->authorizeManageTask();

        if (! $task) {
            return;
        }

        // Validate status belongs to this task's list
        $validStatusIds = $task->taskList->statuses()->pluck('id')->toArray();

        if (! in_array($statusId, $validStatusIds)) {
            Flux::toast('Status tidak valid untuk list ini.', variant: 'danger');

            return;
        }

        $oldStatus = $task->status->name ?? '-';
        $task->update(['task_status_id' => $statusId]);
        $task->refresh();
        $this->taskStatusId = $statusId;

        $this->logActivity($task->id, 'status_changed', $oldStatus, $task->status->name);
        $this->afterTaskChange('Status diperbarui.');
    }

    public function updatePriority(string $priority): void
    {
        if (! in_array($priority, self::PRIORITIES, true)) {
            Flux::toast('Prioritas tidak valid.', variant: 'danger');

            return;
        }

        $task = $this->authorizeManageTask();

        if (! $task) {
            return;
        }

        $old = $task->priority;
        $task->update(['priority' => $priority]);
        $this->taskPriority = $priority;

        $this->logActivity($task->id, 'priority_changed', $old, $priority);
        $this->afterTaskChange('Prioritas diperbarui.');
    }

    public function updateDueDate(): void
    {
        $task = $this->authorizeManageTask();

        if (! $task) {
            return;
        }

        $task->update(['due_date' => $this->taskDueDate ?: null]);
        $this->afterTaskChange('Tanggal jatuh tempo diperbarui.');
    }

    public function updateAssignees(): void
    {
        $task = $this->authorizeManageTask();

        if (! $task) {
            return;
        }

        $task->load('taskList.members');

        $listMemberIds = $task->taskList->members->pluck('id')->toArray();
        $invalidIds = array_diff($this->taskAssigneeIds, $listMemberIds);

        if (! empty($invalidIds)) {
            Flux::toast('User yang dipilih bukan anggota dari list ini.', variant: 'danger');
            $this->taskAssigneeIds = array_values(array_intersect($this->taskAssigneeIds, $listMemberIds));

            return;
        }

        $task->assignees()->sync($this->taskAssigneeIds);

        $names = User::whereIn('id', $this->taskAssigneeIds)->pluck('name')->join(', ') ?: 'Unassigned';
        $this->logActivity($task->id, 'assignee_changed', null, $names);

        $this->afterTaskChange('Penugasan berhasil diperbarui.');
    }

    private function logActivity(int $taskId, string $type, ?string $oldValue, string $newValue): void
    {
        TaskActivity::create([
            'task_id' => $taskId,
            'user_id' => auth()->id(),
            'type' => $type,
            'old_value' => $oldValue,
            'new_value' => $newValue,
        ]);
    }

    /** Notify the parent board, broadcast to other viewers, and optionally confirm. */
    private function afterTaskChange(?string $toast = null): void
    {
        $this->dispatch('task-updated');
        $this->broadcastChange();

        if ($toast !== null) {
            Flux::toast($toast, variant: 'success');
        }
    }
}
