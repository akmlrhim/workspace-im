<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use Flux\Flux;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Project — Spaces')]
class SpaceIndex extends Component
{
    public ?Workspace $workspace = null;

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

        $this->workspace = Workspace::where('owner_id', $user->id)->first();

        if (! $this->workspace) {
            $this->workspace = Workspace::create([
                'name' => $user->name."'s Workspace",
                'owner_id' => $user->id,
            ]);

            WorkspaceMember::create([
                'workspace_id' => $this->workspace->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);
        }
    }

    public function createSpace(): void
    {
        $this->validate([
            'spaceName' => 'required|min:2|max:100|unique:spaces,name,NULL,id,workspace_id,'.$this->workspace->id,
            'spaceColor' => 'required|string',
        ]);

        $maxPosition = $this->workspace->spaces()->max('position') ?? -1;

        $this->workspace->spaces()->create([
            'name' => $this->spaceName,
            'color' => $this->spaceColor,
            'icon' => $this->spaceIcon,
            'position' => $maxPosition + 1,
        ]);

        $this->reset(['spaceName', 'spaceColor', 'spaceIcon', 'showCreateSpace']);
        $this->spaceColor = '#6366f1';

        Flux::toast(__('messages.space_created'), variant: 'success');
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
        $this->validate([
            'editSpaceName' => 'required|min:2|max:100|unique:spaces,name,'.$this->editingSpaceId.',id,workspace_id,'.$this->workspace->id,
            'editSpaceColor' => 'required|string',
        ]);

        $space = Space::findOrFail($this->editingSpaceId);
        $space->update([
            'name' => $this->editSpaceName,
            'color' => $this->editSpaceColor,
        ]);

        $this->reset(['editingSpaceId', 'editSpaceName', 'editSpaceColor', 'showEditSpace']);

        Flux::toast(__('messages.space_updated'), variant: 'success');
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

        Flux::toast(__('messages.space_deleted'), variant: 'danger');
    }

    public function getAvailableColorsProperty(): array
    {
        return $this->colors;
    }

    public function render()
    {
        $spaces = $this->workspace->spaces()->withCount(['lists', 'folders'])->get();

        return view('livewire.project.space-index', [
            'spaces' => $spaces,
        ]);
    }
}
