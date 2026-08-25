<?php

namespace App\Livewire\Concerns;

use App\Models\TaskAttachment;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ManagesTaskAttachments
{
    use ValidatesAttachmentUploads;

    /**
     * @var array<int, TemporaryUploadedFile>
     */
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
        if (! $this->validateAttachmentFiles($this->uploadFiles)) {
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
     * @param  array<int, TemporaryUploadedFile>  $files
     * @param  array<string, mixed>  $extraAttributes  Overrides merged into every created attachment.
     * @return int Number of files stored successfully.
     */
    private function storeUploadedAttachments(array $files, array $extraAttributes = []): int
    {
        $stored = 0;
        $failed = 0;

        foreach ($files as $file) {
            $path = null;

            try {
                $path = $file->store('task-attachments', 'public');

                if ($path === false) {
                    $failed++;

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
            } catch (\Throwable $e) {
                $failed++;

                if ($path !== null) {
                    Storage::disk('public')->delete($path);
                }

                Log::warning('Gagal menyimpan lampiran task #'.$this->taskId, [
                    'filename' => $file->getClientOriginalName() ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($failed > 0) {
            Flux::toast($failed === 1 ? '1 file gagal disimpan. Coba lagi.' : "{$failed} file gagal disimpan. Coba lagi.", variant: 'danger');
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

    private function deleteAttachmentRecord(TaskAttachment $attachment): void
    {
        if (! $attachment->is_link && $attachment->path) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();
    }
}
