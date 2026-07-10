<?php

use App\Models\Project\Task;
use App\Models\Project\TaskStatus;
use App\Models\Project\Workspace;
use App\Models\User;

function makeCompletedAtContext(): array
{
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Design', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Backlog', 'position' => 0]);
    $open = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);
    $closed = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Done', 'position' => 1, 'type' => 'closed']);

    return compact('owner', 'list', 'open', 'closed');
}

function makeTask(array $ctx, int $statusId): Task
{
    return Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $statusId,
        'title' => 'Task',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);
}

test('completed_at is set when a task moves to a closed status', function () {
    $ctx = makeCompletedAtContext();

    $task = makeTask($ctx, $ctx['open']->id);
    expect($task->completed_at)->toBeNull();

    $task->update(['task_status_id' => $ctx['closed']->id]);

    expect($task->fresh()->completed_at)->not->toBeNull();
});

test('completed_at is cleared when a task is reopened', function () {
    $ctx = makeCompletedAtContext();

    $task = makeTask($ctx, $ctx['closed']->id);
    expect($task->completed_at)->not->toBeNull();

    $task->update(['task_status_id' => $ctx['open']->id]);

    expect($task->fresh()->completed_at)->toBeNull();
});

test('completed_at is preserved when moving between two closed statuses', function () {
    $ctx = makeCompletedAtContext();
    $archived = TaskStatus::create(['task_list_id' => $ctx['list']->id, 'name' => 'Archived', 'position' => 2, 'type' => 'closed']);

    $task = makeTask($ctx, $ctx['closed']->id);
    $completedAt = $task->fresh()->completed_at;

    $task->update(['task_status_id' => $archived->id]);

    expect($task->fresh()->completed_at->timestamp)->toBe($completedAt->timestamp);
});
