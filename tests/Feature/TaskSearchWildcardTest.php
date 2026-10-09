<?php

use App\Livewire\TaskBoard;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use Livewire\Livewire;

test('a task search term with SQL wildcards is treated as a literal, not a wildcard', function () {
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $openStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $openStatus->id,
        'title' => 'Progres 100% selesai',
        'priority' => 'normal',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $openStatus->id,
        'title' => 'Tugas lain',
        'priority' => 'normal',
        'position' => 1,
        'created_by' => $owner->id,
    ]);

    $titles = fn (string $term) => Livewire::actingAs($owner)
        ->test(TaskBoard::class, ['space' => $space, 'taskList' => $list])
        ->set('search', $term)
        ->instance()
        ->statuses
        ->flatMap(fn ($status) => $status->tasks)
        ->pluck('title');

    // A bare "%" must behave as a literal character: only the title that
    // really contains "%" matches, never every task on the board.
    expect($titles('%'))->toContain('Progres 100% selesai')
        ->and($titles('%'))->not->toContain('Tugas lain')
        ->and($titles('_'))->toBeEmpty()
        ->and($titles('100% selesai'))->toContain('Progres 100% selesai')
        ->and($titles('100% selesai'))->not->toContain('Tugas lain');
});
