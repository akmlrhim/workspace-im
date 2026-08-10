<?php

namespace App\Livewire\Project;

use App\Events\SpaceUpdated;
use App\Livewire\Project\Concerns\BroadcastsChangesSafely;
use App\Livewire\Project\Concerns\BuildsTaskboardCalendar;
use App\Livewire\Project\Concerns\ManagesListMembers;
use App\Livewire\Project\Concerns\ManagesSpaces;
use App\Livewire\Project\Concerns\ManagesTaskLists;
use App\Livewire\Project\Concerns\OpensTaskDetailPanel;
use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskList;
use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('General Taskboard')]
class GeneralTaskboard extends Component
{
    use BroadcastsChangesSafely;
    use BuildsTaskboardCalendar;
    use ManagesListMembers;
    use ManagesSpaces;
    use ManagesTaskLists;
    use OpensTaskDetailPanel;

    public string $search = '';

    /** Global task search across all accessible spaces */
    public string $globalSearch = '';

    public ?int $workspaceId = null;

    public string $activeTab = 'lists';

    public function mount(): void
    {
        $user = auth()->user();

        $workspace = Workspace::whereHas('members', fn ($q) => $q->where('user_id', $user->id))
            ->orderBy('id')
            ->first();

        if (! $workspace) {
            $workspace = Workspace::create([
                'name' => $user->name."'s Workspace",
                'owner_id' => $user->id,
            ]);

            WorkspaceMember::create([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);
        }

        $this->workspaceId = $workspace->id;
        $this->calYear = now()->year;
        $this->calMonth = now()->month;
        $this->calSelectedSpaceId = Space::accessibleBy($user->id)->orderBy('position')->value('id');
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        $listeners = [
            'close-task-detail' => 'closeTaskDetail',
            'task-updated' => '$refresh',
            'task-deleted' => 'onTaskDeleted',
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

        unset($this->listSpaces, $this->calendarData, $this->spaces);
    }

    private function broadcastChange(): void
    {
        $this->broadcastSafely(fn () => SpaceUpdated::dispatch($this->workspaceId, auth()->id()));
    }

    // ─── Computed ──────────────────────────────────────────────

    /** Spaces with lists — used by the "lists" tab only. */
    #[Computed]
    public function listSpaces()
    {
        return Space::accessibleBy(auth()->id())
            ->with([
                'lists' => function ($q) {
                    $q->accessibleBy(auth()->id())
                        ->with('members')
                        ->withCount(['tasks' => fn ($q2) => $q2->excludeNotes()])
                        ->when($this->search, fn ($q2) => $q2->where('name', 'like', "%{$this->search}%"))
                        ->orderBy('position');
                },
            ])
            ->orderBy('position')
            ->get();
    }

    /** Bare spaces (no lists) — used by the calendar space-filter pills. */
    #[Computed]
    public function spaces()
    {
        return Space::accessibleBy(auth()->id())
            ->orderBy('position')
            ->get();
    }

    /**
     * Global task title suggestions while typing, scoped to lists the user can access.
     */
    #[Computed]
    public function globalSearchSuggestions()
    {
        $term = trim($this->globalSearch);

        if (mb_strlen($term) < 2) {
            return collect();
        }

        return Task::query()
            ->whereNull('parent_id')
            ->excludeNotes()
            ->whereHas('taskList', fn ($q) => $q->accessibleBy(auth()->id()))
            ->where('title', 'like', '%'.addcslashes($term, '\\%_').'%')
            ->with(['status:id,name,color', 'taskList:id,space_id,name', 'taskList.space:id,name,color'])
            ->orderBy('title')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        if ($this->activeTab === 'lists') {
            $spaces = $this->listSpaces;

            return view('livewire.project.general-taskboard', [
                'spaces' => $spaces,
                'totalLists' => $spaces->sum(fn ($sp) => $sp->lists->count()),
                'weeks' => [],
                'calMonthLabel' => '',
            ]);
        }

        $cal = $this->calendarData;

        return view('livewire.project.general-taskboard', [
            'spaces' => collect(),
            'totalLists' => TaskList::count(),
            'weeks' => $cal['weeks'],
            'calMonthLabel' => $cal['label'],
        ]);
    }
}
