<?php

namespace App\Livewire\Project;

use App\Models\Project\TaskList;
use App\Models\Project\Workspace;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Taskboard')]
class GeneralTaskboard extends Component
{
    public string $search = '';

    public ?int $workspaceId = null;

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
