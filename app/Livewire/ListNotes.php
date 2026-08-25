<?php

namespace App\Livewire;

use App\Livewire\Concerns\ManagesNoteAttachments;
use App\Livewire\Concerns\StagesNewNoteAttachments;
use App\Livewire\Concerns\ValidatesWithToast;
use App\Models\Space;
use App\Models\TaskList;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ListNotes extends Component
{
    use ManagesNoteAttachments;
    use StagesNewNoteAttachments;
    use ValidatesWithToast;
    use WithFileUploads;

    /**
     * @var array<int, string>
     */
    private const NEW_NOTE_FIELDS = [
        'newNoteTitle', 'newNoteContent', 'showNewNoteForm',
        'newNoteFiles', 'newNoteLinks', 'newNoteLinkUrl', 'newNoteLinkLabel',
    ];

    public Space $space;

    public TaskList $taskList;

    public bool $showNewNoteForm = false;

    public string $newNoteTitle = '';

    public string $newNoteContent = '';

    public bool $showEditNoteForm = false;

    public ?int $editingNoteId = null;

    public string $editNoteTitle = '';

    public string $editNoteContent = '';

    public bool $showDeleteNoteConfirm = false;

    public ?int $confirmingDeleteNoteId = null;

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;
    }

    #[Computed]
    public function notes(): Collection
    {
        return $this->taskList->notes()
            ->with(['creator', 'attachments.user'])
            ->latest('updated_at')
            ->get();
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

    private function guardManage(): bool
    {
        if (! $this->canManage()) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah catatan ini.', variant: 'danger');

            return false;
        }

        return true;
    }

    /**
     * @return array{0: array<string, string>, 1: array<string, string>}
     */
    private function noteRules(string $titleField, string $contentField): array
    {
        return [
            [$titleField => 'required|string|max:255', $contentField => 'nullable|string|max:10000'],
            [
                "{$titleField}.required" => 'Judul catatan wajib diisi.',
                "{$titleField}.max" => 'Judul maksimal 255 karakter.',
                "{$contentField}.max" => 'Isi catatan maksimal 10.000 karakter.',
            ],
        ];
    }

    public function createNote(): void
    {
        if (! $this->guardManage()) {
            return;
        }

        if (! $this->validateWithToast(...$this->noteRules('newNoteTitle', 'newNoteContent'))) {
            return;
        }

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

        $this->reset(self::NEW_NOTE_FIELDS);
        unset($this->notes);

        Flux::toast('Catatan berhasil dibuat.', variant: 'success');
    }

    public function cancelNewNote(): void
    {
        $this->reset(self::NEW_NOTE_FIELDS);
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

        if (! $this->validateWithToast(...$this->noteRules('editNoteTitle', 'editNoteContent'))) {
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
            $this->deleteStoredFile($attachment);
        }

        $note->delete();

        $this->reset(['showDeleteNoteConfirm', 'confirmingDeleteNoteId']);
        unset($this->notes);

        Flux::toast('Catatan dihapus.', variant: 'success');
    }

    public function render()
    {
        return view('livewire.list-notes', [
            'canManage' => $this->canManage(),
        ]);
    }
}
