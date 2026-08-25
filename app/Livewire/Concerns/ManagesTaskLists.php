<?php

namespace App\Livewire\Concerns;

use App\Livewire\Forms\TaskListForm;
use App\Models\Space;
use App\Models\TaskList;
use Flux\Flux;

trait ManagesTaskLists
{
    public TaskListForm $createListForm;

    public bool $showEditList = false;

    public ?int $editingListId = null;

    public string $editListName = '';

    public ?int $editListSpaceId = null;

    public function createList(): void
    {
        $this->createListForm->validate();

        $space = Space::findOrFail($this->createListForm->spaceId);

        $list = $space->lists()->create([
            'name' => trim($this->createListForm->name),
            'position' => ($space->lists()->max('position') ?? -1) + 1,
        ]);

        $list->createDefaultStatuses();
        $list->members()->attach(auth()->id());

        unset($this->listSpaces);
        $this->createListForm->reset();
        $this->js('$flux.modal("create-list-modal").close()');
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('List berhasil dibuat.', variant: 'success');
    }

    public function resetCreateListForm(): void
    {
        $this->createListForm->reset();
        $this->resetValidation();
    }

    public function openEditList(int $listId): void
    {
        if (! $this->authorizeListManagement()) {
            return;
        }

        $list = TaskList::findOrFail($listId);
        $this->editingListId = $list->id;
        $this->editListName = $list->name;
        $this->editListSpaceId = $list->space_id;
        $this->showEditList = true;
    }

    public function updateList(): void
    {
        if (! $this->authorizeListManagement()) {
            return;
        }

        if (! $this->editingListId) {
            return;
        }

        $list = TaskList::findOrFail($this->editingListId);
        $this->validate(
            [
                'editListName' => 'required|min:2|max:100|unique:task_lists,name,'.$this->editingListId.',id,space_id,'.$this->editListSpaceId,
                'editListSpaceId' => 'required|exists:spaces,id',
            ],
            [
                'editListName.required' => 'Nama list wajib diisi.',
                'editListName.unique' => 'Nama list sudah digunakan di space tujuan.',
                'editListSpaceId.required' => 'Pilih space tujuan terlebih dahulu.',
            ]
        );

        $targetSpace = Space::accessibleBy(auth()->id())->whereKey($this->editListSpaceId)->firstOrFail();
        $updates = [
            'name' => trim($this->editListName),
            'space_id' => $targetSpace->id,
        ];

        if ($list->space_id !== $targetSpace->id) {
            $updates['position'] = ($targetSpace->lists()->max('position') ?? -1) + 1;
        }

        $list->update($updates);
        unset($this->listSpaces);
        $this->reset(['editingListId', 'editListName', 'editListSpaceId', 'showEditList']);
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('List berhasil diperbarui.', variant: 'success');
    }

    public function deleteList(int $listId): void
    {
        if (! auth()->user()->canManageLists()) {
            Flux::toast('Hanya Administrator yang dapat menghapus list.', variant: 'danger');

            return;
        }

        TaskList::findOrFail($listId)->delete();

        unset($this->listSpaces);
        $this->skipRender();
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('List berhasil dihapus.', variant: 'success');
    }

    private function authorizeListManagement(): bool
    {
        if (auth()->user()->canManageLists()) {
            return true;
        }

        Flux::toast('Hanya Administrator yang dapat mengelola list.', variant: 'danger');

        return false;
    }
}
