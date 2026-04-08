<?php

namespace App\Livewire\Project;

use App\Models\Project\Folder;
use App\Models\Project\Space;
use App\Models\Project\TaskList;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Flux\Flux;

#[Layout('layouts.app')]
class SpaceShow extends Component
{
	public Space $space;

	public bool $showCreateList = false;
	public string $listName = '';
	public ?int $listFolderId = null;

	public bool $showCreateFolder = false;
	public string $folderName = '';

	public bool $showEditList = false;
	public ?int $editingListId = null;
	public string $editListName = '';

	public bool $showEditFolder = false;
	public ?int $editingFolderId = null;
	public string $editFolderName = '';

	public bool $showDeleteListConfirm = false;
	public ?int $deletingListId = null;

	public bool $showDeleteFolderConfirm = false;
	public ?int $deletingFolderId = null;

	public function mount(Space $space): void
	{
		$this->space = $space;
	}

	public function getTitle(): string
	{
		return $this->space->name . ' — Space';
	}

	public function createList(): void
	{
		$this->validate(['listName' => 'required|min:2|max:100']);

		$maxPosition = $this->space->lists()->max('position') ?? -1;

		$list = $this->space->lists()->create([
			'name' => $this->listName,
			'folder_id' => $this->listFolderId,
			'position' => $maxPosition + 1,
		]);

		$list->createDefaultStatuses();

		$this->reset(['listName', 'listFolderId', 'showCreateList']);
		Flux::toast(__('messages.list_created'), variant: 'success');
	}

	public function createFolder(): void
	{
		$this->validate(['folderName' => 'required|min:2|max:100']);

		$maxPosition = $this->space->folders()->max('position') ?? -1;

		$this->space->folders()->create([
			'name' => $this->folderName,
			'position' => $maxPosition + 1,
		]);

		$this->reset(['folderName', 'showCreateFolder']);
		Flux::toast(__('messages.folder_created'), variant: 'success');
	}

	public function openEditList(int $listId): void
	{
		$list = TaskList::findOrFail($listId);
		$this->editingListId = $listId;
		$this->editListName = $list->name;
		$this->showEditList = true;
	}

	public function updateList(): void
	{
		$this->validate(['editListName' => 'required|min:2|max:100']);

		TaskList::findOrFail($this->editingListId)->update([
			'name' => $this->editListName,
		]);

		$this->reset(['editingListId', 'editListName', 'showEditList']);
		Flux::toast(__('messages.list_updated'), variant: 'success');
	}

	public function openEditFolder(int $folderId): void
	{
		$folder = Folder::findOrFail($folderId);
		$this->editingFolderId = $folderId;
		$this->editFolderName = $folder->name;
		$this->showEditFolder = true;
	}

	public function updateFolder(): void
	{
		$this->validate(['editFolderName' => 'required|min:2|max:100']);

		Folder::findOrFail($this->editingFolderId)->update([
			'name' => $this->editFolderName,
		]);

		$this->reset(['editingFolderId', 'editFolderName', 'showEditFolder']);
		Flux::toast(__('messages.folder_updated'), variant: 'success');
	}

	public function confirmDeleteList(int $listId): void
	{
		$this->deletingListId = $listId;
		$this->showDeleteListConfirm = true;
	}

	public function deleteList(): void
	{
		if ($this->deletingListId) {
			TaskList::findOrFail($this->deletingListId)->delete();
		}
		$this->reset(['deletingListId', 'showDeleteListConfirm']);
		Flux::toast(__('messages.list_deleted'), variant: 'danger');
	}

	public function confirmDeleteFolder(int $folderId): void
	{
		$this->deletingFolderId = $folderId;
		$this->showDeleteFolderConfirm = true;
	}

	public function deleteFolder(): void
	{
		if ($this->deletingFolderId) {
			Folder::findOrFail($this->deletingFolderId)->delete();
		}
		$this->reset(['deletingFolderId', 'showDeleteFolderConfirm']);
		Flux::toast(__('messages.folder_deleted'), variant: 'danger');
	}

	public function render()
	{
		$folders = $this->space->folders()->with(['lists' => function ($q) {
			$q->withCount('tasks');
		}])->get();

		$listsWithoutFolder = $this->space->listsWithoutFolder()->withCount('tasks')->get();

		return view('livewire.project.space-show', [
			'folders' => $folders,
			'listsWithoutFolder' => $listsWithoutFolder,
		]);
	}
}
