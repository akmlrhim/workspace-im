<?php

namespace App\Livewire\Project;

use App\Events\TaskUpdated as TaskUpdatedEvent;
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
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskDetail extends Component
{
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

    // Comments
    public string $newComment = '';

    // Subtasks (legacy, kept for backward compat)
    public string $newSubtaskTitle = '';

    public bool $showSubtaskForm = false;

    // Attachments (task-level) — 5 MB max
    #[Rule(['uploadFiles.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'], message: ['uploadFiles.*.max' => 'Ukuran file maksimal 5 MB.', 'uploadFiles.*.mimes' => 'Format file tidak didukung.'])]
    public array $uploadFiles = [];

    public bool $showLinkForm = false;

    public string $newLinkUrl = '';

    public string $newLinkLabel = '';

    // Labels
    public bool $showLabelForm = false;

    public string $newLabelName = '';

    public string $newLabelColor = '#6366f1';

    // Timer
    public ?int $activeTimerId = null;

    public bool $showChecklistForm = false;

    public string $newChecklistName = '';

    public ?int $addingItemToChecklistId = null;

    public string $newChecklistItemTitle = '';

    public ?int $activeChecklistItemId = null;

    public string $activeItemDueDate = '';

    public array $activeItemAssigneeIds = [];

    // Checklist item attachments — 5 MB max
    #[Rule(['activeItemFiles.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'], message: ['activeItemFiles.*.max' => 'Ukuran file maksimal 5 MB.', 'activeItemFiles.*.mimes' => 'Format file tidak didukung.'])]
    public array $activeItemFiles = [];

    public bool $showItemLinkForm = false;

    public string $newItemLinkUrl = '';

    public string $newItemLinkLabel = '';

    // UI State
    public string $activeTab = 'overview';

    public ?int $replyingToCommentId = null;

    public string $replyBody = '';

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

    public function loadTask(int $taskId): void
    {
        $task = Task::with(['status', 'assignees', 'taskList.statuses', 'taskList.space', 'labels'])->findOrFail($taskId);
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

    /**
     * Find a checklist item that belongs to this task's checklists.
     */
    private function findOwnedChecklistItem(int $itemId): ?TaskChecklistItem
    {
        $item = TaskChecklistItem::with('checklist')->findOrFail($itemId);

        if ($item->checklist->task_id !== $this->taskId) {
            Flux::toast('Item tidak ditemukan dalam tugas ini.', variant: 'danger');

            return null;
        }

        return $item;
    }

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
        $task = Task::with(['assignees', 'taskList'])->findOrFail($this->taskId);
        if (! $task->canBeManagedBy(auth()->user())) {
            Flux::toast('Anda tidak memiliki izin untuk mengubah tugas ini.', variant: 'danger');

            return null;
        }

        return $task;
    }

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

    public function toggleLabel(int $labelId): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        $task->labels()->toggle($labelId);
        $this->dispatch('task-updated');
        $this->broadcastChange();
    }

    public function createLabel(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }

        if (! $this->validateWithToast(['newLabelName' => 'required|min:1|max:100'], [
            'newLabelName.required' => 'Nama label wajib diisi.',
            'newLabelName.max' => 'Nama label maksimal 100 karakter.',
        ])) {
            return;
        }

        $task->load('taskList.space.workspace');
        $workspaceId = $task->taskList->space->workspace_id;

        $label = TaskLabel::create([
            'workspace_id' => $workspaceId,
            'name' => trim($this->newLabelName),
            'color' => $this->newLabelColor,
        ]);

        $task->labels()->attach($label->id);

        $this->reset(['newLabelName', 'newLabelColor', 'showLabelForm']);
        $this->newLabelColor = '#6366f1';

        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Label dibuat dan ditambahkan.', variant: 'success');
    }

    // ─── Comments ──────────────────────────────────────────────────

    public function addComment(): void
    {
        if (! $this->validateWithToast(['newComment' => 'required|min:1|max:5000'], [
            'newComment.required' => 'Komentar wajib diisi.',
            'newComment.max' => 'Komentar maksimal 5000 karakter.',
        ])) {
            return;
        }

        // Verify task exists
        Task::findOrFail($this->taskId);

        TaskComment::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'body' => $this->newComment,
        ]);

        $this->reset('newComment');
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Komentar berhasil ditambahkan.', variant: 'success');
    }

    public function deleteComment(int $commentId): void
    {
        TaskComment::where('id', $commentId)->where('user_id', auth()->id())->delete();
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Komentar berhasil dihapus.', variant: 'danger');
    }

    public function startReply(int $parentCommentId): void
    {
        $this->replyingToCommentId = $parentCommentId;
        $this->replyBody = '';
    }

    public function cancelReply(): void
    {
        $this->replyingToCommentId = null;
        $this->replyBody = '';
    }

    public function addReply(): void
    {
        if (! $this->replyingToCommentId) {
            return;
        }

        if (! $this->validateWithToast(['replyBody' => 'required|min:1|max:5000'], [
            'replyBody.required' => 'Balasan wajib diisi.',
            'replyBody.max' => 'Balasan maksimal 5000 karakter.',
        ])) {
            return;
        }

        $parent = TaskComment::where('id', $this->replyingToCommentId)
            ->where('task_id', $this->taskId)
            ->firstOrFail();

        TaskComment::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'parent_id' => $parent->id,
            'body' => $this->replyBody,
        ]);

        $this->reset(['replyingToCommentId', 'replyBody']);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Balasan berhasil ditambahkan.', variant: 'success');
    }

    public function deleteReply(int $replyId): void
    {
        TaskComment::where('id', $replyId)
            ->where('task_id', $this->taskId)
            ->whereNotNull('parent_id')
            ->where('user_id', auth()->id())
            ->delete();

        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Balasan berhasil dihapus.', variant: 'danger');
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'checklist', 'comments', 'activity'], true)) {
            $this->activeTab = $tab;
        }
    }

    // ─── Subtasks (legacy) ─────────────────────────────────────────

    public function addSubtask(): void
    {
        $parentTask = $this->authorizeManageTask();
        if (! $parentTask) {
            return;
        }
        if (! $this->validateWithToast(['newSubtaskTitle' => 'required|min:1|max:500'], [
            'newSubtaskTitle.required' => 'Judul subtask wajib diisi.',
            'newSubtaskTitle.max' => 'Judul subtask maksimal 500 karakter.',
        ])) {
            return;
        }
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
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Subtask berhasil ditambahkan.', variant: 'success');
    }

    public function toggleSubtaskComplete(int $subtaskId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        // subtaskId is already scoped to parent_id below
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

        $this->dispatch('task-updated');
        $this->broadcastChange();
    }

    public function editSubtaskTitle(int $subtaskId, string $newTitle): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $newTitle = trim($newTitle);
        if (empty($newTitle)) {
            Flux::toast('Judul subtask wajib diisi.', variant: 'warning');

            return;
        }

        Task::where('parent_id', $this->taskId)->findOrFail($subtaskId)->update(['title' => $newTitle]);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Subtask berhasil diperbarui.', variant: 'success');
    }

    public function deleteSubtask(int $subtaskId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        Task::where('parent_id', $this->taskId)->findOrFail($subtaskId)->delete();
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Subtask berhasil dihapus.', variant: 'success');
    }

    public function updatedUploadFiles(): void
    {
        $this->uploadAttachment();
    }

    public function uploadAttachment(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        if (! $this->validateWithToast(['uploadFiles.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'], [
            'uploadFiles.*.max' => 'Ukuran file maksimal 5 MB.',
            'uploadFiles.*.mimes' => 'Format file tidak didukung.',
        ])) {
            $this->reset('uploadFiles');

            return;
        }

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
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('File berhasil diunggah.', variant: 'success');
    }

    public function addLinkAttachment(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }

        if (! $this->validateWithToast(
            ['newLinkUrl' => 'required|url|max:2048', 'newLinkLabel' => 'nullable|max:255'],
            [
                'newLinkUrl.required' => 'URL wajib diisi.',
                'newLinkUrl.url' => 'URL tidak valid.',
                'newLinkUrl.max' => 'URL maksimal 2048 karakter.',
                'newLinkLabel.max' => 'Label maksimal 255 karakter.',
            ]
        )) {
            return;
        }

        $url = trim($this->newLinkUrl);
        $label = trim($this->newLinkLabel) ?: $url;

        TaskAttachment::create([
            'task_id' => $this->taskId,
            'user_id' => auth()->id(),
            'filename' => $label,
            'path' => $url,
            'mime_type' => 'link',
            'size' => 0,
            'is_link' => true,
        ]);

        $this->reset(['newLinkUrl', 'newLinkLabel', 'showLinkForm']);
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Link berhasil ditambahkan.', variant: 'success');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        // Scoped to user_id below
        $attachment = TaskAttachment::where('id', $attachmentId)->where('user_id', auth()->id())->firstOrFail();
        if (! $attachment->is_link) {
            Storage::disk('public')->delete($attachment->path);
        }
        $attachment->delete();
        $this->dispatch('task-updated');
        $this->broadcastChange();
        Flux::toast('Lampiran berhasil dihapus.', variant: 'danger');
    }

    public function addChecklist(): void
    {
        $task = $this->authorizeManageTask();
        if (! $task) {
            return;
        }
        if (! $this->validateWithToast(['newChecklistName' => 'required|min:1|max:200'], [
            'newChecklistName.required' => 'Nama checklist wajib diisi.',
            'newChecklistName.max' => 'Nama checklist maksimal 200 karakter.',
        ])) {
            return;
        }

        $maxPosition = TaskChecklist::where('task_id', $this->taskId)->max('position') ?? -1;

        TaskChecklist::create([
            'task_id' => $this->taskId,
            'name' => trim($this->newChecklistName),
            'position' => $maxPosition + 1,
        ]);

        $this->reset(['newChecklistName', 'showChecklistForm']);
        $this->broadcastChange();
        Flux::toast('Checklist berhasil dibuat.', variant: 'success');
    }

    public function deleteChecklist(int $checklistId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        TaskChecklist::where('task_id', $this->taskId)->findOrFail($checklistId)->delete();
        $this->broadcastChange();
        Flux::toast('Checklist berhasil dihapus.', variant: 'danger');
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
        $this->broadcastChange();
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
        if (! $this->validateWithToast(['newChecklistItemTitle' => 'required|min:1|max:500'], [
            'newChecklistItemTitle.required' => 'Judul item wajib diisi.',
            'newChecklistItemTitle.max' => 'Judul item maksimal 500 karakter.',
        ])) {
            return;
        }

        $checklist = TaskChecklist::where('task_id', $this->taskId)->findOrFail($this->addingItemToChecklistId);
        $maxPosition = $checklist->items()->max('position') ?? -1;

        TaskChecklistItem::create([
            'task_checklist_id' => $checklist->id,
            'title' => trim($this->newChecklistItemTitle),
            'position' => $maxPosition + 1,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['newChecklistItemTitle', 'addingItemToChecklistId']);
        $this->broadcastChange();
        Flux::toast('Item checklist berhasil ditambahkan.', variant: 'success');
    }

    public function toggleChecklistItem(int $itemId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $item = $this->findOwnedChecklistItem($itemId);
        if (! $item) {
            return;
        }
        $item->update(['is_completed' => ! $item->is_completed]);
        $this->broadcastChange();
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
        $item = $this->findOwnedChecklistItem($itemId);
        if (! $item) {
            return;
        }
        $item->update(['title' => $title]);
        $this->broadcastChange();
        Flux::toast('Item checklist berhasil diperbarui.', variant: 'success');
    }

    public function deleteChecklistItem(int $itemId): void
    {
        if (! $this->authorizeManageTask()) {
            return;
        }
        $item = $this->findOwnedChecklistItem($itemId);
        if (! $item) {
            return;
        }
        $item->delete();

        if ($this->activeChecklistItemId === $itemId) {
            $this->closeChecklistItemPanel();
        }

        $this->broadcastChange();
        Flux::toast('Item checklist berhasil dihapus.', variant: 'danger');
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
        $this->showItemLinkForm = false;
        $this->newItemLinkUrl = '';
        $this->newItemLinkLabel = '';
    }

    public function updateChecklistItemDueDate(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }
        TaskChecklistItem::findOrFail($this->activeChecklistItemId)
            ->update(['due_date' => $this->activeItemDueDate ?: null]);
        $this->broadcastChange();
        Flux::toast('Tanggal jatuh tempo checklist item berhasil diperbarui.', variant: 'success');
    }

    public function clearChecklistItemDueDate(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }
        $this->activeItemDueDate = '';
        TaskChecklistItem::findOrFail($this->activeChecklistItemId)
            ->update(['due_date' => null]);
        $this->broadcastChange();
        Flux::toast('Tanggal jatuh tempo checklist item berhasil dihapus.', variant: 'success');
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
        $this->broadcastChange();
        Flux::toast('Assignees checklist item berhasil diperbarui.', variant: 'success');
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

        if (! $this->validateWithToast(['activeItemFiles.*' => 'file|max:5120|mimes:jpg,jpeg,png,gif,webp,svg,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'], [
            'activeItemFiles.*.max' => 'Ukuran file maksimal 5 MB.',
            'activeItemFiles.*.mimes' => 'Format file tidak didukung.',
        ])) {
            $this->activeItemFiles = [];

            return;
        }

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
        $this->broadcastChange();
        Flux::toast('File berhasil diunggah.', variant: 'success');
    }

    public function addChecklistItemLink(): void
    {
        if (! $this->authorizeManageTask() || ! $this->activeChecklistItemId) {
            return;
        }

        if (! $this->validateWithToast(
            ['newItemLinkUrl' => 'required|url|max:2048', 'newItemLinkLabel' => 'nullable|max:255'],
            [
                'newItemLinkUrl.required' => 'URL wajib diisi.',
                'newItemLinkUrl.url' => 'URL tidak valid.',
                'newItemLinkUrl.max' => 'URL maksimal 2048 karakter.',
                'newItemLinkLabel.max' => 'Label maksimal 255 karakter.',
            ]
        )) {
            return;
        }

        $item = TaskChecklistItem::with('checklist')->findOrFail($this->activeChecklistItemId);

        $url = trim($this->newItemLinkUrl);
        $label = trim($this->newItemLinkLabel) ?: $url;

        TaskAttachment::create([
            'task_id' => $item->checklist->task_id,
            'task_checklist_item_id' => $item->id,
            'user_id' => auth()->id(),
            'filename' => $label,
            'path' => $url,
            'mime_type' => 'link',
            'size' => 0,
            'is_link' => true,
        ]);

        $this->reset(['newItemLinkUrl', 'newItemLinkLabel', 'showItemLinkForm']);
        $this->broadcastChange();
        Flux::toast('Link berhasil ditambahkan.', variant: 'success');
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
        if (! $attachment->is_link) {
            Storage::disk('public')->delete($attachment->path);
        }
        $attachment->delete();
        $this->broadcastChange();
        Flux::toast('Lampiran berhasil dihapus.', variant: 'danger');
    }

    // time tracking
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
        Flux::toast('Penghitung waktu dimulai.', variant: 'success');
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
            Flux::toast('Penghitung waktu dihentikan.', variant: 'success');
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
                'status',
                'assignees',
                'labels',
                'creator',
                'taskList.statuses',
                'taskList.space.workspace',
            ])->find($this->taskId);

            if ($task) {
                $canManage = $task->canBeManagedBy(auth()->user());
                $comments = $task->comments()->with(['user', 'replies.user'])->get();
                $subtasks = $task->subtasks()->with('status')->get();
                $checklists = $task->checklists()->with([
                    'items' => fn ($q) => $q->orderBy('position')->with(['assignees', 'attachments']),
                ])->get();
                $attachments = $task->attachments()
                    ->whereNull('task_checklist_item_id')
                    ->latest()->get();
                $activities = $task->activities()->with('user')->latest()->take(20)->get();
                $timeEntries = $task->timeTrackings()->with('user')->latest()->take(30)->get();
                $statuses = $task->taskList->statuses->sortBy('position')->values();
                $totalTimeSeconds = $task->timeTrackings()->sum('duration_seconds');
                $workspaceUsers = $task->taskList->members()->orderBy('name')->get();
                $allLabels = $this->workspaceId
                    ? TaskLabel::where('workspace_id', $this->workspaceId)->orderBy('name')->get()
                    : collect();

                if ($this->activeChecklistItemId) {
                    $activeItemAttachments = TaskAttachment::where('task_checklist_item_id', $this->activeChecklistItemId)
                        ->latest()->get();
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
