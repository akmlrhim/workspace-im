<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\Task;
use App\Models\Project\TaskAttachment;
use App\Models\Project\TaskChecklistItem;
use Flux\Flux;
use Livewire\Attributes\Rule;

/**
 * The expandable detail panel of a single checklist item:
 * assignees, due date, file uploads and link attachments.
 */
trait ManagesChecklistItemDetails
{
    public ?int $activeChecklistItemId = null;

    public string $activeItemDueDate = '';

    public array $activeItemAssigneeIds = [];

    // Checklist item attachments — 5 MB max
    #[Rule(['activeItemFiles.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'], message: ['activeItemFiles.*.max' => 'Ukuran file maksimal 5 MB.', 'activeItemFiles.*.mimes' => 'Format file tidak didukung.'])]
    public array $activeItemFiles = [];

    public bool $showItemLinkForm = false;

    public string $newItemLinkUrl = '';

    public string $newItemLinkLabel = '';

    public function openChecklistItemPanel(int $itemId): void
    {
        if ($this->activeChecklistItemId === $itemId) {
            $this->closeChecklistItemPanel();

            return;
        }

        $item = TaskChecklistItem::with('assignees')->findOrFail($itemId);
        $this->activeChecklistItemId = $itemId;
        $this->activeItemDueDate = $item->due_date?->format('Y-m-d') ?? '';
        $this->activeItemAssigneeIds = $item->assignees->pluck('id')->toArray();
        $this->activeItemFiles = [];
    }

    public function closeChecklistItemPanel(): void
    {
        $this->activeChecklistItemId = null;
        $this->activeItemDueDate = '';
        $this->activeItemAssigneeIds = [];
        $this->activeItemFiles = [];
        $this->showItemLinkForm = false;
        $this->newItemLinkUrl = '';
        $this->newItemLinkLabel = '';
    }

    public function updateChecklistItemDueDate(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }
        TaskChecklistItem::findOrFail($this->activeChecklistItemId)
            ->update(['due_date' => $this->activeItemDueDate ?: null]);
        $this->broadcastChange();
        Flux::toast('Tanggal jatuh tempo checklist item berhasil diperbarui.', variant: 'success');
    }

    public function clearChecklistItemDueDate(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }
        $this->activeItemDueDate = '';
        TaskChecklistItem::findOrFail($this->activeChecklistItemId)
            ->update(['due_date' => null]);
        $this->broadcastChange();
        Flux::toast('Tanggal jatuh tempo checklist item berhasil dihapus.', variant: 'success');
    }

    public function updateChecklistItemAssignees(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }

        // Only allow task assignees
        $taskAssigneeIds = Task::findOrFail($this->taskId)->assignees()->pluck('users.id')->toArray();
        $validIds = array_values(array_intersect($this->activeItemAssigneeIds, $taskAssigneeIds));
        $this->activeItemAssigneeIds = $validIds;

        TaskChecklistItem::findOrFail($this->activeChecklistItemId)->assignees()->sync($validIds);
        $this->broadcastChange();
        Flux::toast('Assignees checklist item berhasil diperbarui.', variant: 'success');
    }

    public function updatedActiveItemFiles(): void
    {
        $this->uploadChecklistItemFiles();
    }

    public function uploadChecklistItemFiles(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }

        if (! $this->validateWithToast(['activeItemFiles.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'], [
            'activeItemFiles.*.max' => 'Ukuran file maksimal 5 MB.',
            'activeItemFiles.*.mimes' => 'Format file tidak didukung.',
        ])) {
            $this->activeItemFiles = [];

            return;
        }

        $item = TaskChecklistItem::with('checklist')->findOrFail($this->activeChecklistItemId);

        $uploaded = $this->storeUploadedAttachments($this->activeItemFiles, [
            'task_id' => $item->checklist->task_id,
            'task_checklist_item_id' => $item->id,
        ]);

        $this->activeItemFiles = [];

        if ($uploaded > 0) {
            $this->broadcastChange();
            Flux::toast('File berhasil diunggah.', variant: 'success');
        }
    }

    public function addChecklistItemLink(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }

        if (! $this->validateWithToast(
            ['newItemLinkUrl' => 'required|url|max:2048', 'newItemLinkLabel' => 'nullable|max:255'],
            [
                'newItemLinkUrl.required' => 'URL wajib diisi.',
                'newItemLinkUrl.url' => 'URL tidak valid.',
                'newItemLinkUrl.max' => 'URL maksimal 2048 karakter.',
                'newItemLinkLabel.max' => 'Label maksimal 255 karakter.',
            ]
        )) {
            return;
        }

        $item = TaskChecklistItem::with('checklist')->findOrFail($this->activeChecklistItemId);

        $this->storeLinkAttachment($this->newItemLinkUrl, $this->newItemLinkLabel, [
            'task_id' => $item->checklist->task_id,
            'task_checklist_item_id' => $item->id,
        ]);

        $this->reset(['newItemLinkUrl', 'newItemLinkLabel', 'showItemLinkForm']);
        $this->broadcastChange();
        Flux::toast('Link berhasil ditambahkan.', variant: 'success');
    }

    public function deleteChecklistItemAttachment(int $attachmentId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }

        $attachment = TaskAttachment::where('id', $attachmentId)
            ->where('task_id', $this->taskId)
            ->where('task_checklist_item_id', $this->activeChecklistItemId)
            ->firstOrFail();

        $this->deleteAttachmentRecord($attachment);
        $this->broadcastChange();
        Flux::toast('Lampiran berhasil dihapus.', variant: 'danger');
    }
}
