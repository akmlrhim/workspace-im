<?php

namespace App\Livewire\Project;

use App\Models\Project\ListNote;
use App\Models\Project\ListNoteAttachment;
use App\Models\Project\Space;
use App\Models\Project\TaskList;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ListNotes extends Component
{
    use WithFileUploads;

    public Space $space;

    public TaskList $taskList;

    // Create note
    public bool $showNewNoteForm = false;

    public string $newNoteTitle = '';

    public string $newNoteContent = '';

    // Staged attachments for the note being created (before it has an id)
    /** @var array<int, TemporaryUploadedFile> */
    public array $newNoteFiles = [];

    /** @var array<int, array{url: string, label: string}> */
    public array $newNoteLinks = [];

    public string $newNoteLinkUrl = '';

    public string $newNoteLinkLabel = '';

    // Edit note (modal)
    public bool $showEditNoteForm = false;

    public ?int $editingNoteId = null;

    public string $editNoteTitle = '';

    public string $editNoteContent = '';

    // Delete confirmations (modal)
    public bool $showDeleteNoteConfirm = false;

    public ?int $confirmingDeleteNoteId = null;

    public bool $showDeleteAttachmentConfirm = false;

    public ?int $confirmingDeleteAttachmentId = null;

    // Attachment upload target (note id)
    /** @var array<int, array<int, TemporaryUploadedFile>> */
    public array $uploadFiles = [];

    // Link form (add link to an existing note)
    public bool $showLinkForm = false;

    public ?int $linkFormFor = null;

    public string $newLinkUrl = '';

    public string $newLinkLabel = '';

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;
    }

    private function canManage(): bool
    {
        return once(function () {
            $user = auth()->user();

            if ($user->canManageAllProjects()) {
                return true;
            }

            if ($user->isManager() && $this->taskList->isAccessibleBy($user)) {
                return true;
            }

            return $this->taskList->members()->where('users.id', $user->id)->exists();
        });
    }

    #[Computed]
    public function notes(): Collection
    {
        return $this->taskList->notes()
            ->with(['creator', 'attachments.user'])
            ->latest('updated_at')
            ->get();
    }

    private function validateWithToast(array $rules, array $messages = []): bool
    {
        $data = [];
        foreach ($rules as $field => $rule) {
            $data[$field] = data_get($this, $field);
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            Flux::toast($validator->errors()->first(), variant: 'danger');

            return false;
        }

        return true;
    }

    private function guardManage(): bool
    {
        if (! $this->canManage()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah catatan ini.', variant: 'danger');

            return false;
        }

        return true;
    }

    // ─── Note CRUD ─────────────────────────────────────────────────

    public function createNote(): void
    {
        if (! $this->guardManage()) {
            return;
        }

        if (! $this->validateWithToast(
            ['newNoteTitle' => 'required|string|max:255', 'newNoteContent' => 'nullable|string|max:10000'],
            [
                'newNoteTitle.required' => 'Judul catatan wajib diisi.',
                'newNoteTitle.max' => 'Judul maksimal 255 karakter.',
                'newNoteContent.max' => 'Isi catatan maksimal 10.000 karakter.',
            ]
        )) {
            return;
        }

        // Validate any staged files before creating the note so nothing is half-saved.
        if (! empty($this->newNoteFiles) && ! $this->validateFiles($this->newNoteFiles)) {
            return;
        }

        $maxPosition = $this->taskList->notes()->max('position') ?? -1;

        $note = $this->taskList->notes()->create([
            'created_by' => auth()->id(),
            'title' => trim($this->newNoteTitle),
            'content' => trim($this->newNoteContent) ?: null,
            'position' => $maxPosition + 1,
        ]);

        $this->storeUploadedFiles($note, $this->newNoteFiles);

        foreach ($this->newNoteLinks as $link) {
            $this->createLinkAttachment($note, $link['url'], $link['label']);
        }

        $this->reset(['newNoteTitle', 'newNoteContent', 'showNewNoteForm', 'newNoteFiles', 'newNoteLinks', 'newNoteLinkUrl', 'newNoteLinkLabel']);
        unset($this->notes);

        Flux::toast('Catatan berhasil dibuat.', variant: 'success');
    }

    public function cancelNewNote(): void
    {
        $this->reset(['newNoteTitle', 'newNoteContent', 'showNewNoteForm', 'newNoteFiles', 'newNoteLinks', 'newNoteLinkUrl', 'newNoteLinkLabel']);
    }

    // ─── Staged attachments (for the note being created) ───────────

    public function addStagedLink(): void
    {
        if (! $this->guardManage()) {
            return;
        }

        if (! $this->validateWithToast(
            ['newNoteLinkUrl' => 'required|url|max:2048', 'newNoteLinkLabel' => 'nullable|max:255'],
            [
                'newNoteLinkUrl.required' => 'URL wajib diisi.',
                'newNoteLinkUrl.url' => 'URL tidak valid.',
                'newNoteLinkUrl.max' => 'URL maksimal 2048 karakter.',
                'newNoteLinkLabel.max' => 'Label maksimal 255 karakter.',
            ]
        )) {
            return;
        }

        $url = trim($this->newNoteLinkUrl);
        $label = trim($this->newNoteLinkLabel) ?: $url;

        $this->newNoteLinks[] = ['url' => $url, 'label' => $label];
        $this->reset(['newNoteLinkUrl', 'newNoteLinkLabel']);
    }

    public function removeStagedLink(int $index): void
    {
        unset($this->newNoteLinks[$index]);
        $this->newNoteLinks = array_values($this->newNoteLinks);
    }

    public function removeStagedFile(int $index): void
    {
        unset($this->newNoteFiles[$index]);
        $this->newNoteFiles = array_values($this->newNoteFiles);
    }

    public function updatedNewNoteFiles(): void
    {
        if (! empty($this->newNoteFiles)) {
            $this->validateFiles($this->newNoteFiles);
        }
    }

    public function openEditNote(int $noteId): void
    {
        if (! $this->guardManage()) {
            return;
        }

        $note = $this->taskList->notes()->findOrFail($noteId);

        $this->editingNoteId = $note->id;
        $this->editNoteTitle = $note->title;
        $this->editNoteContent = $note->content ?? '';
        $this->showEditNoteForm = true;
    }

    public function updateNote(): void
    {
        if (! $this->guardManage() || $this->editingNoteId === null) {
            return;
        }

        if (! $this->validateWithToast(
            ['editNoteTitle' => 'required|string|max:255', 'editNoteContent' => 'nullable|string|max:10000'],
            [
                'editNoteTitle.required' => 'Judul catatan wajib diisi.',
                'editNoteTitle.max' => 'Judul maksimal 255 karakter.',
                'editNoteContent.max' => 'Isi catatan maksimal 10.000 karakter.',
            ]
        )) {
            return;
        }

        $note = $this->taskList->notes()->findOrFail($this->editingNoteId);

        $note->update([
            'title' => trim($this->editNoteTitle),
            'content' => trim($this->editNoteContent) ?: null,
        ]);

        $this->reset(['showEditNoteForm', 'editingNoteId', 'editNoteTitle', 'editNoteContent']);
        unset($this->notes);

        Flux::toast('Catatan diperbarui.', variant: 'success');
    }

    public function confirmDeleteNote(int $noteId): void
    {
        if (! $this->guardManage()) {
            return;
        }

        $this->confirmingDeleteNoteId = $noteId;
        $this->showDeleteNoteConfirm = true;
    }

    public function deleteNote(): void
    {
        if (! $this->guardManage() || $this->confirmingDeleteNoteId === null) {
            return;
        }

        $note = $this->taskList->notes()->with('attachments')->findOrFail($this->confirmingDeleteNoteId);

        foreach ($note->attachments as $attachment) {
            if (! $attachment->is_link && $attachment->path) {
                Storage::disk('public')->delete($attachment->path);
            }
        }

        $note->delete();

        $this->reset(['showDeleteNoteConfirm', 'confirmingDeleteNoteId']);
        unset($this->notes);

        Flux::toast('Catatan dihapus.', variant: 'success');
    }

    // ─── Attachments ───────────────────────────────────────────────

    public function updatedUploadFiles(): void
    {
        // Upload happens per-note; the array key is the note id.
        foreach ($this->uploadFiles as $noteId => $files) {
            if (! empty($files)) {
                $this->uploadFilesForNote((int) $noteId);
            }
        }
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
     * Validate a batch of uploaded files, showing a toast on failure.
     *
     * @param  array<int, TemporaryUploadedFile>  $files
     */
    private function validateFiles(array $files): bool
    {
        $validator = Validator::make(
            ['files' => $files],
            ['files.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'],
            [
                'files.*.max' => 'Ukuran file maksimal 10 MB.',
                'files.*.mimes' => 'Format file tidak didukung.',
            ]
        );

        if ($validator->fails()) {
            Flux::toast($validator->errors()->first(), variant: 'danger');

            return false;
        }

        return true;
    }

    /**
     * Persist already-validated uploaded files as attachments on the note.
     *
     * @param  array<int, TemporaryUploadedFile>  $files
     */
    private function storeUploadedFiles(ListNote $note, array $files): int
    {
        $uploaded = 0;

        foreach ($files as $file) {
            $path = $file->store('list-note-attachments', 'public');

            if ($path === false) {
                Flux::toast('Gagal menyimpan file. Coba lagi.', variant: 'danger');

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
        $label = trim($this->newLinkLabel) ?: $url;

        $this->createLinkAttachment($note, $url, $label);

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

        if (! $attachment->is_link && $attachment->path) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();

        $this->reset(['showDeleteAttachmentConfirm', 'confirmingDeleteAttachmentId']);
        unset($this->notes);

        Flux::toast('Lampiran dihapus.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.project.list-notes', [
            'canManage' => $this->canManage(),
        ]);
    }
}
