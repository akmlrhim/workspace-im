<?php

namespace App\Livewire\Project;

use App\Events\SpaceUpdated;
use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskList;
use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use App\Models\User;
use Carbon\Carbon;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('General Taskboard')]
class GeneralTaskboard extends Component
{
    public string $search = '';

    public ?int $workspaceId = null;

    public string $activeTab = 'lists';

    // ─── Space creation ────────────────────────────────────────
    public bool $showCreateSpace = false;

    public string $spaceName = '';

    public string $spaceColor = '#6366f1';

    public string $spaceIcon = 'folder';

    // ─── List creation ─────────────────────────────────────────
    public bool $showCreateList = false;

    public string $listName = '';

    public ?int $listSpaceId = null;

    // ─── Edit list ─────────────────────────────────────────────
    public bool $showEditList = false;

    public ?int $editingListId = null;

    public string $editListName = '';

    // ─── Manage list members ───────────────────────────────────
    public bool $showManageMembers = false;

    public ?int $managingListId = null;

    public array $listMemberIds = [];

    // ─── Calendar ──────────────────────────────────────────────
    public int $calYear;

    public int $calMonth;

    /** @var array<int> Empty = semua space ditampilkan */
    public array $calSelectedSpaceIds = [];

    // ─── Task detail (calendar) ────────────────────────────────
    public ?int $selectedTaskId = null;

    public bool $showTaskDetail = false;

    public function mount(): void
    {
        $user = auth()->user();

        $workspace = Workspace::where('owner_id', $user->id)->first();

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
    }

    private function broadcastChange(): void
    {
        SpaceUpdated::dispatch($this->workspaceId, auth()->id());
    }

    // ─── Space ─────────────────────────────────────────────────

    public function createSpace(): void
    {
        $workspace = Workspace::findOrFail($this->workspaceId);

        $this->validate([
            'spaceName' => 'required|min:2|max:100|unique:spaces,name,NULL,id,workspace_id,'.$workspace->id,
            'spaceColor' => 'required|string',
        ], [
            'spaceName.required' => 'Nama space wajib diisi.',
            'spaceName.min' => 'Nama space minimal 2 karakter.',
            'spaceName.max' => 'Nama space maksimal 100 karakter.',
            'spaceName.unique' => 'Nama space sudah digunakan.',
        ]);

        $workspace->spaces()->create([
            'name' => trim($this->spaceName),
            'color' => $this->spaceColor,
            'icon' => $this->spaceIcon,
            'position' => ($workspace->spaces()->max('position') ?? -1) + 1,
        ]);

        $this->reset(['spaceName', 'showCreateSpace']);
        $this->spaceColor = '#6366f1';
        $this->spaceIcon = 'folder';
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('Space berhasil dibuat.', variant: 'success');
    }

    // ─── List ──────────────────────────────────────────────────

    public function openCreateList(?int $spaceId = null): void
    {
        $this->listSpaceId = $spaceId;
        $this->listName = '';
        $this->showCreateList = true;
    }

    public function createList(): void
    {
        $this->validate([
            'listName' => 'required|min:2|max:100',
            'listSpaceId' => 'required|exists:spaces,id',
        ], [
            'listName.required' => 'Nama list wajib diisi.',
            'listName.min' => 'Nama list minimal 2 karakter.',
            'listSpaceId.required' => 'Pilih space terlebih dahulu.',
        ]);

        $space = Space::findOrFail($this->listSpaceId);

        $list = $space->lists()->create([
            'name' => trim($this->listName),
            'position' => ($space->lists()->max('position') ?? -1) + 1,
        ]);

        $list->createDefaultStatuses();
        $list->members()->attach(auth()->id());

        $this->reset(['listName', 'listSpaceId', 'showCreateList']);
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('List berhasil dibuat.', variant: 'success');
    }

