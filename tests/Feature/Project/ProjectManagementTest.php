<?php

use App\Models\Project\Space;
use App\Models\Project\Task;
use App\Models\Project\TaskStatus;
use App\Models\Project\Workspace;
use App\Models\User;

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

test('space route model binding resolves by uuid', function () {
	$owner = User::factory()->create(['role' => 'member']);
	$this->actingAs($owner);

	$workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
	$space = $workspace->spaces()->create(['name' => 'Ops', 'position' => 0]);

	$this->get(route('project-management.spaces.show', $space))->assertOk();

	// The route URL must contain the uuid, not the numeric id.
	$url = route('project-management.spaces.show', $space);
	expect($url)->toContain($space->uuid)->not->toContain('/' . $space->id);
});

test('manager role can manage tasks without being assigned', function () {
	$owner = User::factory()->create(['role' => 'member']);
	$manager = User::factory()->create(['role' => 'manager']);

	$workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
	$space = $workspace->spaces()->create(['name' => 'Sales', 'position' => 0]);
	$list = $space->lists()->create(['name' => 'Pipeline', 'position' => 0]);
	$status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
	$task = Task::create([
		'task_list_id' => $list->id,
		'task_status_id' => $status->id,
		'title' => 'Close deal',
		'created_by' => $owner->id,
	]);
	$task->load('assignees', 'taskList.space.workspace');

	expect($task->canBeManagedBy($manager))->toBeTrue();
	expect($manager->canManageAllProjects())->toBeTrue();
});

test('accessible spaces scope grants elevated roles access to every space', function () {
	$owner = User::factory()->create(['role' => 'member']);
	$outsider = User::factory()->create(['role' => 'member']);
	$administrator = User::factory()->create(['role' => 'administrator']);

	$workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
	$workspace->spaces()->create(['name' => 'Private', 'position' => 0]);

	expect(Space::accessibleBy($outsider->id)->count())->toBe(0);
	expect(Space::accessibleBy($administrator->id)->count())->toBe(1);
});
