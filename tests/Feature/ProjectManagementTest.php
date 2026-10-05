<?php

use App\Models\Space;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;

test('project management models generate a uuid on creation', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Design', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Backlog', 'position' => 0]);
    $status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);
    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'Example',
        'created_by' => $owner->id,
    ]);

    expect($workspace->uuid)->not->toBeEmpty();
    expect($space->uuid)->not->toBeEmpty();
    expect($list->uuid)->not->toBeEmpty();
    expect($status->uuid)->not->toBeEmpty();
    expect($task->uuid)->not->toBeEmpty();
});

test('manager can manage every task in a list they are a member of without being assigned', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $manager = User::factory()->create(['role' => 'manager']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Sales', 'position' => 0]);

    $list = $space->lists()->create(['name' => 'Pipeline', 'position' => 0]);
    $list->members()->attach($manager->id);

    $status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'Close deal',
        'created_by' => $owner->id,
    ]);
    $task->load('assignees', 'taskList.space.workspace');

    expect($task->canBeManagedBy($manager))->toBeTrue();
    expect($manager->canManageAllProjects())->toBeFalse();
});

test('manager cannot manage tasks in spaces they are not involved in', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $manager = User::factory()->create(['role' => 'manager']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Private', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Internal', 'position' => 0]);
    $status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'Secret',
        'created_by' => $owner->id,
    ]);
    $task->load('assignees', 'taskList.space.workspace');

    expect($task->canBeManagedBy($manager))->toBeFalse();
});

test('accessible spaces scope grants administrators access to every space but restricts managers', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $outsider = User::factory()->create(['role' => 'member']);
    $administrator = User::factory()->create(['role' => 'administrator']);
    $manager = User::factory()->create(['role' => 'manager']);
    $involvedManager = User::factory()->create(['role' => 'manager']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Private', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Private List', 'position' => 0]);
    $list->members()->attach($involvedManager->id);

    expect(Space::accessibleBy($outsider->id)->count())->toBe(0);
    expect(Space::accessibleBy($administrator->id)->count())->toBe(1);
    expect(Space::accessibleBy($manager->id)->count())->toBe(0);
    expect(Space::accessibleBy($involvedManager->id)->count())->toBe(1);
});

test('list can be moved between spaces', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);

    $spaceA = $workspace->spaces()->create(['name' => 'Space A', 'position' => 0]);
    $spaceB = $workspace->spaces()->create(['name' => 'Space B', 'position' => 1]);

    $listToMove = $spaceA->lists()->create(['name' => 'List A', 'position' => 0]);
    $existingInTarget = $spaceB->lists()->create(['name' => 'List B', 'position' => 0]);

    $targetPosition = ($spaceB->lists()->max('position') ?? -1) + 1;

    $listToMove->update([
        'space_id' => $spaceB->id,
        'position' => $targetPosition,
    ]);

    expect($listToMove->fresh()->space_id)->toBe($spaceB->id);
    expect($listToMove->fresh()->position)->toBeGreaterThan($existingInTarget->position);
});