    // ─── Edit list ─────────────────────────────────────────────

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
        $this->showEditList = true;
    }

    public function updateList(): void
    {
        if (! $this->authorizeListManagement()) {
            return;
        }

        $list = TaskList::findOrFail($this->editingListId);
        $this->validate(
            ['editListName' => 'required|min:2|max:100|unique:task_lists,name,'.$this->editingListId.',id,space_id,'.$list->space_id],
            ['editListName.required' => 'Nama list wajib diisi.', 'editListName.unique' => 'Nama list sudah digunakan di space ini.']
        );

        $list->update(['name' => $this->editListName]);
        $this->reset(['editingListId', 'editListName', 'showEditList']);
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('List berhasil diperbarui.', variant: 'success');
    }

    // ─── Members ───────────────────────────────────────────────

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

        $list = TaskList::with(['members', 'tasks', 'space'])->findOrFail($this->managingListId);

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

    // ─── Calendar ──────────────────────────────────────────────

    public function calPrevMonth(): void
    {
        $date = Carbon::create($this->calYear, $this->calMonth, 1)->subMonth();
        $this->calYear = $date->year;
        $this->calMonth = $date->month;
    }

    public function calNextMonth(): void
    {
        $date = Carbon::create($this->calYear, $this->calMonth, 1)->addMonth();
        $this->calYear = $date->year;
        $this->calMonth = $date->month;
    }

    public function calToday(): void
    {
        $this->calYear = now()->year;
        $this->calMonth = now()->month;
    }

    public function toggleCalSpace(int $spaceId): void
    {
        if (in_array($spaceId, $this->calSelectedSpaceIds)) {
            $this->calSelectedSpaceIds = array_values(
                array_filter($this->calSelectedSpaceIds, fn ($id) => $id !== $spaceId)
            );
        } else {
            $this->calSelectedSpaceIds[] = $spaceId;
        }
    }

    public function calSelectAll(): void
    {
        $this->calSelectedSpaceIds = [];
    }

    public function openTaskDetail(int $taskId): void
    {
        $this->selectedTaskId = $taskId;
        $this->showTaskDetail = true;
    }

    public function onTaskDeleted(int $taskId): void
    {
        if ($this->selectedTaskId === $taskId) {
            $this->selectedTaskId = null;
        }
        $this->showTaskDetail = false;
    }

    public function closeTaskDetail(): void
    {
        $this->showTaskDetail = false;
    }

    // ─── Computed ──────────────────────────────────────────────

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
        $lists = TaskList::with(['space', 'folder', 'members'])
            ->withCount(['tasks' => fn ($q) => $q->excludeNotes()])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('space_id')
            ->orderBy('position')
            ->get();

        $grouped = $lists->groupBy(fn (TaskList $list) => $list->space->name ?? 'Tanpa Space');

        // Calendar data
        $monthStart = Carbon::create($this->calYear, $this->calMonth, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();

        $calTasks = Task::with(['status', 'taskList.space', 'assignees'])
            ->whereNull('parent_id')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$monthStart, $monthEnd])
            ->whereHas('taskList.space', function ($q) {
                $q->accessibleBy(auth()->id());
                if (! empty($this->calSelectedSpaceIds)) {
                    $q->whereIn('id', $this->calSelectedSpaceIds);
                }
            })
            ->orderBy('due_date')
            ->get()
            ->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));

        $calStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];
        $cur = $calStart->copy();

        while ($cur <= $calEnd) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $ds = $cur->format('Y-m-d');
                $week[] = [
                    'date' => $cur->copy(),
                    'isCurrentMonth' => $cur->month === $this->calMonth,
                    'isToday' => $cur->isToday(),
                    'tasks' => $calTasks->get($ds, collect()),
                ];
                $cur->addDay();
            }
            $weeks[] = $week;
        }

        return view('livewire.project.general-taskboard', [
            'grouped' => $grouped,
            'totalLists' => $lists->count(),
            'weeks' => $weeks,
            'calMonthLabel' => $monthStart->isoFormat('MMMM Y'),
        ]);
    }
}
