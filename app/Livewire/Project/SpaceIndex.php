<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Project — Spaces')]
class SpaceIndex extends Component
{
	public ?int $workspaceId = null;

	// Create space form
	public bool $showCreateSpace = false;

	public string $spaceName = '';

	public string $spaceColor = '#6366f1';

	public string $spaceIcon = 'folder';

	// Edit space form
	public bool $showEditSpace = false;

	public ?int $editingSpaceId = null;

	public string $editSpaceName = '';

	public string $editSpaceColor = '#6366f1';

	public bool $showDeleteConfirm = false;

	public ?int $deletingSpaceId = null;

	protected $colors = [
		'#6366f1',
		'#8b5cf6',
		'#ec4899',
		'#ef4444',
		'#f97316',
		'#f59e0b',
		'#10b981',
		'#14b8a6',
		'#06b6d4',
		'#3b82f6',
	];

	public function mount(): void
	{
		$user = auth()->user();

		$workspace = Workspace::where('owner_id', $user->id)->first();

		if (! $workspace) {
			$workspace = Workspace::create([
				'name' => $user->name . "'s Workspace",
				'owner_id' => $user->id,
			]);

			WorkspaceMember::create([
				'workspace_id' => $workspace->id,
				'user_id' => $user->id,
				'role' => 'owner',
			]);
		}

		$this->workspaceId = $workspace->id;
	}

	private function getWorkspace(): Workspace
	{
		return Workspace::findOrFail($this->workspaceId);
	}

	public function createSpace(): void
	{
		$workspace = $this->getWorkspace();

		$this->validate([
			'spaceName' => 'required|min:2|max:100|unique:spaces,name,NULL,id,workspace_id,' . $workspace->id,
			'spaceColor' => 'required|string',
		], [
			'spaceName.required' => 'Nama space wajib diisi.',
			'spaceName.min' => 'Nama space minimal 2 karakter.',
			'spaceName.max' => 'Nama space maksimal 100 karakter.',
			'spaceName.unique' => 'Nama space sudah digunakan.',
			'spaceColor.required' => 'Warna wajib dipilih.',
		]);

		$maxPosition = $workspace->spaces()->max('position') ?? -1;

		$workspace->spaces()->create([
			'name' => $this->spaceName,
			'color' => $this->spaceColor,
			'icon' => $this->spaceIcon,
			'position' => $maxPosition + 1,
		]);

		$this->reset(['spaceName', 'spaceColor', 'spaceIcon', 'showCreateSpace']);
		$this->spaceColor = '#6366f1';

		Flux::toast('Space berhasil dibuat.', variant: 'success');
	}

	public function editSpace(int $spaceId): void
	{
		$space = Space::findOrFail($spaceId);
		$this->editingSpaceId = $space->id;
		$this->editSpaceName = $space->name;
		$this->editSpaceColor = $space->color;
		$this->showEditSpace = true;
	}

	public function updateSpace(): void
	{
		$workspace = $this->getWorkspace();

		$this->validate([
			'editSpaceName' => 'required|min:2|max:100|unique:spaces,name,' . $this->editingSpaceId . ',id,workspace_id,' . $workspace->id,
			'editSpaceColor' => 'required|string',
		], [
			'editSpaceName.required' => 'Nama space wajib diisi.',
			'editSpaceName.min' => 'Nama space minimal 2 karakter.',
			'editSpaceName.max' => 'Nama space maksimal 100 karakter.',
			'editSpaceName.unique' => 'Nama space sudah digunakan.',
			'editSpaceColor.required' => 'Warna wajib dipilih.',
		]);

		$space = Space::findOrFail($this->editingSpaceId);
		$space->update([
			'name' => $this->editSpaceName,
			'color' => $this->editSpaceColor,
		]);

		$this->reset(['editingSpaceId', 'editSpaceName', 'editSpaceColor', 'showEditSpace']);

		Flux::toast('Space berhasil diperbarui.', variant: 'success');
	}

	public function confirmDelete(int $spaceId): void
	{
		$this->deletingSpaceId = $spaceId;
		$this->showDeleteConfirm = true;
	}

	public function deleteSpace(): void
	{
		if ($this->deletingSpaceId) {
			Space::findOrFail($this->deletingSpaceId)->delete();
		}
		$this->reset(['deletingSpaceId', 'showDeleteConfirm']);

		Flux::toast('Space berhasil dihapus.', variant: 'danger');
	}

	public function getAvailableColorsProperty(): array
	{
		return $this->colors;
	}

	public function render()
	{
		/** @var User $user */
		$user = auth()->user();

		$spaces = Space::accessibleBy($user->id)
			->withCount(['lists', 'folders'])
			->with('workspace')
			->orderBy('position')
			->get();

		return view('livewire.project.space-index', [
			'spaces' => $spaces,
		]);
	}
}
