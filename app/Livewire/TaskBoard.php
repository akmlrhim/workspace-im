<?php

namespace App\Livewire;

use App\Events\TaskListUpdated;
use App\Livewire\Concerns\BatchesPositionUpdates;
use App\Livewire\Concerns\BroadcastsChangesSafely;
use App\Livewire\Concerns\FiltersBoardTasks;
use App\Livewire\Concerns\ManagesBoardColumns;
use App\Livewire\Concerns\OpensTaskDetailPanel;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskList;
use App\Models\TaskStatus;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class TaskBoard extends Component
{
    use BatchesPositionUpdates;
    use BroadcastsChangesSafely;
    use FiltersBoardTasks;
    use ManagesBoardColumns;
    use OpensTaskDetailPanel;

    public Space $space;

    public TaskList $taskList;

    public function mount(Space $space, TaskList $taskList): void
    {
        $this->space = $space;
        $this->taskList = $taskList;
    }

    /**
     * @return array<string, string>
     */
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

    public function onTaskUpdated(): void
    {
        unset($this->statuses);
        $this->broadcastChange();
    }

    private function broadcastChange(): void
    {
        $this->broadcastSafely(fn () => TaskListUpdated::dispatch(
            $this->taskList->id,
            auth()->id(),
            $this->taskList->space->workspace_id,
        ));
    }

    private function canManageBoard(): bool
    {
        return once(function () {
            $user = auth()->user();

            if ($user->canManageAllProjects()) {
                return true;
            }

            if ($user->isManager() && $this->taskList->isAccessibleBy($user)) {
                return true;
            }

            if ($this->taskList->members()->where('users.id', $user->id)->exists()) {
                return true;
            }

            return $this->taskList->tasks()->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))->exists()
                || $this->taskList->tasks()->where('assigned_to', $user->id)->exists();
        });
    }

    /**
     * @param  array<int, int>  $orderedIds
     */
    public function moveTask(int $taskId, int $newStatusId, array $orderedIds): void
    {
        $task = Task::with(['assignees', 'status'])->findOrFail($taskId);
        $task->setRelation('taskList', $this->taskList);

        if (! $task->canBeManagedBy(auth()->user())) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');
            unset($this->statuses);

            return;
        }

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

        DB::transaction(function () use ($task, $newStatusId, $oldStatusId, $orderedIds) {
            $task->update(['task_status_id' => $newStatusId]);

            $this->applyPositionOrder('tasks', $orderedIds);

            if ($oldStatusId !== $newStatusId) {
                $sourceIds = Task::where('task_list_id', $this->taskList->id)
                    ->where('task_status_id', $oldStatusId)
                    ->whereNull('parent_id')
                    ->orderBy('position')
                    ->pluck('id')
                    ->all();

                $this->applyPositionOrder('tasks', $sourceIds);
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

            Flux::toast('Dipindah ke '.$newStatusName, variant: 'success');
            $this->dispatch('task-status-updated-from-board', taskId: $task->id, statusId: $newStatusId);
        }

        $this->broadcastChange();

        $this->skipRender();
    }

    #[Computed]
    public function statuses()
    {
        $user = auth()->user();
        $this->taskList->loadMissing('space.workspace');

        $statuses = $this->taskList->statuses()
            ->with(['tasks' => function ($q) {
                $q->whereNull('parent_id')
                    ->with(['assignees', 'labels'])
                    ->withCount([
                        'comments',
                        'attachments',
                        'subtasks',
                        'subtasks as completed_subtasks_count' => fn ($q2) => $q2->where('is_completed', true),
                    ])
                    ->orderBy('position');

                $this->applyTaskFilters($q);
            }])
            ->orderBy('position')
            ->get();

        foreach ($statuses as $status) {
            foreach ($status->tasks as $task) {
                $task->setRelation('taskList', $this->taskList);
                $task->can_drag = $task->canBeManagedBy($user);
            }
        }

        return $statuses;
    }

    public function render()
    {
        return view('livewire.task-board', [
            'canManage' => $this->canManageBoard(),
        ]);
    }
}
