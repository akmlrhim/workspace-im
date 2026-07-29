<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\TaskLabel;
use Flux\Flux;

/**
 * Label handling for the task detail component.
 */
trait ManagesTaskLabels
{
    public bool $showLabelForm = false;

    public string $newLabelName = '';

    public string $newLabelColor = '#6366f1';

    public function toggleLabel(int $labelId): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        $task->labels()->toggle($labelId);
        $this->dispatch('task-updated');
        $this->broadcastChange();
    }

    public function createLabel(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }

        if (! $this->validateWithToast(['newLabelName' => 'required|min:1|max:100'], [
            'newLabelName.required' => 'Nama label wajib diisi.',
            'newLabelName.max' => 'Nama label maksimal 100 karakter.',
        ])) {
            return;
        }

        $task->load('taskList.space.workspace');
        $workspaceId = $task->taskList->space->workspace_id;

        $label = TaskLabel::create([
            'workspace_id' => $workspaceId,
            'name' => trim($this->newLabelName),
            'color' => $this->newLabelColor,
        ]);

        $task->labels()->attach($label->id);

        $this->reset(['newLabelName', 'newLabelColor', 'showLabelForm']);
        $this->newLabelColor = '#6366f1';

        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Label dibuat dan ditambahkan.', variant: 'success');
    }
}
