<?php

namespace App\Livewire\Concerns;

use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use Flux\Flux;

trait ManagesTaskChecklists
{
    public bool $showChecklistForm = false;

    public string $newChecklistName = '';

    public ?int $addingItemToChecklistId = null;

    public string $newChecklistItemTitle = '';

    public function addChecklist(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        if (! $this->validateWithToast(['newChecklistName' => 'required|min:1|max:200'], [
            'newChecklistName.required' => 'Nama checklist wajib diisi.',
            'newChecklistName.max' => 'Nama checklist maksimal 200 karakter.',
        ])) {
            return;
        }

        $maxPosition = TaskChecklist::where('task_id', $this->taskId)->max('position') ?? -1;

        TaskChecklist::create([
            'task_id' => $this->taskId,
            'name' => trim($this->newChecklistName),
            'position' => $maxPosition + 1,
        ]);

        $this->reset(['newChecklistName', 'showChecklistForm']);
        $this->broadcastChange();
        Flux::toast('Checklist berhasil dibuat.', variant: 'success');
    }

    public function deleteChecklist(int $checklistId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        TaskChecklist::where('task_id', $this->taskId)->findOrFail($checklistId)->delete();
        $this->broadcastChange();
        Flux::toast('Checklist berhasil dihapus.', variant: 'danger');
    }

    public function editChecklistName(int $checklistId, string $name): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $name = trim($name);
        if (empty($name)) {
            return;
        }
        TaskChecklist::where('task_id', $this->taskId)->findOrFail($checklistId)->update(['name' => $name]);
        $this->broadcastChange();
    }

    public function openAddChecklistItem(int $checklistId): void
    {
        $this->addingItemToChecklistId = $this->addingItemToChecklistId === $checklistId ? null : $checklistId;
        $this->newChecklistItemTitle = '';
    }

    public function addChecklistItem(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        if (! $this->validateWithToast(['newChecklistItemTitle' => 'required|min:1|max:500'], [
            'newChecklistItemTitle.required' => 'Judul item wajib diisi.',
            'newChecklistItemTitle.max' => 'Judul item maksimal 500 karakter.',
        ])) {
            return;
        }

        $checklist = TaskChecklist::where('task_id', $this->taskId)->findOrFail($this->addingItemToChecklistId);
        $maxPosition = $checklist->items()->max('position') ?? -1;

        TaskChecklistItem::create([
            'task_checklist_id' => $checklist->id,
            'title' => trim($this->newChecklistItemTitle),
            'position' => $maxPosition + 1,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['newChecklistItemTitle', 'addingItemToChecklistId']);
        $this->broadcastChange();
        Flux::toast('Item checklist berhasil ditambahkan.', variant: 'success');
    }

    public function toggleChecklistItem(int $itemId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $item = $this->findOwnedChecklistItem($itemId);
        if (! $item) {
            return;
        }
        $item->update(['is_completed' => ! $item->is_completed]);
        $this->broadcastChange();
    }

    public function editChecklistItemTitle(int $itemId, string $title): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $title = trim($title);
        if (empty($title)) {
            return;
        }
        $item = $this->findOwnedChecklistItem($itemId);
        if (! $item) {
            return;
        }
        $item->update(['title' => $title]);
        $this->broadcastChange();
        Flux::toast('Item checklist berhasil diperbarui.', variant: 'success');
    }

    public function deleteChecklistItem(int $itemId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $item = $this->findOwnedChecklistItem($itemId);
        if (! $item) {
            return;
        }
        $item->delete();

        if ($this->activeChecklistItemId === $itemId) {
            $this->closeChecklistItemPanel();
        }

        $this->broadcastChange();
        Flux::toast('Item checklist berhasil dihapus.', variant: 'danger');
    }

    private function findOwnedChecklistItem(int $itemId): ?TaskChecklistItem
    {
        $item = TaskChecklistItem::with('checklist')->findOrFail($itemId);

        if ($item->checklist->task_id !== $this->taskId) {
            Flux::toast('Item tidak ditemukan dalam tugas ini.', variant: 'danger');

            return null;
        }

        return $item;
    }
}
