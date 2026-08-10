<?php

namespace App\Livewire\Project;

use App\Events\TaskUpdated as TaskUpdatedEvent;
use App\Livewire\Project\Concerns\BuildsTaskDetailViewData;
use App\Livewire\Project\Concerns\EditsTaskFields;
use App\Livewire\Project\Concerns\ManagesChecklistItemDetails;
use App\Livewire\Project\Concerns\ManagesTaskAttachments;
use App\Livewire\Project\Concerns\ManagesTaskChecklists;
use App\Livewire\Project\Concerns\ManagesTaskComments;
use App\Livewire\Project\Concerns\ManagesTaskLabels;
use App\Livewire\Project\Concerns\ManagesTaskSubtasks;
use App\Livewire\Project\Concerns\TracksTaskTime;
use App\Livewire\Project\Concerns\ValidatesWithToast;
use App\Models\Project\Task;
use App\Models\Project\TimeTracking;
use Flux\Flux;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskDetail extends Component
{
    use BuildsTaskDetailViewData;
    use EditsTaskFields;
    use ManagesChecklistItemDetails;
    use ManagesTaskAttachments;
    use ManagesTaskChecklists;
    use ManagesTaskComments;
    use ManagesTaskLabels;
    use ManagesTaskSubtasks;
    use TracksTaskTime;
    use ValidatesWithToast;
    use WithFileUploads;

    /** @var array<int, string> */
    private const TABS = ['overview', 'checklist', 'comments', 'activity'];

    public ?int $taskId = null;

    public ?int $taskListId = null;

    public ?int $workspaceId = null;

    public string $activeTab = 'overview';

    public function mount(?int $taskId = null): void
    {
        if ($taskId) {
            $this->loadTask($taskId);
        }
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        $listeners = [
            'task-status-updated-from-board' => 'syncStatusFromBoard',
        ];

        if ($this->taskId) {
            $listeners["echo:task.{$this->taskId},TaskUpdated"] = 'onBroadcastUpdate';
        }

        return $listeners;
    }

    public function syncStatusFromBoard(int $taskId, int $statusId): void
    {
        if ($this->taskId !== $taskId) {
            return;
        }

        $this->taskStatusId = $statusId;
    }

    public function onBroadcastUpdate(array $event): void
    {
        if (($event['triggeredBy'] ?? null) == auth()->id()) {
            $this->skipRender();

            return;
        }

        if (! $this->taskId) {
            return;
        }

        if (! Task::whereKey($this->taskId)->exists()) {
            Flux::toast('Tugas telah dihapus oleh pengguna lain.', variant: 'danger');
            $this->close();

            return;
        }

        $this->loadTask($this->taskId);
    }

    public function exception($e, $stopPropagation): void
    {
        if ($e instanceof ModelNotFoundException) {
            Flux::toast('Data tidak ditemukan.', variant: 'danger');
            $stopPropagation();
        }
    }

    public function loadTask(int $taskId): void
    {
        $task = Task::with(['status', 'assignees', 'taskList.statuses', 'taskList.space', 'labels'])->find($taskId);

        if (! $task) {
            Flux::toast('Tugas tidak ditemukan.', variant: 'danger');
            $this->close();

            return;
        }

        $this->taskId = $task->id;
        $this->taskListId = $task->task_list_id;
        $this->workspaceId = $task->taskList?->space?->workspace_id;
        $this->taskTitle = $task->title;
        $this->taskDescription = $task->description ?? '';
        $this->taskPriority = $task->priority;
        $this->taskStatusId = $task->task_status_id;
        $this->taskDueDate = $task->due_date?->format('Y-m-d');
        $this->taskAssigneeIds = $task->assignees->pluck('id')->toArray();

        $this->activeTimerId = TimeTracking::where('task_id', $taskId)
            ->where('user_id', auth()->id())
            ->whereNull('stopped_at')
            ->value('id');
    }

    // ─── Shared helpers (used by the Concerns traits) ──────────────

    private function broadcastChange(): void
    {
        if (! $this->taskId) {
            return;
        }

        TaskUpdatedEvent::dispatch($this->taskId, auth()->id(), $this->taskListId, $this->workspaceId);
    }

    private function authorizeManageTask(): ?Task
    {
        if (! $this->taskId) {
            return null;
        }

        $task = Task::with(['assignees', 'taskList'])->findOrFail($this->taskId);

        if (! $task->canBeManagedBy(auth()->user())) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

            return null;
        }

        return $task;
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, self::TABS, true)) {
            $this->activeTab = $tab;
        }
    }

    public function close(): void
    {
        $this->dispatch('close-task-detail');
    }

    public function render()
    {
        return view('livewire.project.task-detail', $this->viewData());
    }
}
