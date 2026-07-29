<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\TaskAttachment;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Task-level attachments: file uploads and link attachments.
 */
trait ManagesTaskAttachments
{
    // Attachments (task-level) — 5 MB max
    #[Rule(['uploadFiles.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'], message: ['uploadFiles.*.max' => 'Ukuran file maksimal 5 MB.', 'uploadFiles.*.mimes' => 'Format file tidak didukung.'])]
    public array $uploadFiles = [];

    public bool $showLinkForm = false;

    public string $newLinkUrl = '';

    public string $newLinkLabel = '';

    public function updatedUploadFiles(): void
    {
        $this->uploadAttachment();
    }

    public function uploadAttachment(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        if (! $this->validateWithToast(['uploadFiles.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'], [
            'uploadFiles.*.max' => 'Ukuran file maksimal 5 MB.',
            'uploadFiles.*.mimes' => 'Format file tidak didukung.',
        ])) {
            $this->reset('uploadFiles');

            return;
        }

        $uploaded = $this->storeUploadedAttachments($this->uploadFiles);

        $this->reset('uploadFiles');

        if ($uploaded > 0) {
            $this->dispatch('task-updated');
            $this->broadcastChange();
            Flux::toast('File berhasil diunggah.', variant: 'success');
        }
    }

    public function addLinkAttachment(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }

        if (! $this->validateWithToast(
            ['newLinkUrl' => 'required|url|max:2048', 'newLinkLabel' => 'nullable|max:255'],
            [
                'newLinkUrl.required' => 'URL wajib diisi.',
                'newLinkUrl.url' => 'URL tidak valid.',
                'newLinkUrl.max' => 'URL maksimal 2048 karakter.',
                'newLinkLabel.max' => 'Label maksimal 255 karakter.',
            ]
        )) {
            return;
        }

        $this->storeLinkAttachment($this->newLinkUrl, $this->newLinkLabel);

        $this->reset(['newLinkUrl', 'newLinkLabel', 'showLinkForm']);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Link berhasil ditambahkan.', variant: 'success');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }

        $attachment = TaskAttachment::where('id', $attachmentId)
            ->where('task_id', $this->taskId)
            ->whereNull('task_comment_id')
            ->whereNull('task_checklist_item_id')
            ->firstOrFail();

        $this->deleteAttachmentRecord($attachment);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Lampiran berhasil dihapus.', variant: 'danger');
    }

    /**
     * Persist uploaded files as attachments of the current task.
     *
     * @param  array<int, TemporaryUploadedFile>  $files
     * @param  array<string, mixed>  $extraAttributes  Overrides merged into every created attachment.
     * @return int Number of files stored successfully.
     */
    private function storeUploadedAttachments(array $files, array $extraAttributes = []): int
    {
        $stored = 0;

        foreach ($files as $file) {
            $path = $file->store('task-attachments', 'public');

            if ($path === false) {
                Flux::toast('Gagal menyimpan file. Coba lagi.', variant: 'danger');

                continue;
            }

            TaskAttachment::create([
                'task_id' => $this->taskId,
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName() ?: 'file',
                'path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size' => $file->getSize() ?? 0,
                ...$extraAttributes,
            ]);

            $stored++;
        }

        return $stored;
    }

    /**
     * @param  array<string, mixed>  $extraAttributes  Overrides merged into the created attachment.
     */
    private function storeLinkAttachment(string $url, string $label, array $extraAttributes = []): TaskAttachment
    {
        $url = trim($url);

        return TaskAttachment::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'filename' => trim($label) ?: $url,
            'path' => $url,
            'mime_type' => 'link',
            'size' => 0,
            'is_link' => true,
            ...$extraAttributes,
        ]);
    }

    /**
     * Remove an attachment together with its stored file, when it is not a link.
     */
    private function deleteAttachmentRecord(TaskAttachment $attachment): void
    {
        if (! $attachment->is_link && $attachment->path) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();
    }
}
