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
        // Match the same lookup as GeneralTaskboard — prefer the oldest workspace
        // the user belongs to as a member (not necessarily the one they own).
        $this->workspaceId = Workspace::whereHas('members', fn ($q) => $q->where('user_id', auth()->id()))
            ->orderBy('id')
            ->value('id');
    }

    /** @return array<string, string> */
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

        // Re-render so sidebar reflects the new space/list for other users
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
