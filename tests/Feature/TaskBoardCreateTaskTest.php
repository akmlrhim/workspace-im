<?php

use App\Livewire\TaskBoard;
use App\Livewire\TaskFormModal;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Livewire\Livewire;

test('the create-task modal opens with the chosen column preselected and creates the task there', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
    $doneStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Done', 'position' => 1, 'type' => 'closed']);

    Livewire::actingAs($owner)
        ->test(TaskFormModal::class, ['space' => $space, 'taskList' => $list])
        ->call('openCreateForm', $doneStatus->id)
        ->assertSet('showTaskForm', true)
        ->assertSet('formTaskStatusId', $doneStatus->id)
        ->set('formTaskTitle', 'Tugas baru dari modal')
        ->call('saveTask')
        ->assertSet('showTaskForm', false)
        ->assertDispatched('task-updated');

    $task = Task::where('task_list_id', $list->id)->where('title', 'Tugas baru dari modal')->first();

    expect($task)->not->toBeNull()
        ->and($task->task_status_id)->toBe($doneStatus->id)
        ->and($task->assignees()->pluck('users.id')->all())->toContain($owner->id);
});

test('a task created via the modal is appended after existing tasks', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $openStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    $existing = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $openStatus->id,
        'title' => 'Tugas lama',
        'priority' => 'normal',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    Livewire::actingAs($owner)
        ->test(TaskFormModal::class, ['space' => $space, 'taskList' => $list])
        ->call('openCreateForm', $openStatus->id)
        ->set('formTaskTitle', 'Tugas paling baru')
        ->call('saveTask');

    $new = Task::where('title', 'Tugas paling baru')->first();

    expect($new->position)->toBe(1)
        ->and($existing->fresh()->position)->toBe(0);
});

test('search filters board tasks and shows suggestions scoped to the current list', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);

    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $siblingList = $space->lists()->create(['name' => 'Backlog', 'position' => 1]);

    $openStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
    $siblingStatus = TaskStatus::create(['task_list_id' => $siblingList->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    $makeTask = fn (int $listId, int $statusId, string $title) => Task::create([
        'task_list_id' => $listId,
        'task_status_id' => $statusId,
        'title' => $title,
        'priority' => 'normal',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    $makeTask($list->id, $openStatus->id, 'Rapat internal tim');
    $makeTask($list->id, $openStatus->id, 'Deploy aplikasi');
    $makeTask($siblingList->id, $siblingStatus->id, 'Rapat sprint planning');

    $component = Livewire::actingAs($owner)
        ->test(TaskBoard::class, ['space' => $space, 'taskList' => $list])
        ->set('search', 'Rapat')

        ->assertSee('Rapat internal tim')
        ->assertDontSee('Deploy aplikasi')

        ->assertDontSee('Rapat sprint planning');

    $suggestions = $component->instance()->searchSuggestions->pluck('title');

    expect($suggestions)->toContain('Rapat internal tim')
        ->not->toContain('Rapat sprint planning', 'Deploy aplikasi');
});

test('the task form modal is a named flux modal so open and close never fight', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    $html = Livewire::actingAs($owner)
        ->test(TaskFormModal::class, ['space' => $space, 'taskList' => $list])
        ->html();

    expect($html)
        ->toContain('name="task-form-modal"')
        ->not->toContain('wire:model="showTaskForm"')
        ->toContain('@task-form-modal-open.window');
});

test('opening the create form dispatches the named modal open event', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    Livewire::actingAs($owner)
        ->test(TaskFormModal::class, ['space' => $space, 'taskList' => $list])
        ->call('openCreateForm')
        ->assertSet('showTaskForm', true)
        ->assertDispatched('task-form-modal-open');
});

test('saving a task closes the modal through the named modal close event', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    Livewire::actingAs($owner)
        ->test(TaskFormModal::class, ['space' => $space, 'taskList' => $list])
        ->call('openCreateForm')
        ->set('formTaskTitle', 'Tugas dengan modal tertutup rapi')
        ->call('saveTask')
        ->assertSet('showTaskForm', false)
        ->assertDispatched('task-form-modal-close');
});

test('closing the form resets the editing state so a stale task is never saved', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $openStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $openStatus->id,
        'title' => 'Tugas asli',
        'priority' => 'normal',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    Livewire::actingAs($owner)
        ->test(TaskFormModal::class, ['space' => $space, 'taskList' => $list])
        ->call('openEditForm', $task->id)
        ->assertSet('editingTaskId', $task->id)
        ->call('closeForm')
        ->assertSet('editingTaskId', null)
        ->assertSet('formTaskTitle', '')
        ->assertSet('showTaskForm', false);
});
