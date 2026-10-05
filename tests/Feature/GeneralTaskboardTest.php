<?php

use App\Livewire\GeneralTaskboard;
use App\Models\Task;
use App\Models\User;
use App\Models\Workspace;
use Livewire\Livewire;

test('taskboard page requires authentication', function () {
    $this->get(route('general-taskboard'))->assertRedirect(route('login'));
});

test('taskboard renders for authenticated user', function () {
    $user = User::factory()->create(['role' => 'member', 'position' => 'Kreatif']);

    $this->actingAs($user)
        ->get(route('general-taskboard'))
        ->assertOk();
});

test('taskboard shows accessible lists grouped by space', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0, 'color' => '#3b82f6', 'icon' => 'code-bracket']);
    $space->lists()->create(['name' => 'Sprint 1', 'position' => 0]);
    $space->lists()->create(['name' => 'Sprint 2', 'position' => 1]);

    Livewire::actingAs($owner)
        ->test(GeneralTaskboard::class)
        ->assertSee('Engineering')
        ->assertSee('Sprint 1')
        ->assertSee('Sprint 2');
});

test('taskboard shows lists from multiple spaces', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space1 = $workspace->spaces()->create(['name' => 'Frontend', 'position' => 0, 'color' => '#3b82f6', 'icon' => 'code-bracket']);
    $space2 = $workspace->spaces()->create(['name' => 'Backend', 'position' => 1, 'color' => '#10b981', 'icon' => 'server']);

    $space1->lists()->create(['name' => 'UI Tasks', 'position' => 0]);
    $space2->lists()->create(['name' => 'API Tasks', 'position' => 0]);

    Livewire::actingAs($owner)
        ->test(GeneralTaskboard::class)
        ->assertSee('Frontend')
        ->assertSee('Backend')
        ->assertSee('UI Tasks')
        ->assertSee('API Tasks');
});

test('taskboard search filters lists by name', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0, 'color' => '#3b82f6', 'icon' => 'code-bracket']);
    $space->lists()->create(['name' => 'Sprint Alpha', 'position' => 0]);
    $space->lists()->create(['name' => 'Sprint Beta', 'position' => 1]);

    Livewire::actingAs($owner)
        ->test(GeneralTaskboard::class)
        ->set('search', 'Alpha')
        ->assertSee('Sprint Alpha')
        ->assertDontSee('Sprint Beta');
});

test('taskboard shows task count per list', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Ops', 'position' => 0, 'color' => '#f59e0b', 'icon' => 'cog']);
    $list = $space->lists()->create(['name' => 'Deployments', 'position' => 0]);

    $status = $list->statuses()->create(['name' => 'Open', 'position' => 0, 'type' => 'open', 'color' => '#6b7280']);
    Task::create(['task_list_id' => $list->id, 'task_status_id' => $status->id, 'title' => 'Task 1', 'created_by' => $owner->id]);
    Task::create(['task_list_id' => $list->id, 'task_status_id' => $status->id, 'title' => 'Task 2', 'created_by' => $owner->id]);

    Livewire::actingAs($owner)
        ->test(GeneralTaskboard::class)
        ->assertSee('Deployments')
        ->assertSee('2 tasks');
});

test('taskboard only shows spaces accessible to the authenticated user', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $outsider = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Private Space', 'position' => 0, 'color' => '#3b82f6', 'icon' => 'lock-closed']);
    $list = $space->lists()->create(['name' => 'Internal List', 'position' => 0]);

    Livewire::actingAs($outsider)
        ->test(GeneralTaskboard::class)
        ->assertDontSee('Private Space')
        ->assertDontSee('Internal List');

    Livewire::actingAs($owner)
        ->test(GeneralTaskboard::class)
        ->assertSee('Private Space')
        ->assertSee('Internal List');

    $list->members()->attach($outsider->id);
    Livewire::actingAs($outsider)
        ->test(GeneralTaskboard::class)
        ->assertSee('Private Space')
        ->assertSee('Internal List');
});

test('non-assigned member cannot edit task but can view it', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $viewer = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0, 'color' => '#3b82f6', 'icon' => 'code-bracket']);
    $list = $space->lists()->create(['name' => 'Tasks', 'position' => 0]);
    $status = $list->statuses()->create(['name' => 'Open', 'position' => 0, 'type' => 'open', 'color' => '#6b7280']);
    $task = Task::create(['task_list_id' => $list->id, 'task_status_id' => $status->id, 'title' => 'Some Task', 'created_by' => $owner->id]);
    $task->assignees()->attach($owner->id);

    expect($task->canBeManagedBy($viewer))->toBeFalse();
    expect($task->canBeManagedBy($owner))->toBeTrue();
});

test('manager can edit any task in a list they are a member of', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $manager = User::factory()->create(['role' => 'manager']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0, 'color' => '#3b82f6', 'icon' => 'code-bracket']);

    $list = $space->lists()->create(['name' => 'Tasks', 'position' => 0]);
    $list->members()->attach($manager->id);

    $status = $list->statuses()->create(['name' => 'Open', 'position' => 0, 'type' => 'open', 'color' => '#6b7280']);
    $task = Task::create(['task_list_id' => $list->id, 'task_status_id' => $status->id, 'title' => 'Some Task', 'created_by' => $owner->id]);

    expect($task->canBeManagedBy($manager))->toBeTrue();
});

test('manager cannot edit tasks in lists they are not involved in', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $manager = User::factory()->create(['role' => 'manager']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0, 'color' => '#3b82f6', 'icon' => 'code-bracket']);
    $list = $space->lists()->create(['name' => 'Tasks', 'position' => 0]);
    $status = $list->statuses()->create(['name' => 'Open', 'position' => 0, 'type' => 'open', 'color' => '#6b7280']);
    $task = Task::create(['task_list_id' => $list->id, 'task_status_id' => $status->id, 'title' => 'Some Task', 'created_by' => $owner->id]);

    expect($task->canBeManagedBy($manager))->toBeFalse();
});

test('the deadline calendar colors tasks by status, not by space', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0, 'color' => '#abcdef', 'icon' => 'code-bracket']);
    $list = $space->lists()->create(['name' => 'Tasks', 'position' => 0]);
    $list->members()->attach($owner->id);

    $status = $list->statuses()->create(['name' => 'Open', 'position' => 0, 'type' => 'open', 'color' => '#123456']);

    Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'Deadline Task',
        'due_date' => today()->toDateString(),
        'created_by' => $owner->id,
    ]);

    Livewire::actingAs($owner)
        ->test(GeneralTaskboard::class)
        ->set('activeTab', 'calendar')
        ->assertSee('Deadline Task')
        ->assertSee('#123456')
        ->assertDontSee('#abcdef');
});
