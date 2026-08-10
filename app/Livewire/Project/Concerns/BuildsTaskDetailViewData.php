<?php

namespace App\Livewire\Project\Concerns;

use App\Models\Project\Task;
use App\Models\Project\TaskAttachment;
use App\Models\Project\TaskLabel;

/**
 * Assembles every relation the task detail panel renders.
 */
trait BuildsTaskDetailViewData
{
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
            return $this->emptyViewData();
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

    /**
     * Blank shape for a missing/deleted task, so the view never hits null relations.
     *
     * @return array<string, mixed>
     */
    private function emptyViewData(): array
    {
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
}
