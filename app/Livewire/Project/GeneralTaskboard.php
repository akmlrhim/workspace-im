<?php

namespace App\Livewire\Project;

use App\Events\SpaceUpdated;
use App\Models\Project\TaskList;
use App\Models\Project\Workspace;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Taskboard')]
class GeneralTaskboard extends Component
{
    public string $search = '';

    public ?int $workspaceId = null;

    // Edit list
    public bool $showEditList = false;

    public ?int $editingListId = null;

    public string $editListName = '';

    // Manage list members
    public bool $showManageMembers = false;

    public ?int $managingListId = null;

    public array $listMemberIds = [];

    public function mount(): void
    {
        $this->workspaceId = Workspace::where('owner_id', auth()->id())->value('id');
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        $listeners = [];

        if ($this->workspaceId) {
            $listeners["echo:workspace.{$this->workspaceId},SpaceUpdated"] = 'onBroadcastUpdate';
            $listeners["echo:workspace.{$this->workspaceId},TaskUpdatedGlobal"] = 'onBroadcastUpdate';
        }

        return $listeners;
    }

    public function onBroadcastUpdate(array $event): void
    {
        if (($event['triggeredBy'] ?? null) == auth()->id()) {
            $this->skipRender();

            return;
        }
    }

    private function authorizeListAccess(TaskList $list): bool
    {
        if ($list->isAccessibleBy(auth()->user())) {
            return true;
        }

        Flux::toast('Anda tidak memiliki akses ke list ini.', variant: 'danger');

        return false;
    }

    public function openEditList(int $listId): void
    {
        $list = TaskList::findOrFail($listId);

        if (! $this->authorizeListAccess($list)) {
            return;
        }

        $this->editingListId = $listId;
        $this->editListName = $list->name;
        $this->showEditList = true;
    }

    public function updateList(): void
    {
        $list = TaskList::findOrFail($this->editingListId);

        if (! $this->authorizeListAccess($list)) {
            return;
        }

        $this->validate(['editListName' => 'required|min:2|max:100|unique:task_lists,name,'.$this->editingListId.',id,space_id,'.$list->space_id], [
            'editListName.required' => 'Nama list wajib diisi.',
            'editListName.min' => 'Nama list minimal 2 karakter.',
            'editListName.max' => 'Nama list maksimal 100 karakter.',
            'editListName.unique' => 'Nama list sudah digunakan di space ini.',
        ]);

        $list->update(['name' => $this->editListName]);

        $this->reset(['editingListId', 'editListName', 'showEditList']);
        $this->dispatch('sidebar-updated');
        SpaceUpdated::dispatch($list->space->workspace_id, auth()->id());
        Flux::toast('List berhasil diperbarui.', variant: 'success');
    }

    public function openManageMembers(int $listId): void
    {
        $list = TaskList::with('members')->findOrFail($listId);

        if (! $this->authorizeListAccess($list)) {
            return;
        }

        $this->managingListId = $listId;
        $this->listMemberIds = $list->members->pluck('id')->toArray();
        $this->showManageMembers = true;
    }

    public function saveMembers(): void
    {
        $list = TaskList::with(['members', 'tasks', 'space'])->findOrFail($this->managingListId);

        if (! $this->authorizeListAccess($list)) {
            return;
        }

        $removedIds = array_diff($list->members->pluck('id')->toArray(), $this->listMemberIds);

        $list->members()->sync($this->listMemberIds);

        if (! empty($removedIds)) {
            foreach ($list->tasks as $task) {
                $task->assignees()->detach($removedIds);
            }
        }

        $this->reset(['managingListId', 'listMemberIds', 'showManageMembers']);
        SpaceUpdated::dispatch($list->space->workspace_id, auth()->id());
        Flux::toast('Anggota list berhasil diperbarui.', variant: 'success');
    }

    #[Computed]
    public function allUsers()
    {
        return User::orderBy('name')->get();
    }

    public function render()
    {
        $lists = TaskList::with(['space', 'folder', 'members'])
            ->withCount('tasks')
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('space_id')
            ->orderBy('position')
            ->get();

        $grouped = $lists->groupBy(fn (TaskList $list) => $list->space->name ?? 'Tanpa Space');

        return view('livewire.project.general-taskboard', [
            'grouped' => $grouped,
            'totalLists' => $lists->count(),
        ]);
    }
}
