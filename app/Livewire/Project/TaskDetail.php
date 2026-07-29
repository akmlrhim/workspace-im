<?php

namespace App\Livewire\Project;

use App\Events\TaskUpdated as TaskUpdatedEvent;
use App\Livewire\Project\Concerns\ManagesChecklistItemDetails;
use App\Livewire\Project\Concerns\ManagesTaskAttachments;
use App\Livewire\Project\Concerns\ManagesTaskChecklists;
use App\Livewire\Project\Concerns\ManagesTaskComments;
use App\Livewire\Project\Concerns\ManagesTaskLabels;
use App\Livewire\Project\Concerns\ManagesTaskSubtasks;
use App\Livewire\Project\Concerns\TracksTaskTime;
use App\Models\Project\Task;
use App\Models\Project\TaskActivity;
use App\Models\Project\TaskAttachment;
use App\Models\Project\TaskLabel;
use App\Models\Project\TimeTracking;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskDetail extends Component
{
    use ManagesChecklistItemDetails;
    use ManagesTaskAttachments;
    use ManagesTaskChecklists;
    use ManagesTaskComments;
    use ManagesTaskLabels;
    use ManagesTaskSubtasks;
    use TracksTaskTime;
    use WithFileUploads;

    public ?int $taskId = null;

    public ?int $taskListId = null;

    public ?int $workspaceId = null;

    public string $taskTitle = '';

    public string $taskDescription = '';

    public string $taskPriority = 'normal';

    public ?int $taskStatusId = null;

    public ?string $taskDueDate = null;

    public array $taskAssigneeIds = [];

    // UI State
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

        $activeTimer = TimeTracking::where('task_id', $taskId)
            ->where('user_id', auth()->id())
            ->whereNull('stopped_at')
            ->first();
        $this->activeTimerId = $activeTimer?->id;
    }

    // ─── Shared helpers (used by the Concerns traits) ──────────────

    /**
     * @param  array<string, string>  $rules
     * @param  array<string, string>  $messages
     */
    private function validateWithToast(array $rules, array $messages = []): bool
    {
        $data = [];
        foreach ($rules as $field => $rule) {
            $data[$field] = data_get($this, $field);
        }

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            Flux::toast($validator->errors()->first(), variant: 'danger');

            return false;
        }

        return true;
    }

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

    // ─── Core task fields ──────────────────────────────────────────

    public function saveTitle(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        if (! $this->validateWithToast(['taskTitle' => 'required|min:1|max:500'])) {
            return;
        }
        $task->update(['title' => $this->taskTitle]);
        $this->dispatch('task-updated');
        $this->broadcastChange();
    }

    public function saveDescription(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        $task->update(['description' => $this->taskDescription]);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Deskripsi disimpan.', variant: 'success');
    }

    public function updateStatus(int $statusId): void
    {
        $task = $this->authorizeManageTask();

        if (! $task) {
            return;
        }

        // Validate status belongs to this task's list
        $validStatusIds = $task->taskList->statuses()->pluck('id')->toArray();
        if (! in_array($statusId, $validStatusIds)) {
            Flux::toast('Status tidak valid untuk list ini.', variant: 'danger');

            return;
        }

        $oldStatus = $task->status->name ?? '-';
        $task->update(['task_status_id' => $statusId]);
        $task->refresh();
        $this->taskStatusId = $statusId;

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $task->status->name,
        ]);

        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Status diperbarui.', variant: 'success');
    }

    public function updatePriority(string $priority): void
    {
        if (! in_array($priority, ['urgent', 'high', 'normal', 'low'])) {
            Flux::toast('Prioritas tidak valid.', variant: 'danger');

            return;
        }

        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        $old = $task->priority;
        $task->update(['priority' => $priority]);
        $this->taskPriority = $priority;

        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'priority_changed',
            'old_value' => $old,
            'new_value' => $priority,
        ]);

        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Prioritas diperbarui.', variant: 'success');
    }

    public function updateDueDate(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        $task->update(['due_date' => $this->taskDueDate ?: null]);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Tanggal jatuh tempo diperbarui.', variant: 'success');
    }

    public function updateAssignees(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        $task->load('taskList.members');

        $listMemberIds = $task->taskList->members->pluck('id')->toArray();
        $invalidIds = array_diff($this->taskAssigneeIds, $listMemberIds);

        if (! empty($invalidIds)) {
            Flux::toast('User yang dipilih bukan anggota dari list ini.', variant: 'danger');
            $this->taskAssigneeIds = array_values(array_intersect($this->taskAssigneeIds, $listMemberIds));

            return;
        }

        $task->assignees()->sync($this->taskAssigneeIds);

        $names = User::whereIn('id', $this->taskAssigneeIds)->pluck('name')->join(', ') ?: 'Unassigned';
        TaskActivity::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'type' => 'assignee_changed',
            'new_value' => $names,
        ]);

        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Penugasan berhasil diperbarui.', variant: 'success');
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'checklist', 'comments', 'activity'], true)) {
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

    /**
     * Everything the task detail view renders, keyed by view variable name.
     *
     * @return array<string, mixed>
     */
    private function viewData(): array
    {
        $task = $this->taskId
            ? Task::with([
                'status',
                'assignees',
                'labels',
                'creator',
                'taskList.statuses',
                'taskList.space.workspace',
            ])->find($this->taskId)
            : null;

        if (! $task) {
            return [
                'task' => null,
                'canManage' => false,
                'comments' => collect(),
                'subtasks' => collect(),
                'checklists' => collect(),
                'attachments' => collect(),
                'activities' => collect(),
                'timeEntries' => collect(),
                'statuses' => collect(),
                'totalTimeSeconds' => 0,
                'workspaceUsers' => collect(),
                'allLabels' => collect(),
                'activeItemAttachments' => collect(),
            ];
        }

        return [
            'task' => $task,
            'canManage' => $task->canBeManagedBy(auth()->user()),
            'comments' => $task->comments()->with(['user', 'replies.user'])->get(),
            'subtasks' => $task->subtasks()->with('status')->get(),
            'checklists' => $task->checklists()->with([
                'items' => fn ($q) => $q->orderBy('position')->with(['assignees', 'attachments']),
            ])->get(),
            'attachments' => $task->attachments()->whereNull('task_checklist_item_id')->latest()->get(),
            'activities' => $task->activities()->with('user')->latest()->take(20)->get(),
            'timeEntries' => $task->timeTrackings()->with('user')->latest()->take(30)->get(),
            'statuses' => $task->taskList->statuses->sortBy('position')->values(),
            'totalTimeSeconds' => $task->timeTrackings()->sum('duration_seconds'),
            'workspaceUsers' => $task->taskList->members()->orderBy('name')->get(),
            'allLabels' => $this->workspaceId
                ? TaskLabel::where('workspace_id', $this->workspaceId)->orderBy('name')->get()
                : collect(),
            'activeItemAttachments' => $this->activeChecklistItemId
                ? TaskAttachment::where('task_checklist_item_id', $this->activeChecklistItemId)->latest()->get()
                : collect(),
        ];
    }
}
