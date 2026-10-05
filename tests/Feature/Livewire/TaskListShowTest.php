<?php

use App\Livewire\TaskListShow;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Livewire\Livewire;

function makeListWithStatuses(): array
{
    $owner = User::factory()->create(['role' => 'administrator']);
    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Space', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'List', 'position' => 0]);

    $activeStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'To Do', 'position' => 0, 'type' => 'active']);
    $noteStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Note', 'position' => 1, 'type' => 'active']);

    return [$owner, $space, $list, $activeStatus, $noteStatus];
}

test('tasks with Note status are excluded from task list', function () {
    [$owner, $space, $list, $activeStatus, $noteStatus] = makeListWithStatuses();

    $visibleTask = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $activeStatus->id,
        'title' => 'Visible Task',
        'created_by' => $owner->id,
    ]);

    $noteTask = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $noteStatus->id,
        'title' => 'Note Task',
        'created_by' => $owner->id,
    ]);

    $taskIds = Livewire::actingAs($owner)
        ->test(TaskListShow::class, ['space' => $space, 'taskList' => $list])
        ->instance()
        ->tasks
        ->pluck('id');

    expect($taskIds)->toContain($visibleTask->id)
        ->and($taskIds)->not->toContain($noteTask->id);
});

test('tasks with lowercase note status are also excluded from task list', function () {
    [$owner, $space, $list] = makeListWithStatuses();

    $lowercaseNoteStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'note', 'position' => 2, 'type' => 'active']);

    $noteTask = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $lowercaseNoteStatus->id,
        'title' => 'Lowercase Note Task',
        'created_by' => $owner->id,
    ]);

    $taskIds = Livewire::actingAs($owner)
        ->test(TaskListShow::class, ['space' => $space, 'taskList' => $list])
        ->instance()
        ->tasks
        ->pluck('id');

    expect($taskIds)->not->toContain($noteTask->id);
});
