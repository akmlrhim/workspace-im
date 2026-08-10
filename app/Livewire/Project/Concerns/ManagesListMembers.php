<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\TaskList;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;

/**
 * The manage-members modal for a task list.
 */
trait ManagesListMembers
{
    public bool $showManageMembers = false;

    public ?int $managingListId = null;

    public array $listMemberIds = [];

    public function openManageMembers(int $listId): void
    {
        if (! $this->authorizeListManagement()) {
            return;
        }

        $list = TaskList::with('members')->findOrFail($listId);
        $this->managingListId = $list->id;
        $this->listMemberIds = $list->members->pluck('id')->toArray();
        $this->showManageMembers = true;
    }

    public function saveMembers(): void
    {
        if (! $this->authorizeListManagement()) {
            return;
        }

        if (! $this->managingListId) {
            return;
        }

        $list = TaskList::with(['members', 'tasks', 'space'])->findOrFail($this->managingListId);

        // Validate against real user IDs in the DB only — prevents arbitrary ID injection.
        $requestedIds = collect($this->listMemberIds)->map(fn ($id) => (int) $id)->toArray();
        $validIds = User::whereIn('id', $requestedIds)->pluck('id')->toArray();

        $removedIds = array_diff($list->members->pluck('id')->toArray(), $validIds);
        $list->members()->sync($validIds);

        if (! empty($removedIds)) {
            foreach ($list->tasks as $task) {
                $task->assignees()->detach($removedIds);
            }
        }

        unset($this->listSpaces);
        $this->reset(['managingListId', 'listMemberIds', 'showManageMembers']);
        $this->broadcastChange();
        Flux::toast('Anggota list berhasil diperbarui.', variant: 'success');
    }

    /** All users for the manage-members modal. */
    #[Computed]
    public function allUsers()
    {
        return User::orderBy('name')->get();
    }
}
