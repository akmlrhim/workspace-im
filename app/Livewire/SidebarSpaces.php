<?php

namespace App\Livewire;

use App\Models\Space;
use App\Models\Workspace;
use Livewire\Component;

class SidebarSpaces extends Component
{
    public ?int $workspaceId = null;

    public function mount(): void
    {
        $this->workspaceId = Workspace::whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('id')
            ->value('id');
    }

    /**
     * @return array<string, string>
     */
    public function getListeners(): array
    {
        $listeners = [
            'sidebar-updated' => 'refresh',
        ];

        if ($this->workspaceId) {
            $listeners["echo:workspace.{$this->workspaceId},SpaceUpdated"] = 'onBroadcastUpdate';
            $listeners["echo:workspace.{$this->workspaceId},TaskListUpdated"] = 'onBroadcastUpdate';
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

    public function refresh(): void {}

    public function render()
    {
        $userId = auth()->id();

        $spaces = Space::accessibleBy($userId)
            ->with([
                'lists' => fn ($q) => $q->accessibleBy($userId)->orderBy('position'),
            ])
            ->get();

        return view('livewire.sidebar-spaces', [
            'sidebarSpaces' => $spaces,
        ]);
    }
}
