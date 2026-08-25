<?php

namespace App\Livewire\Concerns;

use App\Livewire\Forms\SpaceForm;
use App\Models\Space;
use App\Models\Workspace;
use Flux\Flux;

trait ManagesSpaces
{
    public bool $showCreateSpace = false;

    public SpaceForm $createSpaceForm;

    public bool $showEditSpace = false;

    public ?int $editingSpaceId = null;

    public string $editSpaceName = '';

    public string $editSpaceColor = '#6366f1';

    public string $editSpaceIcon = 'folder';

    public function createSpace(): void
    {
        $this->createSpaceForm->validate();

        $workspace = Workspace::findOrFail($this->workspaceId);

        $workspace->spaces()->create([
            'name' => trim($this->createSpaceForm->name),
            'color' => $this->createSpaceForm->color,
            'icon' => $this->createSpaceForm->icon,
            'position' => (Space::max('position') ?? -1) + 1,
        ]);

        unset($this->listSpaces, $this->spaces);
        $this->createSpaceForm->reset();
        $this->showCreateSpace = false;
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('Space berhasil dibuat.', variant: 'success');
    }

    public function updateSpace(): void
    {
        if (! $this->authorizeSpaceManagement()) {
            return;
        }

        if (! $this->editingSpaceId) {
            return;
        }

        $space = Space::findOrFail($this->editingSpaceId);

        $this->validate([
            'editSpaceName' => 'required|min:2|max:100|unique:spaces,name,'.$this->editingSpaceId,
            'editSpaceColor' => 'required|string',
        ], [
            'editSpaceName.required' => 'Nama space wajib diisi.',
            'editSpaceName.min' => 'Nama space minimal 2 karakter.',
            'editSpaceName.max' => 'Nama space maksimal 100 karakter.',
            'editSpaceName.unique' => 'Nama space sudah digunakan.',
        ]);

        $space->update([
            'name' => trim($this->editSpaceName),
            'color' => $this->editSpaceColor,
            'icon' => $this->editSpaceIcon,
        ]);

        unset($this->listSpaces, $this->spaces);
        $this->reset(['editingSpaceId', 'editSpaceName', 'showEditSpace']);
        $this->editSpaceColor = '#6366f1';
        $this->editSpaceIcon = 'folder';
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('Space berhasil diperbarui.', variant: 'success');
    }

    public function deleteSpace(int $spaceId): void
    {
        if (! $this->authorizeSpaceManagement()) {
            return;
        }

        Space::accessibleBy(auth()->id())->findOrFail($spaceId)->delete();

        unset($this->listSpaces, $this->spaces);
        $this->skipRender();
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('Space berhasil dihapus.', variant: 'success');
    }

    public function updatedShowCreateSpace(bool $value): void
    {
        if (! $value) {
            $this->createSpaceForm->reset();
            $this->resetValidation();
        }
    }

    private function authorizeSpaceManagement(): bool
    {
        if (auth()->user()->canManageLists()) {
            return true;
        }

        Flux::toast('Hanya Administrator yang dapat mengelola space.', variant: 'danger');

        return false;
    }
}
