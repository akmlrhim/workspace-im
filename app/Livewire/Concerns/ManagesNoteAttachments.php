<?php

namespace App\Livewire\Concerns;

use App\Models\ListNote;
use App\Models\ListNoteAttachment;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait ManagesNoteAttachments
{
    use ValidatesAttachmentUploads;

    /**
     * @var array<int, array<int, TemporaryUploadedFile>>
     */
    public array $uploadFiles = [];

    public bool $showLinkForm = false;

    public ?int $linkFormFor = null;

    public string $newLinkUrl = '';

    public string $newLinkLabel = '';

    public bool $showDeleteAttachmentConfirm = false;

    public ?int $confirmingDeleteAttachmentId = null;

    public function updatedUploadFiles(): void
    {
        foreach ($this->uploadFiles as $noteId => $files) {
            if (! empty($files)) {
                $this->uploadFilesForNote((int) $noteId);
            }
        }
    }

    public function openLinkForm(int $noteId): void
    {
        if (! $this->guardManage()) {
            return;
        }

        $this->linkFormFor = $noteId;
        $this->reset(['newLinkUrl', 'newLinkLabel']);
        $this->showLinkForm = true;
    }

    public function addLink(): void
    {
        if (! $this->guardManage() || $this->linkFormFor === null) {
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

        $note = $this->taskList->notes()->findOrFail($this->linkFormFor);

        $url = trim($this->newLinkUrl);

        $this->createLinkAttachment($note, $url, trim($this->newLinkLabel) ?: $url);

        $note->touch();
        $this->reset(['showLinkForm', 'newLinkUrl', 'newLinkLabel', 'linkFormFor']);
        unset($this->notes);

        Flux::toast('Link berhasil ditambahkan.', variant: 'success');
    }

    public function confirmDeleteAttachment(int $attachmentId): void
    {
        if (! $this->guardManage()) {
            return;
        }

        $this->confirmingDeleteAttachmentId = $attachmentId;
        $this->showDeleteAttachmentConfirm = true;
    }

    public function deleteAttachment(): void
    {
        if (! $this->guardManage() || $this->confirmingDeleteAttachmentId === null) {
            return;
        }

        $attachment = ListNoteAttachment::where('id', $this->confirmingDeleteAttachmentId)
            ->whereHas('note', fn ($q) => $q->where('task_list_id', $this->taskList->id))
            ->firstOrFail();

        $this->deleteStoredFile($attachment);
        $attachment->delete();

        $this->reset(['showDeleteAttachmentConfirm', 'confirmingDeleteAttachmentId']);
        unset($this->notes);

        Flux::toast('Lampiran dihapus.', variant: 'success');
    }

    private function uploadFilesForNote(int $noteId): void
    {
        if (! $this->guardManage()) {
            $this->reset('uploadFiles');

            return;
        }

        $note = $this->taskList->notes()->find($noteId);

        if (! $note) {
            $this->reset('uploadFiles');

            return;
        }

        if (! $this->validateFiles($this->uploadFiles[$noteId])) {
            unset($this->uploadFiles[$noteId]);

            return;
        }

        $uploaded = $this->storeUploadedFiles($note, $this->uploadFiles[$noteId]);

        unset($this->uploadFiles[$noteId]);
        $note->touch();
        unset($this->notes);

        if ($uploaded > 0) {
            Flux::toast('File berhasil diunggah.', variant: 'success');
        }
    }

    /**
     * @param  array<int, TemporaryUploadedFile>  $files
     */
    private function validateFiles(array $files): bool
    {
        return $this->validateAttachmentFiles($files);
    }

    /**
     * @param  array<int, TemporaryUploadedFile>  $files
     * @return int number of files stored
     */
    private function storeUploadedFiles(ListNote $note, array $files): int
    {
        $uploaded = 0;
        $failed = 0;

        foreach ($files as $file) {
            $path = null;

            try {
                $path = $file->store('list-note-attachments', 'public');

                if ($path === false) {
                    $failed++;

                    continue;
                }

                $note->attachments()->create([
                    'user_id' => auth()->id(),
                    'filename' => $file->getClientOriginalName() ?: 'file',
                    'path' => $path,
                    'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                    'size' => $file->getSize() ?? 0,
                    'is_link' => false,
                ]);

                $uploaded++;
            } catch (\Throwable $e) {
                $failed++;

                if ($path !== null) {
                    Storage::disk('public')->delete($path);
                }

                Log::warning('Gagal menyimpan lampiran catatan #'.$note->id, [
                    'filename' => $file->getClientOriginalName() ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($failed > 0) {
            Flux::toast($failed === 1 ? '1 file gagal disimpan. Coba lagi.' : "{$failed} file gagal disimpan. Coba lagi.", variant: 'danger');
        }

        return $uploaded;
    }

    private function createLinkAttachment(ListNote $note, string $url, string $label): void
    {
        $note->attachments()->create([
            'user_id' => auth()->id(),
            'filename' => $label,
            'path' => $url,
            'mime_type' => 'link',
            'size' => 0,
            'is_link' => true,
        ]);
    }

    private function deleteStoredFile(ListNoteAttachment $attachment): void
    {
        if (! $attachment->is_link && $attachment->path) {
            Storage::disk('public')->delete($attachment->path);
        }
    }
}
