<?php

use App\Livewire\TaskBoard;
use App\Livewire\TaskDetail;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\TaskStatus;
use App\Models\Workspace;
use App\Models\User;
use Livewire\Livewire;

test('moving a kanban card notifies an open task detail modal about the new status', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $openStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
    $doneStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Done', 'position' => 1, 'type' => 'closed']);
    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $openStatus->id,
        'title' => 'Sync modal status',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    Livewire::actingAs($owner)
        ->test(TaskBoard::class, ['space' => $space, 'taskList' => $list])
        ->call('moveTask', $task->id, $doneStatus->id, [$task->id])
        ->assertDispatched('task-status-updated-from-board');

    expect($task->refresh()->task_status_id)->toBe($doneStatus->id)
        ->and(TaskActivity::where('task_id', $task->id)->where('type', 'status_changed')->exists())->toBeTrue();
});

test('task detail accepts kanban status sync events for its own task only', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $openStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
    $doneStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Done', 'position' => 1, 'type' => 'closed']);
    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $openStatus->id,
        'title' => 'Detail modal task',
        'created_by' => $owner->id,
    ]);
    $otherTask = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $openStatus->id,
        'title' => 'Other task',
        'created_by' => $owner->id,
    ]);

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->assertSet('taskStatusId', $openStatus->id)
        ->call('syncStatusFromBoard', $otherTask->id, $doneStatus->id)
        ->assertSet('taskStatusId', $openStatus->id)
        ->call('syncStatusFromBoard', $task->id, $doneStatus->id)
        ->assertSet('taskStatusId', $doneStatus->id);
});
