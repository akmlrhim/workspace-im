<?php

namespace App\Livewire\Project;

use App\Events\SpaceUpdated;
use App\Models\Project\Space;
use App\Models\Project\TaskList;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SpaceShow extends Component
{
	public Space $space;

	public bool $showCreateList = false;

	public string $listName = '';

	public bool $showEditList = false;

	public ?int $editingListId = null;

	public string $editListName = '';

	public ?int $editListSpaceId = null;

	public bool $showDeleteListConfirm = false;

	public ?int $deletingListId = null;

	// List member management
	public bool $showManageMembers = false;

	public ?int $managingListId = null;

	public array $listMemberIds = [];

	public function mount(Space $space): void
	{
		$this->space = $space;
	}

	/** @return array<string, string> */
	public function getListeners(): array
	{
		return [
			"echo:workspace.{$this->space->workspace_id},SpaceUpdated" => 'onBroadcastUpdate',
		];
	}

	public function onBroadcastUpdate(array $event): void
	{
		if (($event['triggeredBy'] ?? null) == auth()->id()) {
			$this->skipRender();

			return;
		}
	}

	private function broadcastChange(): void
	{
		SpaceUpdated::dispatch($this->space->workspace_id, auth()->id());
	}

	public function getTitle(): string
	{
		return $this->space->name . ' Space';
	}

	public function openCreateList(): void
	{
		$this->listName = '';
		$this->showCreateList = true;
	}

	public function createList(): void
	{
		$this->validate(['listName' => 'required|min:2|max:100|unique:task_lists,name,NULL,id,space_id,' . $this->space->id], [
			'listName.required' => 'Nama list wajib diisi.',
			'listName.min' => 'Nama list minimal 2 karakter.',
			'listName.max' => 'Nama list maksimal 100 karakter.',
			'listName.unique' => 'Nama list sudah digunakan di space ini.',
		]);

		$maxPosition = $this->space->lists()->max('position') ?? -1;

		$list = $this->space->lists()->create([
			'name' => trim($this->listName),
			'position' => $maxPosition + 1,
		]);

		$list->createDefaultStatuses();
		$list->members()->attach(auth()->id());

		$this->reset(['listName', 'showCreateList']);
		$this->dispatch('sidebar-updated');
		$this->broadcastChange();
		Flux::toast('List berhasil dibuat.', variant: 'success');
	}

	private function authorizeListManagement(): bool
	{
		if (auth()->user()->canManageLists()) {
			return true;
		}

		Flux::toast('Hanya Administrator yang dapat mengelola list.', variant: 'danger');

		return false;
	}

	public function openEditList(int $listId): void
	{
		if (! $this->authorizeListManagement()) {
			return;
		}

		$list = TaskList::findOrFail($listId);
		$this->editingListId = $listId;
		$this->editListName = $list->name;
		$this->editListSpaceId = $list->space_id;
		$this->showEditList = true;
	}

	public function updateList(): void
	{
		if (! $this->authorizeListManagement()) {
			return;
		}

		$this->validate([
			'editListName' => 'required|min:2|max:100|unique:task_lists,name,' . $this->editingListId . ',id,space_id,' . $this->editListSpaceId,
			'editListSpaceId' => 'required|exists:spaces,id',
		], [
			'editListName.required' => 'Nama list wajib diisi.',
			'editListName.unique' => 'Nama list sudah digunakan di space tujuan.',
			'editListSpaceId.required' => 'Pilih space tujuan terlebih dahulu.',
		]);

		$list = TaskList::findOrFail($this->editingListId);
		$targetSpace = Space::accessibleBy(auth()->id())->whereKey($this->editListSpaceId)->firstOrFail();
		$updates = [
			'name' => trim($this->editListName),
			'space_id' => $targetSpace->id,
		];

		if ($list->space_id !== $targetSpace->id) {
			$updates['position'] = ($targetSpace->lists()->max('position') ?? -1) + 1;
		}

		$list->update($updates);
		$this->reset(['editingListId', 'editListName', 'editListSpaceId', 'showEditList']);
		$this->dispatch('sidebar-updated');
		$this->broadcastChange();
		Flux::toast('List berhasil diperbarui.', variant: 'success');
	}

	public function confirmDeleteList(int $listId): void
	{
		if (! $this->authorizeListManagement()) {
			return;
		}

		$this->deletingListId = $listId;
		$this->showDeleteListConfirm = true;
	}

	public function deleteList(): void
	{
		if (! $this->authorizeListManagement()) {
			return;
		}

		if ($this->deletingListId) {
			TaskList::findOrFail($this->deletingListId)->delete();
		}
		$this->reset(['deletingListId', 'showDeleteListConfirm']);
		$this->dispatch('sidebar-updated');
		$this->broadcastChange();
		Flux::toast('List berhasil dihapus.', variant: 'danger');
	}

	// List member management
	public function openManageMembers(int $listId): void
	{
		if (! $this->authorizeListManagement()) {
			return;
		}

		$list = TaskList::with('members')->findOrFail($listId);
		$this->managingListId = $listId;
		$this->listMemberIds = $list->members->pluck('id')->toArray();
		$this->showManageMembers = true;
	}

	public function saveMembers(): void
	{
		if (! $this->authorizeListManagement()) {
			return;
		}

		$list = TaskList::with(['members', 'tasks'])->findOrFail($this->managingListId);

		$removedIds = array_diff($list->members->pluck('id')->toArray(), $this->listMemberIds);

		$list->members()->sync($this->listMemberIds);

		if (! empty($removedIds)) {
			foreach ($list->tasks as $task) {
				$task->assignees()->detach($removedIds);
			}
		}

		$this->reset(['managingListId', 'listMemberIds', 'showManageMembers']);
		$this->broadcastChange();
		Flux::toast('Anggota list berhasil diperbarui.', variant: 'success');
	}

	#[Computed]
	public function lists()
	{
		return $this->space->lists()
			->withCount(['tasks' => fn($q) => $q->excludeNotes()])
			->with('members')
			->get();
	}

	#[Computed]
	public function spaces()
	{
		return Space::accessibleBy(auth()->id())
			->orderBy('position')
			->get();
	}

	#[Computed]
	public function allUsers()
	{
		return User::orderBy('name')->get();
	}

	public function render()
	{
		return view('livewire.project.space-show');
	}
}
