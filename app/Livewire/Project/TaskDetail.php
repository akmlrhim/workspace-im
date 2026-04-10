<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use App\Models\Project\TaskActivity;
use App\Models\Project\TaskAttachment;
use App\Models\Project\TaskChecklist;
use App\Models\Project\TaskChecklistItem;
use App\Models\Project\TaskComment;
use App\Models\Project\TaskLabel;
use App\Models\Project\TimeTracking;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskDetail extends Component
{
    use WithFileUploads;

    public ?int $taskId = null;

    public string $taskTitle = '';

    public string $taskDescription = '';

    public string $taskPriority = 'normal';

    public ?int $taskStatusId = null;

    public ?string $taskDueDate = null;

    public array $taskAssigneeIds = [];

    // Comments
    public string $newComment = '';

    // Subtasks (legacy, kept for backward compat)
    public string $newSubtaskTitle = '';

    public bool $showSubtaskForm = false;

    // Attachments (task-level)
    #[Rule(['uploadFiles.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'])]
    public array $uploadFiles = [];

    // Labels
    public bool $showLabelForm = false;

    public string $newLabelName = '';

    public string $newLabelColor = '#6366f1';

    // Timer
    public ?int $activeTimerId = null;

    // ── Checklist ────────────────────────────────────────────────────
    public bool $showChecklistForm = false;

    public string $newChecklistName = '';

    // Which checklist's "add item" form is open
    public ?int $addingItemToChecklistId = null;

    public string $newChecklistItemTitle = '';

    // Active item panel (for assignees / due date / attachments)
    public ?int $activeChecklistItemId = null;

    public string $activeItemDueDate = '';

    public array $activeItemAssigneeIds = [];

    #[Rule(['activeItemFiles.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'])]
    public array $activeItemFiles = [];

    public function mount(?int $taskId = null): void
    {
        if ($taskId) {
            $this->loadTask($taskId);
        }
    }

    public function loadTask(int $taskId): void
    {
        $task = Task::with(['status', 'assignees', 'taskList.statuses', 'labels'])->findOrFail($taskId);
        $this->taskId = $task->id;
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

    private function authorizeManageTask(): bool
    {
        $task = Task::with('assignees')->findOrFail($this->taskId);
        if (! $task->canBeManagedBy(auth()->user())) {
            Flux::toast(__('messages.no_permission_modify_task'), variant: 'danger');

            return false;
        }

        return true;
    }

    public function saveTitle(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        Task::findOrFail($this->taskId)->update(['title' => $this->taskTitle]);
        $this->dispatch('task-updated');
    }

    public function saveDescription(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        Task::findOrFail($this->taskId)->update(['description' => $this->taskDescription]);
        Flux::toast(__('messages.description_saved'), variant: 'success');
    }

    public function updateStatus(int $statusId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $task = Task::findOrFail($this->taskId);
        $oldStatus = $task->status->name;
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
        Flux::toast(__('messages.status_updated'), variant: 'success');
    }

    public function updatePriority(string $priority): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $task = Task::findOrFail($this->taskId);
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
        Flux::toast(__('messages.priority_updated'), variant: 'success');
    }

    public function updateDueDate(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        Task::findOrFail($this->taskId)->update(['due_date' => $this->taskDueDate ?: null]);
        $this->dispatch('task-updated');
        Flux::toast(__('messages.due_date_updated'), variant: 'success');
    }

    public function updateAssignees(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $task = Task::with('taskList.members')->findOrFail($this->taskId);

        $listMemberIds = $task->taskList->members->pluck('id')->toArray();
        $invalidIds = array_diff($this->taskAssigneeIds, $listMemberIds);

        if (! empty($invalidIds)) {
            Flux::toast(__('messages.assignee_not_list_member'), variant: 'danger');
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
        Flux::toast(__('messages.assignees_updated'), variant: 'success');
    }

    // ─── Labels ────────────────────────────────────────────────────

    public function toggleLabel(int $labelId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        Task::findOrFail($this->taskId)->labels()->toggle($labelId);
        $this->dispatch('task-updated');
    }

    public function createLabel(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }

        $this->validate(['newLabelName' => 'required|min:1|max:100']);

        $task = Task::with('taskList.space.workspace')->findOrFail($this->taskId);
        $workspaceId = $task->taskList->space->workspace->id;

        $label = TaskLabel::create([
            'workspace_id' => $workspaceId,
            'name' => trim($this->newLabelName),
            'color' => $this->newLabelColor,
        ]);

        $task->labels()->attach($label->id);

        $this->reset(['newLabelName', 'newLabelColor', 'showLabelForm']);
        $this->newLabelColor = '#6366f1';

        $this->dispatch('task-updated');
        Flux::toast(__('messages.label_created_attached'), variant: 'success');
    }

    // ─── Comments ──────────────────────────────────────────────────

    public function addComment(): void
    {
        $this->validate(['newComment' => 'required|min:1']);

        TaskComment::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'body' => $this->newComment,
        ]);

        $this->reset('newComment');
        Flux::toast(__('messages.comment_added'), variant: 'success');
    }

    public function deleteComment(int $commentId): void
    {
        TaskComment::where('id', $commentId)->where('user_id', auth()->id())->delete();
        Flux::toast(__('messages.comment_deleted'), variant: 'danger');
    }

    // ─── Subtasks (legacy) ─────────────────────────────────────────

    public function addSubtask(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $this->validate(['newSubtaskTitle' => 'required|min:1|max:500']);

        $parentTask = Task::findOrFail($this->taskId);
        $maxPosition = Task::where('parent_id', $this->taskId)->max('position') ?? -1;

        $firstStatus = $parentTask->taskList->statuses()->orderBy('position')->first();

        Task::create([
            'task_list_id' => $parentTask->task_list_id,
            'task_status_id' => $firstStatus->id,
            'parent_id' => $this->taskId,
            'title' => $this->newSubtaskTitle,
            'priority' => 'normal',
            'position' => $maxPosition + 1,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['newSubtaskTitle', 'showSubtaskForm']);
        Flux::toast(__('messages.subtask_added'), variant: 'success');
    }

    public function toggleSubtaskComplete(int $subtaskId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $subtask = Task::where('parent_id', $this->taskId)->findOrFail($subtaskId);
        $subtask->update(['is_completed' => ! $subtask->is_completed]);

        if ($subtask->is_completed) {
            $doneStatus = $subtask->taskList->statuses()->orderByDesc('position')->first();
            if ($doneStatus) {
                $subtask->update(['task_status_id' => $doneStatus->id]);
            }
        } else {
            $todoStatus = $subtask->taskList->statuses()->orderBy('position')->first();
            if ($todoStatus) {
                $subtask->update(['task_status_id' => $todoStatus->id]);
            }
        }
    }

    public function editSubtaskTitle(int $subtaskId, string $newTitle): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $newTitle = trim($newTitle);
        if (empty($newTitle)) {
            Flux::toast(__('messages.subtask_title_empty'), variant: 'warning');

            return;
        }

        Task::where('parent_id', $this->taskId)->findOrFail($subtaskId)->update(['title' => $newTitle]);
        Flux::toast(__('messages.subtask_updated'), variant: 'success');
    }

    public function deleteSubtask(int $subtaskId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        Task::where('parent_id', $this->taskId)->findOrFail($subtaskId)->delete();
        Flux::toast(__('messages.subtask_deleted'), variant: 'success');
    }

    // ─── Task-level Attachments ────────────────────────────────────

    public function updatedUploadFiles(): void
    {
        $this->uploadAttachment();
    }

    public function uploadAttachment(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $this->validate(['uploadFiles.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip']);

        foreach ($this->uploadFiles as $file) {
            $path = $file->store('task-attachments', 'public');

            TaskAttachment::create([
                'task_id' => $this->taskId,
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $this->reset('uploadFiles');
        Flux::toast(__('messages.file_uploaded'), variant: 'success');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $attachment = TaskAttachment::where('id', $attachmentId)->where('user_id', auth()->id())->firstOrFail();
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
        Flux::toast(__('messages.attachment_deleted'), variant: 'danger');
    }

    // ─── Checklist Groups ──────────────────────────────────────────

    public function addChecklist(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $this->validate(['newChecklistName' => 'required|min:1|max:200']);

        $maxPosition = TaskChecklist::where('task_id', $this->taskId)->max('position') ?? -1;

        TaskChecklist::create([
            'task_id' => $this->taskId,
            'name' => trim($this->newChecklistName),
            'position' => $maxPosition + 1,
        ]);

        $this->reset(['newChecklistName', 'showChecklistForm']);
        Flux::toast(__('messages.checklist_created'), variant: 'success');
    }

    public function deleteChecklist(int $checklistId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        TaskChecklist::where('task_id', $this->taskId)->findOrFail($checklistId)->delete();
        Flux::toast(__('messages.checklist_deleted'), variant: 'danger');
    }

    public function editChecklistName(int $checklistId, string $name): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $name = trim($name);
        if (empty($name)) {
            return;
        }
        TaskChecklist::where('task_id', $this->taskId)->findOrFail($checklistId)->update(['name' => $name]);
    }

    // ─── Checklist Items ───────────────────────────────────────────

    public function openAddChecklistItem(int $checklistId): void
    {
        $this->addingItemToChecklistId = $this->addingItemToChecklistId === $checklistId ? null : $checklistId;
        $this->newChecklistItemTitle = '';
    }

    public function addChecklistItem(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $this->validate(['newChecklistItemTitle' => 'required|min:1|max:500']);

        $checklist = TaskChecklist::where('task_id', $this->taskId)->findOrFail($this->addingItemToChecklistId);
        $maxPosition = $checklist->items()->max('position') ?? -1;

        TaskChecklistItem::create([
            'task_checklist_id' => $checklist->id,
            'title' => trim($this->newChecklistItemTitle),
            'position' => $maxPosition + 1,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['newChecklistItemTitle', 'addingItemToChecklistId']);
        Flux::toast(__('messages.checklist_item_added'), variant: 'success');
    }

    public function toggleChecklistItem(int $itemId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $item = TaskChecklistItem::findOrFail($itemId);
        $item->update(['is_completed' => ! $item->is_completed]);
    }

    public function editChecklistItemTitle(int $itemId, string $title): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $title = trim($title);
        if (empty($title)) {
            return;
        }
        TaskChecklistItem::findOrFail($itemId)->update(['title' => $title]);
        Flux::toast(__('messages.checklist_item_updated'), variant: 'success');
    }

    public function deleteChecklistItem(int $itemId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        TaskChecklistItem::findOrFail($itemId)->delete();

        if ($this->activeChecklistItemId === $itemId) {
            $this->closeChecklistItemPanel();
        }

        Flux::toast(__('messages.checklist_item_deleted'), variant: 'danger');
    }

    // ─── Checklist Item Panel (assignees / due date / attachments) ─

    public function openChecklistItemPanel(int $itemId): void
    {
        if ($this->activeChecklistItemId === $itemId) {
            $this->closeChecklistItemPanel();

            return;
        }

        $item = TaskChecklistItem::with('assignees')->findOrFail($itemId);
        $this->activeChecklistItemId = $itemId;
        $this->activeItemDueDate = $item->due_date?->format('Y-m-d') ?? '';
        $this->activeItemAssigneeIds = $item->assignees->pluck('id')->toArray();
        $this->activeItemFiles = [];
    }

    public function closeChecklistItemPanel(): void
    {
        $this->activeChecklistItemId = null;
        $this->activeItemDueDate = '';
        $this->activeItemAssigneeIds = [];
        $this->activeItemFiles = [];
    }

    public function updateChecklistItemDueDate(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }
        TaskChecklistItem::findOrFail($this->activeChecklistItemId)
            ->update(['due_date' => $this->activeItemDueDate ?: null]);
        Flux::toast(__('messages.checklist_item_due_date_updated'), variant: 'success');
    }

    public function clearChecklistItemDueDate(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }
        $this->activeItemDueDate = '';
        TaskChecklistItem::findOrFail($this->activeChecklistItemId)
            ->update(['due_date' => null]);
        Flux::toast(__('messages.checklist_item_due_date_updated'), variant: 'success');
    }

    public function updateChecklistItemAssignees(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }

        // Only allow task assignees
        $taskAssigneeIds = Task::findOrFail($this->taskId)->assignees()->pluck('users.id')->toArray();
        $validIds = array_values(array_intersect($this->activeItemAssigneeIds, $taskAssigneeIds));
        $this->activeItemAssigneeIds = $validIds;

        TaskChecklistItem::findOrFail($this->activeChecklistItemId)->assignees()->sync($validIds);
        Flux::toast(__('messages.checklist_item_assignees_updated'), variant: 'success');
    }

    public function updatedActiveItemFiles(): void
    {
        $this->uploadChecklistItemFiles();
    }

    public function uploadChecklistItemFiles(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }

        $this->validate(['activeItemFiles.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip']);

        $item = TaskChecklistItem::with('checklist')->findOrFail($this->activeChecklistItemId);

        foreach ($this->activeItemFiles as $file) {
            $path = $file->store('task-attachments', 'public');

            TaskAttachment::create([
                'task_id' => $item->checklist->task_id,
                'task_checklist_item_id' => $item->id,
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $this->activeItemFiles = [];
        Flux::toast(__('messages.file_uploaded'), variant: 'success');
    }

    public function deleteChecklistItemAttachment(int $attachmentId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $attachment = TaskAttachment::where('id', $attachmentId)
            ->where('task_checklist_item_id', $this->activeChecklistItemId)
            ->where('user_id', auth()->id())
            ->firstOrFail();
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();
        Flux::toast(__('messages.attachment_deleted'), variant: 'danger');
    }

    // ─── Time tracking ─────────────────────────────────────────────

    public function startTimer(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $timer = TimeTracking::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'started_at' => now(),
        ]);
        $this->activeTimerId = $timer->id;
        Flux::toast(__('messages.timer_started'), variant: 'success');
    }

    public function stopTimer(): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        if ($this->activeTimerId) {
            $timer = TimeTracking::findOrFail($this->activeTimerId);
            $now = now();
            $timer->update([
                'stopped_at' => $now,
                'duration_seconds' => $timer->started_at->diffInSeconds($now),
            ]);
            $this->activeTimerId = null;
            Flux::toast(__('messages.timer_stopped'), variant: 'success');
        }
    }

    public function close(): void
    {
        $this->dispatch('close-task-detail');
    }

    public function render()
    {
        $task = null;
        $comments = collect();
        $subtasks = collect();
        $checklists = collect();
        $attachments = collect();
        $activities = collect();
        $timeEntries = collect();
        $statuses = collect();
        $totalTimeSeconds = 0;
        $workspaceUsers = collect();
        $canManage = false;
        $allLabels = collect();
        $activeItemAttachments = collect();

        if ($this->taskId) {
            $task = Task::with([
                'status', 'assignees', 'labels', 'creator',
                'taskList.statuses.tasks', 'taskList.space.workspace',
            ])->find($this->taskId);

            if ($task) {
                $canManage = $task->canBeManagedBy(auth()->user());
                $comments = $task->comments()->with(['user', 'replies.user'])->get();
                $subtasks = $task->subtasks()->with('status')->get();
                $checklists = $task->checklists()->with([
                    'items' => fn ($q) => $q->with(['assignees', 'attachments.user']),
                ])->get();
                $attachments = $task->attachments()
                    ->whereNull('task_checklist_item_id')
                    ->with('user')->latest()->get();
                $activities = $task->activities()->with('user')->latest()->take(20)->get();
                $timeEntries = $task->timeTrackings()->where('user_id', auth()->id())->latest()->take(10)->get();
                $statuses = $task->taskList->statuses()->orderBy('position')->get();
                $totalTimeSeconds = $task->timeTrackings()->where('user_id', auth()->id())->sum('duration_seconds');
                $workspaceUsers = $task->taskList->members()->orderBy('name')->get();
                $allLabels = TaskLabel::where('workspace_id', $task->taskList->space->workspace->id)->orderBy('name')->get();

                if ($this->activeChecklistItemId) {
                    $activeItemAttachments = TaskAttachment::where('task_checklist_item_id', $this->activeChecklistItemId)
                        ->with('user')->latest()->get();
                }
            }
        }

        return view('livewire.project.task-detail', [
            'task' => $task,
            'canManage' => $canManage,
            'comments' => $comments,
            'subtasks' => $subtasks,
            'checklists' => $checklists,
            'attachments' => $attachments,
            'activities' => $activities,
            'timeEntries' => $timeEntries,
            'statuses' => $statuses,
            'totalTimeSeconds' => $totalTimeSeconds,
            'workspaceUsers' => $workspaceUsers,
            'allLabels' => $allLabels,
            'activeItemAttachments' => $activeItemAttachments,
        ]);
    }
}
