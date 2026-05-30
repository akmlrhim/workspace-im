<?php

namespace App\Livewire\Project;

use App\Events\SpaceUpdated;
use App\Livewire\Forms\SpaceForm;
use App\Livewire\Forms\TaskListForm;
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

    public SpaceForm $createSpaceForm;

    // ─── List creation ─────────────────────────────────────────
    public TaskListForm $createListForm;

    // ─── Edit space ────────────────────────────────────────────
    public bool $showEditSpace = false;

    public ?int $editingSpaceId = null;

    public string $editSpaceName = '';

    public string $editSpaceColor = '#6366f1';

    public string $editSpaceIcon = 'folder';

    // ─── Edit list ─────────────────────────────────────────────
    public bool $showEditList = false;

    public ?int $editingListId = null;

    public string $editListName = '';

    public ?int $editListSpaceId = null;

    // ─── Manage list members ───────────────────────────────────
    public bool $showManageMembers = false;

    public ?int $managingListId = null;

    public array $listMemberIds = [];

    // ─── Calendar ──────────────────────────────────────────────
    public int $calYear;

    public int $calMonth;

    /** Null = semua space ditampilkan */
    public ?int $calSelectedSpaceId = null;

    // ─── Task detail (calendar) ────────────────────────────────
    public ?int $selectedTaskId = null;

    public bool $showTaskDetail = false;

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
        SpaceUpdated::dispatch($this->workspaceId, auth()->id());
    }

    // ─── Space ─────────────────────────────────────────────────

    public function updateSpace(): void
    {
        if (! auth()->user()->canManageLists()) {
            Flux::toast('Hanya Administrator yang dapat mengelola space.', variant: 'danger');

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
        if (! auth()->user()->canManageLists()) {
            Flux::toast('Hanya Administrator yang dapat mengelola space.', variant: 'danger');

            return;
        }

        Space::accessibleBy(auth()->id())->findOrFail($spaceId)->delete();

        unset($this->listSpaces, $this->spaces);
        $this->skipRender();
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('Space berhasil dihapus.', variant: 'success');
    }

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

    public function resetCreateListForm(): void
    {
        $this->createListForm->reset();
        $this->resetValidation();
    }

    public function updatedShowCreateSpace(bool $value): void
    {
        if (! $value) {
            $this->createSpaceForm->reset();
            $this->resetValidation();
        }
    }

    // ─── List ──────────────────────────────────────────────────

    public function deleteList(int $listId): void
    {
        if (! auth()->user()->canManageLists()) {
            Flux::toast('Hanya Administrator yang dapat menghapus list.', variant: 'danger');

            return;
        }

        TaskList::findOrFail($listId)->delete();

        unset($this->listSpaces);
        $this->skipRender();
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('List berhasil dihapus.', variant: 'success');
    }

    public function createList(): void
    {
        $this->createListForm->validate();

        $space = Space::findOrFail($this->createListForm->spaceId);

        $list = $space->lists()->create([
            'name' => trim($this->createListForm->name),
            'position' => ($space->lists()->max('position') ?? -1) + 1,
        ]);

        $list->createDefaultStatuses();
        $list->members()->attach(auth()->id());

        unset($this->listSpaces);
        $this->createListForm->reset();
        $this->js('$flux.modal("create-list-modal").close()');
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
        $this->editingListId = $list->id;
        $this->editListName = $list->name;
        $this->editListSpaceId = $list->space_id;
        $this->showEditList = true;
    }

    public function openManageMembers(int $listId): void
    {
        if (! $this->authorizeListManagement()) {
            return;
        }

        $list = TaskList::with('members')->findOrFail($listId);
        $this->managingListId = $list->id;
        $this->listMemberIds = $list->members->pluck('id')->toArray();
        $this->showManageMembers = true;
    }

    public function updateList(): void
    {
        if (! $this->authorizeListManagement()) {
            return;
        }

        if (! $this->editingListId) {
            return;
        }

        $list = TaskList::findOrFail($this->editingListId);
        $this->validate(
            [
                'editListName' => 'required|min:2|max:100|unique:task_lists,name,'.$this->editingListId.',id,space_id,'.$this->editListSpaceId,
                'editListSpaceId' => 'required|exists:spaces,id',
            ],
            [
                'editListName.required' => 'Nama list wajib diisi.',
                'editListName.unique' => 'Nama list sudah digunakan di space tujuan.',
                'editListSpaceId.required' => 'Pilih space tujuan terlebih dahulu.',
            ]
        );

        $targetSpace = Space::accessibleBy(auth()->id())->whereKey($this->editListSpaceId)->firstOrFail();
        $updates = [
            'name' => trim($this->editListName),
            'space_id' => $targetSpace->id,
        ];

        if ($list->space_id !== $targetSpace->id) {
            $updates['position'] = ($targetSpace->lists()->max('position') ?? -1) + 1;
        }

        $list->update($updates);
        unset($this->listSpaces);
        $this->reset(['editingListId', 'editListName', 'editListSpaceId', 'showEditList']);
        $this->dispatch('sidebar-updated');
        $this->broadcastChange();
        Flux::toast('List berhasil diperbarui.', variant: 'success');
    }

    // ─── Members ───────────────────────────────────────────────

    public function saveMembers(): void
    {
        if (! $this->authorizeListManagement()) {
            return;
        }

        if (! $this->managingListId) {
            return;
        }

        $list = TaskList::with(['members', 'tasks', 'space'])->findOrFail($this->managingListId);

        // Validate against real user IDs in the DB only — prevents arbitrary ID injection.
        $requestedIds = collect($this->listMemberIds)->map(fn ($id) => (int) $id)->toArray();
        $validIds = User::whereIn('id', $requestedIds)->pluck('id')->toArray();

        $removedIds = array_diff($list->members->pluck('id')->toArray(), $validIds);
        $list->members()->sync($validIds);

        if (! empty($removedIds)) {
            foreach ($list->tasks as $task) {
                $task->assignees()->detach($removedIds);
            }
        }

        unset($this->listSpaces);
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

    /** Calendar grid + tasks — used by the "calendar" tab only. */
    #[Computed]
    public function calendarData(): array
    {
        $monthStart = Carbon::create($this->calYear, $this->calMonth, 1);
        $monthEnd = $monthStart->copy()->endOfMonth();
        $selectedId = $this->calSelectedSpaceId;

        $calTasks = Task::with(['status', 'taskList.space', 'assignees'])
            ->whereNull('parent_id')
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$monthStart, $monthEnd])
            ->whereHas('taskList.space', function ($q) use ($selectedId) {
                $q->accessibleBy(auth()->id());
                if ($selectedId !== null) {
                    $q->where('id', $selectedId);
                }
            })
            ->orderBy('due_date')
            ->limit(500)
            ->get()
            ->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));

        $cur = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
        $calEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $weeks = [];

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

        return [
            'weeks' => $weeks,
            'label' => $monthStart->isoFormat('MMMM Y'),
        ];
    }

    /** All users for the manage-members modal. */
    #[Computed]
    public function allUsers()
    {
        return User::orderBy('name')->get();
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
