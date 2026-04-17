<?php

namespace App\Livewire\Project;

use App\Models\Project\Space;
use App\Models\Project\Workspace;
use Livewire\Component;

class SidebarSpaces extends Component
{
    public ?int $workspaceId = null;

    public function mount(): void
    {
        $this->workspaceId = Workspace::where('owner_id', auth()->id())->value('id');
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        $listeners = [
            'sidebar-updated' => 'refresh',
        ];

        if ($this->workspaceId) {
            $listeners["echo:workspace.{$this->workspaceId},SpaceUpdated"] = 'onBroadcastUpdate';
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

    public function refresh(): void
    {
        // Re-renders the component automatically
    }

    public function render()
    {
        $userId = auth()->id();

        $spaces = Space::accessibleBy($userId)
            ->with([
                'lists' => fn ($q) => $q->accessibleBy($userId)->orderBy('position'),
            ])
            ->get();

        return view('livewire.project.sidebar-spaces', [
            'sidebarSpaces' => $spaces,
        ]);
    }
}
