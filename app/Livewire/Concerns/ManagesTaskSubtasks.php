<?php

namespace App\Livewire\Concerns;

use App\Models\Task;
use Flux\Flux;

trait ManagesTaskSubtasks
{
    public string $newSubtaskTitle = '';

    public bool $showSubtaskForm = false;

    public function addSubtask(): void
    {
        $parentTask = $this->authorizeManageTask();
        if (! $parentTask) {
            return;
        }
        if (! $this->validateWithToast(['newSubtaskTitle' => 'required|min:1|max:500'], [
            'newSubtaskTitle.required' => 'Judul subtask wajib diisi.',
            'newSubtaskTitle.max' => 'Judul subtask maksimal 500 karakter.',
        ])) {
            return;
        }
        $maxPosition = Task::where('parent_id', $this->taskId)->max('position') ?? -1;

        $firstStatus = $parentTask->taskList->statuses()->orderBy('position')->first();

        if (! $firstStatus) {
            Flux::toast('Tidak ada status tersedia untuk list ini.', variant: 'danger');

            return;
        }

        Task::create([
            'task_list_id' => $parentTask->task_list_id,
            'task_status_id' => $firstStatus->id,
            'parent_id' => $this->taskId,
            'title' => $this->newSubtaskTitle,
            'priority' => 'normal',
            'position' => $maxPosition + 1,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['newSubtaskTitle', 'showSubtaskForm']);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Subtask berhasil ditambahkan.', variant: 'success');
    }

    public function toggleSubtaskComplete(int $subtaskId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }

        $subtask = Task::where('parent_id', $this->taskId)->findOrFail($subtaskId);
        $subtask->update(['is_completed' => ! $subtask->is_completed]);

        if ($subtask->is_completed) {
            $doneStatus = $subtask->taskList->statuses()->orderByDesc('position')->first();
            if ($doneStatus) {
                $subtask->update(['task_status_id' => $doneStatus->id]);
            }
        } else {
            $todoStatus = $subtask->taskList->statuses()->orderBy('position')->first();
            if ($todoStatus) {
                $subtask->update(['task_status_id' => $todoStatus->id]);
            }
        }

        $this->dispatch('task-updated');
        $this->broadcastChange();
    }

    public function editSubtaskTitle(int $subtaskId, string $newTitle): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $newTitle = trim($newTitle);
        if (empty($newTitle)) {
            Flux::toast('Judul subtask wajib diisi.', variant: 'warning');

            return;
        }

        Task::where('parent_id', $this->taskId)->findOrFail($subtaskId)->update(['title' => $newTitle]);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Subtask berhasil diperbarui.', variant: 'success');
    }

    public function deleteSubtask(int $subtaskId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        Task::where('parent_id', $this->taskId)->findOrFail($subtaskId)->delete();
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Subtask berhasil dihapus.', variant: 'success');
    }
}
