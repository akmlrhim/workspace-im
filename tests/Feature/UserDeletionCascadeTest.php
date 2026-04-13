<?php

use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskList;
use App\Models\Project\TaskStatus;
use App\Models\Project\Workspace;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createWorkspaceWithTask(User $owner, ?User $taskCreator = null): array
{
    $workspace = Workspace::create([
        'name' => 'Test Workspace',
        'owner_id' => $owner->id,
    ]);

    $space = Space::create([
        'workspace_id' => $workspace->id,
        'name' => 'Test Space',
    ]);

    $taskList = TaskList::create([
        'space_id' => $space->id,
        'name' => 'Test List',
    ]);

    $status = TaskStatus::create([
        'task_list_id' => $taskList->id,
        'name' => 'To Do',
    ]);

    $task = Task::create([
        'task_list_id' => $taskList->id,
        'task_status_id' => $status->id,
        'title' => 'Test Task',
        'created_by' => ($taskCreator ?? $owner)->id,
    ]);

    return compact('workspace', 'space', 'taskList', 'status', 'task');
}

test('deleting a user nullifies workspace owner_id instead of deleting workspace', function () {
    $owner = User::factory()->create();
    $data = createWorkspaceWithTask($owner);

    $owner->delete();

    $workspace = Workspace::find($data['workspace']->id);
    expect($workspace)->not->toBeNull();
    expect($workspace->owner_id)->toBeNull();
});

test('deleting a user nullifies task created_by instead of deleting task', function () {
    $owner = User::factory()->create();
    $creator = User::factory()->create();
    $data = createWorkspaceWithTask($owner, $creator);

    $creator->delete();

    $task = Task::find($data['task']->id);
    expect($task)->not->toBeNull();
    expect($task->created_by)->toBeNull();
});

test('workspace spaces and tasks remain intact after owner deletion', function () {
    $owner = User::factory()->create();
    $data = createWorkspaceWithTask($owner);

    $owner->delete();

    expect(Space::find($data['space']->id))->not->toBeNull();
    expect(TaskList::find($data['taskList']->id))->not->toBeNull();
    expect(Task::find($data['task']->id))->not->toBeNull();
});
