<?php

use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\TimeTracking;
use App\Models\User;
use App\Models\Workspace;

test('time tracking entries from all users are visible on a task', function () {
    $owner = User::factory()->create(['role' => 'member', 'position' => 'Kreatif']);
    $memberA = User::factory()->create(['role' => 'member', 'position' => 'Kreatif']);
    $memberB = User::factory()->create(['role' => 'member', 'position' => 'Kreatif']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);

    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'Build feature',
        'created_by' => $owner->id,
    ]);

    TimeTracking::create([
        'task_id' => $task->id,
        'user_id' => $memberA->id,
        'started_at' => now()->subHours(2),
        'stopped_at' => now()->subHour(),
        'duration_seconds' => 3600,
    ]);
    TimeTracking::create([
        'task_id' => $task->id,
        'user_id' => $memberB->id,
        'started_at' => now()->subMinutes(30),
        'stopped_at' => now(),
        'duration_seconds' => 1800,
    ]);

    $entries = $task->timeTrackings()->with('user')->latest()->get();

    expect($entries)->toHaveCount(2);
    expect($entries->pluck('user_id')->sort()->values()->toArray())
        ->toBe(collect([$memberA->id, $memberB->id])->sort()->values()->toArray());

    foreach ($entries as $entry) {
        expect($entry->relationLoaded('user'))->toBeTrue();
        expect($entry->user)->not->toBeNull();
    }

    $totalSeconds = $task->timeTrackings()->sum('duration_seconds');
    expect($totalSeconds)->toBe(5400);
});

test('member assigned to a task can manage that task', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $member = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);

    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'Assigned task',
        'created_by' => $owner->id,
    ]);

    $task->assignees()->attach($member->id);
    $task->load('assignees', 'taskList.space.workspace');

    expect($task->canBeManagedBy($member))->toBeTrue();
});

test('member not assigned to a task cannot manage that task', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $member = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);

    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'Other task',
        'created_by' => $owner->id,
    ]);
    $task->load('assignees', 'taskList.space.workspace');

    expect($task->canBeManagedBy($member))->toBeFalse();
});

test('list member can see board even without specific task assignment', function () {
    $owner = User::factory()->create(['role' => 'member', 'position' => 'Kreatif']);
    $member = User::factory()->create(['role' => 'member', 'position' => 'Kreatif']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Dev', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $list->createDefaultStatuses();

    $list->members()->attach($member->id);

    expect($list->members()->where('users.id', $member->id)->exists())->toBeTrue();
});
