<?php

use App\Livewire\GeneralTaskboard;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use App\Models\User;
use Livewire\Livewire;

test('global search suggests matching tasks across all accessible spaces', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    WorkspaceMember::create(['workspace_id' => $workspace->id, 'user_id' => $owner->id, 'role' => 'owner']);

    $spaceA = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $spaceB = $workspace->spaces()->create(['name' => 'Marketing', 'position' => 1]);

    $listA = $spaceA->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $listB = $spaceB->lists()->create(['name' => 'Campaign', 'position' => 0]);

    $statusA = TaskStatus::create(['task_list_id' => $listA->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
    $statusB = TaskStatus::create(['task_list_id' => $listB->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    $makeTask = fn (int $listId, int $statusId, string $title) => Task::create([
        'task_list_id' => $listId,
        'task_status_id' => $statusId,
        'title' => $title,
        'priority' => 'normal',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    $makeTask($listA->id, $statusA->id, 'Rapat internal tim');
    $makeTask($listB->id, $statusB->id, 'Rapat campaign Q3');
    $makeTask($listA->id, $statusA->id, 'Deploy aplikasi');

    $component = Livewire::actingAs($owner)
        ->test(GeneralTaskboard::class)
        ->set('globalSearch', 'Rapat')
        ->assertSee('Rapat internal tim')
        ->assertSee('Rapat campaign Q3');

    $suggestions = $component->instance()->globalSearchSuggestions->pluck('title');

    expect($suggestions)->toContain('Rapat internal tim', 'Rapat campaign Q3')
        ->not->toContain('Deploy aplikasi');
});

test('global search requires at least two characters and hides inaccessible tasks', function () {
    $owner = User::factory()->create(['role' => 'member']);
    $outsider = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    WorkspaceMember::create(['workspace_id' => $workspace->id, 'user_id' => $owner->id, 'role' => 'owner']);

    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'Rapat internal tim',
        'priority' => 'normal',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    $short = Livewire::actingAs($owner)
        ->test(GeneralTaskboard::class)
        ->set('globalSearch', 'R');

    expect($short->instance()->globalSearchSuggestions)->toBeEmpty();

    $foreign = Livewire::actingAs($outsider)
        ->test(GeneralTaskboard::class)
        ->set('globalSearch', 'Rapat')
        ->assertDontSee('Rapat internal tim');

    expect($foreign->instance()->globalSearchSuggestions)->toBeEmpty();
});
