<?php

namespace App\Livewire\Project;

use App\Models\Project\Task;
use App\Models\Project\TaskLabel;
use App\Models\Project\TaskComment;
use App\Models\Project\TaskActivity;
use App\Models\Project\TaskAttachment;
use App\Models\Project\TimeTracking;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Flux\Flux;

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

	// Subtasks
	public string $newSubtaskTitle = '';
	public bool $showSubtaskForm = false;

	// Attachments
	public $uploadFile = null;

	// Labels
	public bool $showLabelForm = false;
	public string $newLabelName = '';
	public string $newLabelColor = '#6366f1';

	// Timer
	public ?int $activeTimerId = null;

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

		// Check active timer
		$activeTimer = TimeTracking::where('task_id', $taskId)
			->where('user_id', auth()->id())
			->whereNull('stopped_at')
			->first();
		$this->activeTimerId = $activeTimer?->id;
	}

	private function authorizeManageTask(): bool
	{
		$task = Task::findOrFail($this->taskId);
		if (!$task->canBeManagedBy(auth()->user())) {
			Flux::toast('You do not have permission to modify this task.', variant: 'danger');
			return false;
		}
		return true;
	}

	public function saveTitle(): void
	{
		if (!$this->authorizeManageTask()) return;
		$task = Task::findOrFail($this->taskId);
		$task->update(['title' => $this->taskTitle]);
		$this->dispatch('task-updated');
	}

	public function saveDescription(): void
	{
		if (!$this->authorizeManageTask()) return;
		$task = Task::findOrFail($this->taskId);
		$task->update(['description' => $this->taskDescription]);
		Flux::toast('Description saved.', variant: 'success');
	}

	public function updateStatus(int $statusId): void
	{
		if (!$this->authorizeManageTask()) return;
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
		Flux::toast('Status updated.', variant: 'success');
	}

	public function updatePriority(string $priority): void
	{
		if (!$this->authorizeManageTask()) return;
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
		Flux::toast('Priority updated.', variant: 'success');
	}

	public function updateDueDate(): void
	{
		if (!$this->authorizeManageTask()) return;
		$task = Task::findOrFail($this->taskId);
		$task->update(['due_date' => $this->taskDueDate ?: null]);
		$this->dispatch('task-updated');
		Flux::toast('Due date updated.', variant: 'success');
	}

	public function updateAssignees(): void
	{
		if (!$this->authorizeManageTask()) return;
		$task = Task::findOrFail($this->taskId);
		$task->assignees()->sync($this->taskAssigneeIds);

		$names = User::whereIn('id', $this->taskAssigneeIds)->pluck('name')->join(', ') ?: 'Unassigned';
		TaskActivity::create([
			'task_id' => $task->id,
			'user_id' => auth()->id(),
			'type' => 'assignee_changed',
			'new_value' => $names,
		]);

		$this->dispatch('task-updated');
		Flux::toast('Assignees updated.', variant: 'success');
	}

	// ─── Labels ────────────────────────────────────────────────────

	public function toggleLabel(int $labelId): void
	{
		if (!$this->authorizeManageTask()) return;
		$task = Task::findOrFail($this->taskId);
		$task->labels()->toggle($labelId);
		$this->dispatch('task-updated');
	}

	public function createLabel(): void
	{
		if (!$this->authorizeManageTask()) return;

		$this->validate([
			'newLabelName' => 'required|min:1|max:100',
		]);

		$task = Task::with('taskList.space.workspace')->findOrFail($this->taskId);
		$workspaceId = $task->taskList->space->workspace->id;

		$label = TaskLabel::create([
			'workspace_id' => $workspaceId,
			'name' => trim($this->newLabelName),
			'color' => $this->newLabelColor,
		]);

		// Auto-attach to current task
		$task->labels()->attach($label->id);

		$this->reset(['newLabelName', 'newLabelColor', 'showLabelForm']);
		$this->newLabelColor = '#6366f1';

		$this->dispatch('task-updated');
		Flux::toast('Label created & attached.', variant: 'success');
	}

	// Comments
	public function addComment(): void
	{
		$this->validate(['newComment' => 'required|min:1']);

		TaskComment::create([
			'task_id' => $this->taskId,
			'user_id' => auth()->id(),
			'body' => $this->newComment,
		]);

		$this->reset('newComment');
		Flux::toast('Comment added.', variant: 'success');
	}

	public function deleteComment(int $commentId): void
	{
		TaskComment::where('id', $commentId)->where('user_id', auth()->id())->delete();
		Flux::toast('Comment deleted.', variant: 'danger');
	}

	// Subtasks
	public function addSubtask(): void
	{
		if (!$this->authorizeManageTask()) return;
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
		Flux::toast('Subtask added.', variant: 'success');
	}

	public function toggleSubtaskComplete(int $subtaskId): void
	{
		if (!$this->authorizeManageTask()) return;
		$subtask = Task::where('parent_id', $this->taskId)->findOrFail($subtaskId);
		$subtask->update(['is_completed' => !$subtask->is_completed]);

		// If completed, move to last status (Done)
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
		if (!$this->authorizeManageTask()) return;
		$newTitle = trim($newTitle);
		if (empty($newTitle)) {
			Flux::toast('Subtask title cannot be empty.', variant: 'warning');
			return;
		}
		
		$subtask = Task::where('parent_id', $this->taskId)->findOrFail($subtaskId);
		$subtask->update(['title' => $newTitle]);
		Flux::toast('Subtask updated.', variant: 'success');
	}

	public function deleteSubtask(int $subtaskId): void
	{
		if (!$this->authorizeManageTask()) return;
		$subtask = Task::where('parent_id', $this->taskId)->findOrFail($subtaskId);
		$subtask->delete();
		Flux::toast('Subtask deleted.', variant: 'success');
	}

	// Attachments
	public function uploadAttachment(): void
	{
		if (!$this->authorizeManageTask()) return;
		$this->validate(['uploadFile' => 'required|file|max:10240']);

		$path = $this->uploadFile->store('task-attachments', 'public');

		TaskAttachment::create([
			'task_id' => $this->taskId,
			'user_id' => auth()->id(),
			'filename' => $this->uploadFile->getClientOriginalName(),
			'path' => $path,
			'mime_type' => $this->uploadFile->getMimeType(),
			'size' => $this->uploadFile->getSize(),
		]);

		$this->reset('uploadFile');
		Flux::toast('File uploaded.', variant: 'success');
	}

	public function deleteAttachment(int $attachmentId): void
	{
		if (!$this->authorizeManageTask()) return;
		TaskAttachment::where('id', $attachmentId)->where('user_id', auth()->id())->delete();
		Flux::toast('Attachment deleted.', variant: 'danger');
	}

	// Time tracking
	public function startTimer(): void
	{
		if (!$this->authorizeManageTask()) return;
		$timer = TimeTracking::create([
			'task_id' => $this->taskId,
			'user_id' => auth()->id(),
			'started_at' => now(),
		]);
		$this->activeTimerId = $timer->id;
		Flux::toast('Timer started.', variant: 'success');
	}

	public function stopTimer(): void
	{
		if (!$this->authorizeManageTask()) return;
		if ($this->activeTimerId) {
			$timer = TimeTracking::findOrFail($this->activeTimerId);
			$now = now();
			$timer->update([
				'stopped_at' => $now,
				'duration_seconds' => $timer->started_at->diffInSeconds($now),
			]);
			$this->activeTimerId = null;
			Flux::toast('Timer stopped.', variant: 'success');
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
		$attachments = collect();
		$activities = collect();
		$timeEntries = collect();
		$statuses = collect();
		$totalTimeSeconds = 0;
		$workspaceUsers = collect();
		$canManage = false;

		$allLabels = collect();

		if ($this->taskId) {
			$task = Task::with(['status', 'assignees', 'labels', 'creator', 'taskList.statuses.tasks', 'taskList.space.workspace'])->find($this->taskId);
			if ($task) {
				$canManage = $task->canBeManagedBy(auth()->user());
				$comments = $task->comments()->with(['user', 'replies.user'])->get();
				$subtasks = $task->subtasks()->with('status')->get();
				$attachments = $task->attachments()->with('user')->latest()->get();
				$activities = $task->activities()->with('user')->latest()->take(20)->get();
				$timeEntries = $task->timeTrackings()->where('user_id', auth()->id())->latest()->take(10)->get();
				$statuses = $task->taskList->statuses()->orderBy('position')->get();
				$totalTimeSeconds = $task->timeTrackings()->where('user_id', auth()->id())->sum('duration_seconds');
				$workspaceUsers = User::all();
				$allLabels = TaskLabel::where('workspace_id', $task->taskList->space->workspace->id)->orderBy('name')->get();
			}
		}

		return view('livewire.project.task-detail', [
			'task' => $task,
			'canManage' => $canManage,
			'comments' => $comments,
			'subtasks' => $subtasks,
			'attachments' => $attachments,
			'activities' => $activities,
			'timeEntries' => $timeEntries,
			'statuses' => $statuses,
			'totalTimeSeconds' => $totalTimeSeconds,
			'workspaceUsers' => $workspaceUsers,
			'allLabels' => $allLabels,
		]);
	}
}
