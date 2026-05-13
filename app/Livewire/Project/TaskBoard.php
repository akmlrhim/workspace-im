<?php

namespace App\Livewire\Project;

use App\Events\TaskListUpdated;
use App\Livewire\Forms\TaskColumnForm;
use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskActivity;
use App\Models\Project\TaskLabel;
use App\Models\Project\TaskList;
use App\Models\Project\TaskStatus;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TaskBoard extends Component
{
	public Space $space;

	public TaskList $taskList;

	// Create task
	public string $newTaskTitle = '';

	public ?int $createInStatusId = null;

	// Create column
	public bool $showNewColumnInput = false;

	public TaskColumnForm $newColumn;

	// Task detail
	public ?int $selectedTaskId = null;

	public bool $showTaskDetail = false;

	// Delete column confirm
	public bool $showDeleteColumnConfirm = false;

	public ?int $deletingColumnId = null;

	// Filters
	public ?int $filterAssigneeId = null;

	public ?string $filterPriority = null;

	public ?int $filterLabelId = null;

	public function mount(Space $space, TaskList $taskList): void
	{
		$this->space = $space;
		$this->taskList = $taskList;
	}

	/** @return array<string, string> */
	public function getListeners(): array
	{
		return [
			"echo:task-list.{$this->taskList->id},TaskListUpdated" => 'onBroadcastUpdate',
			"echo:task-list.{$this->taskList->id},TaskUpdated" => 'onBroadcastUpdate',
			'task-updated' => 'onTaskUpdated',
			'close-task-detail' => 'closeTaskDetail',
			'task-deleted' => 'onTaskDeleted',
		];
	}

	public function onBroadcastUpdate(array $event): void
	{
		if (($event['triggeredBy'] ?? null) == auth()->id()) {
			$this->skipRender();

			return;
		}

		unset($this->statuses);
	}

	private function broadcastChange(): void
	{
		TaskListUpdated::dispatch(
			$this->taskList->id,
			auth()->id(),
			$this->taskList->space->workspace_id,
		);
	}

	private function canManageBoard(): bool
	{
		$user = auth()->user();

		if ($user->canManageAllProjects()) {
			return true;
		}

		if ($user->isManager() && $this->taskList->isAccessibleBy($user)) {
			return true;
		}

		// List members (assigned to any task or added as member) can create tasks
		if ($this->taskList->members()->where('users.id', $user->id)->exists()) {
			return true;
		}

		return $this->taskList->tasks()->whereHas('assignees', fn($q) => $q->where('users.id', $user->id))->exists()
			|| $this->taskList->tasks()->where('assigned_to', $user->id)->exists();
	}

	// ─── Column CRUD ───────────────────────────────────────────────

	public function addColumn(): void
	{
		if (! $this->canManageBoard()) {
			Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

			return;
		}

		$this->newColumn->validate();

		$maxPosition = TaskStatus::where('task_list_id', $this->taskList->id)
			->max('position') ?? -1;

		TaskStatus::create([
			'task_list_id' => $this->taskList->id,
			'name' => trim($this->newColumn->name),
			'color' => $this->newColumn->color,
			'position' => $maxPosition + 1,
			'type' => 'active',
		]);

		$this->newColumn->reset();
		$this->showNewColumnInput = false;

		$this->broadcastChange();
		Flux::toast('Kolom baru berhasil ditambahkan.', variant: 'success');
	}

	public function saveColumnRename(int $columnId, string $name, string $color): void
	{
		if (! $this->canManageBoard()) {
			Flux::toast('Anda tidak memiliki izin untuk mengubah kolom ini.', variant: 'danger');

			return;
		}

		$name = trim($name);

		if ($name === '' || strlen($name) > 100) {
			return;
		}

		$allowedColors = ['#6b7280', '#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'];

		if (! in_array($color, $allowedColors, true)) {
			$color = '#6b7280';
		}

		TaskStatus::where('id', $columnId)
			->where('task_list_id', $this->taskList->id)
			->update(['name' => $name, 'color' => $color]);

		$this->broadcastChange();
		unset($this->statuses);
		Flux::toast('Kolom berhasil diperbarui.', variant: 'success');
	}

	public function confirmDeleteColumn(int $columnId): void
	{
		if (! $this->canManageBoard()) {
			return;
		}
		$this->deletingColumnId = $columnId;
		$this->showDeleteColumnConfirm = true;
	}

	public function deleteColumn(): void
	{
		if (! $this->canManageBoard() || ! $this->deletingColumnId) {
			return;
		}

		$columnId = $this->deletingColumnId;
		$column = TaskStatus::where('id', $columnId)
			->where('task_list_id', $this->taskList->id)
			->firstOrFail();

		// Prevent deleting the last column
		$remainingCount = TaskStatus::where('task_list_id', $this->taskList->id)->count();
		if ($remainingCount <= 1) {
			$this->reset(['showDeleteColumnConfirm', 'deletingColumnId']);
			Flux::toast('Tidak bisa menghapus kolom terakhir.', variant: 'danger');

			return;
		}

		// Move tasks in this column to the first remaining column
		$firstOther = TaskStatus::where('task_list_id', $this->taskList->id)
			->where('id', '!=', $columnId)
			->orderBy('position')
			->first();

		if ($firstOther) {
			Task::where('task_status_id', $columnId)
				->update(['task_status_id' => $firstOther->id]);
		}

		$column->delete();

		$this->reset(['showDeleteColumnConfirm', 'deletingColumnId']);
		$this->broadcastChange();
		Flux::toast('Kolom berhasil dihapus.', variant: 'success');
	}

	public function updateColumnOrder(array $orderedIds): void
	{
		if (! $this->canManageBoard()) {
			Flux::toast('Anda tidak memiliki izin untuk mengatur ulang kolom.', variant: 'danger');

			return;
		}

		if (empty($orderedIds)) {
			return;
		}

		$cases = [];
		$bindings = [];

		foreach ($orderedIds as $position => $columnId) {
			$cases[] = 'WHEN id = ? THEN ?';
			$bindings[] = $columnId;
			$bindings[] = $position;
		}

		$bindings[] = $this->taskList->id;
		$bindings = array_merge($bindings, $orderedIds);
		$placeholders = implode(',', array_fill(0, count($orderedIds), '?'));

		DB::update(
			'UPDATE task_statuses SET position = CASE ' . implode(' ', $cases) . ' END WHERE task_list_id = ? AND id IN (' . $placeholders . ')',
			$bindings
		);

		$this->broadcastChange();
	}

	// ─── Task CRUD ─────────────────────────────────────────────────

	public function createTaskInStatus(int $statusId): void
	{
		if (! $this->canManageBoard()) {
			Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

			return;
		}
		if (empty($this->newTaskTitle)) {
			return;
		}

		$maxPosition = Task::where('task_list_id', $this->taskList->id)
			->where('task_status_id', $statusId)
			->max('position') ?? -1;

		$task = Task::create([
			'task_list_id' => $this->taskList->id,
			'task_status_id' => $statusId,
			'title' => $this->newTaskTitle,
			'priority' => 'normal',
			'position' => $maxPosition + 1,
			'created_by' => auth()->id(),
		]);

		// Ensure creator is a list member then auto-assign them to the task
		$this->taskList->members()->syncWithoutDetaching([auth()->id()]);
		$task->assignees()->sync([auth()->id()]);

		TaskActivity::create([
			'task_id' => $task->id,
			'user_id' => auth()->id(),
			'type' => 'created',
			'new_value' => $task->title,
		]);

		$this->reset('newTaskTitle');
		$this->createInStatusId = null;

		$this->dispatch('task-created-on-board', taskId: $task->id, statusId: $statusId);

		$this->broadcastChange();
		Flux::toast('Tugas berhasil dibuat.', variant: 'success');
	}

	public function moveTask(int $taskId, int $newStatusId, array $orderedIds): void
	{
		// Load assignees + status only; set taskList from the already-bound component property
		// to avoid re-fetching taskList.space.workspace just for canBeManagedBy()
		$task = Task::with(['assignees', 'status'])->findOrFail($taskId);
		$task->setRelation('taskList', $this->taskList);

		if (! $task->canBeManagedBy(auth()->user())) {
			Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');
			unset($this->statuses);

			return;
		}

		// Validate the destination status belongs to this list
		$validStatus = TaskStatus::where('id', $newStatusId)
			->where('task_list_id', $this->taskList->id)
			->exists();

		if (! $validStatus) {
			Flux::toast('Status tidak valid untuk list ini.', variant: 'danger');
			unset($this->statuses);

			return;
		}

		$oldStatusId = $task->task_status_id;
		$oldStatusName = $task->status?->name ?? '-';

		// Batch update: moved task status + all target column positions in one transaction
		DB::transaction(function () use ($task, $newStatusId, $oldStatusId, $orderedIds) {
			$task->update(['task_status_id' => $newStatusId]);

			// Batch position update for target column
			$this->batchUpdatePositions($orderedIds);

			// Re-index source column if cross-column move
			if ($oldStatusId !== $newStatusId) {
				$sourceIds = Task::where('task_list_id', $this->taskList->id)
					->where('task_status_id', $oldStatusId)
					->whereNull('parent_id')
					->orderBy('position')
					->pluck('id')
					->all();

				$this->batchUpdatePositions($sourceIds);
			}
		});

		if ($oldStatusId !== $newStatusId) {
			$newStatusName = TaskStatus::where('id', $newStatusId)->value('name') ?? '';

			TaskActivity::create([
				'task_id' => $task->id,
				'user_id' => auth()->id(),
				'type' => 'status_changed',
				'old_value' => $oldStatusName,
				'new_value' => $newStatusName,
			]);

			Flux::toast('Dipindah ke ' . $newStatusName, variant: 'success');
			$this->dispatch('task-status-updated-from-board', taskId: $task->id, statusId: $newStatusId);
		}

		$this->broadcastChange();

		// SortableJS already moved the card to the correct position in the DOM.
		// Re-rendering would cause a brief rollback when multiple drags happen quickly
		// because Livewire's morph would overwrite Sortable's visual state mid-drag.
		// Other users receive the update via broadcast and re-render on their side.
		$this->skipRender();
	}

	/**
	 * Batch update positions using a single CASE query instead of N individual updates.
	 *
	 * @param  array<int, int>  $orderedIds
	 */
	private function batchUpdatePositions(array $orderedIds): void
	{
		if (empty($orderedIds)) {
			return;
		}

		$cases = [];
		$bindings = [];

		foreach ($orderedIds as $position => $id) {
			$cases[] = 'WHEN id = ? THEN ?';
			$bindings[] = $id;
			$bindings[] = $position;
		}

		$bindings = array_merge($bindings, $orderedIds);
		$placeholders = implode(',', array_fill(0, count($orderedIds), '?'));

		DB::update(
			'UPDATE tasks SET position = CASE ' . implode(' ', $cases) . ' END WHERE id IN (' . $placeholders . ')',
			$bindings
		);
	}

	// ─── Task Detail ───────────────────────────────────────────────

	public function openTaskDetail(int $taskId): void
	{
		$this->selectedTaskId = $taskId;
		$this->showTaskDetail = true;
	}

	public function onTaskUpdated(): void
	{
		unset($this->statuses);
		$this->broadcastChange();
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

	#[Computed]
	public function statuses()
	{
		$user = auth()->user();
		$this->taskList->loadMissing('space.workspace');

		$filterAssigneeId = $this->filterAssigneeId;
		$filterPriority = $this->filterPriority;
		$filterLabelId = $this->filterLabelId;

		$statuses = $this->taskList->statuses()
			->with(['tasks' => function ($q) use ($filterAssigneeId, $filterPriority, $filterLabelId) {
				$q->whereNull('parent_id')
					->with(['assignees', 'labels'])
					->withCount([
						'comments',
						'attachments',
						'subtasks',
						'subtasks as completed_subtasks_count' => fn($q2) => $q2->where('is_completed', true),
					])
					->orderBy('position');

				if ($filterAssigneeId) {
					$q->whereHas('assignees', fn($q2) => $q2->where('users.id', $filterAssigneeId));
				}
				if ($filterPriority) {
					$q->where('priority', $filterPriority);
				}
				if ($filterLabelId) {
					$q->whereHas('labels', fn($q2) => $q2->where('task_labels.id', $filterLabelId));
				}
			}])
			->orderBy('position')
			->get();

		foreach ($statuses as $status) {
			foreach ($status->tasks as $task) {
				// Ensure canBeManagedBy() resolves workspace without extra queries
				$task->setRelation('taskList', $this->taskList);
				$task->can_drag = $task->canBeManagedBy($user);
			}
		}

		return $statuses;
	}

	#[Computed]
	public function hasActiveFilter(): bool
	{
		return filled($this->filterAssigneeId)
			|| filled($this->filterPriority)
			|| filled($this->filterLabelId);
	}

	#[Computed]
	public function availableAssignees()
	{
		return $this->taskList->members()->orderBy('name')->get();
	}

	#[Computed]
	public function availableLabels()
	{
		$this->taskList->loadMissing('space.workspace');

		return TaskLabel::where('workspace_id', $this->taskList->space->workspace_id)
			->orderBy('name')
			->get();
	}

	public function clearFilters(): void
	{
		$this->filterAssigneeId = null;
		$this->filterPriority = null;
		$this->filterLabelId = null;
		unset($this->statuses);
	}

	public function updatedFilterAssigneeId(): void
	{
		unset($this->statuses);
	}

	public function updatedFilterPriority(): void
	{
		unset($this->statuses);
	}

	public function updatedFilterLabelId(): void
	{
		unset($this->statuses);
	}

	public function render()
	{
		return view('livewire.project.task-board', [
			'canManage' => $this->canManageBoard(),
		]);
	}
}
