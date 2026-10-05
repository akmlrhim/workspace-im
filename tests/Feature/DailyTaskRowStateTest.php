<?php

use App\Livewire\DailyTaskView;
use App\Models\DailyTask;
use App\Models\Space;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use Livewire\Livewire;

/**
 * @return array{0: User, 1: Space, 2: TaskList}
 */
function makeRowStateFixture(): array
{
    $admin = User::factory()->create(['role' => 'administrator']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $admin->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);

    $list->dailyTasks()->create([
        'title' => 'Rapat harian',
        'created_by' => $admin->id,
        'position' => 0,
        'date' => today()->toDateString(),
        'type' => DailyTask::TYPE_ROUTINE,
    ]);

    return [$admin, $space, $list];
}

test('an unchecked task renders data-done false so no completed styling is applied', function () {
    [$admin, $space, $list] = makeRowStateFixture();

    Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->assertSee('data-done="false"', false)
        ->assertDontSee('data-done="true"', false);
});

test('a completed task renders data-done true on a fresh load', function () {
    [$admin, $space, $list] = makeRowStateFixture();

    $task = $list->dailyTasks()->first();

    Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->call('toggleComplete', $task->id);

    Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->assertSee('data-done="true"', false);
});

test('an unchecked task renders data-done false on a fresh load', function () {
    [$admin, $space, $list] = makeRowStateFixture();

    $task = $list->dailyTasks()->first();

    Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->call('toggleComplete', $task->id)
        ->call('toggleComplete', $task->id);

    Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->assertSee('data-done="false"', false)
        ->assertDontSee('data-done="true"', false);
});

test('toggleComplete does not re-render the list so concurrent toggles cannot clobber each other', function () {
    [$admin, $space, $list] = makeRowStateFixture();

    $task = $list->dailyTasks()->first();

    Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->call('toggleComplete', $task->id)
        ->assertNotDispatched('$refresh');
});

test('progress counter reflects how many tasks the user completed', function () {
    [$admin, $space, $list] = makeRowStateFixture();

    $list->dailyTasks()->create([
        'title' => 'Review PR',
        'created_by' => $admin->id,
        'position' => 1,
        'date' => today()->toDateString(),
        'type' => DailyTask::TYPE_ROUTINE,
    ]);

    $component = Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list]);

    expect($component->instance()->completedCount)->toBe(0);

    $component->call('toggleComplete', $list->dailyTasks()->first()->id);

    expect($component->instance()->completedCount)->toBe(1);
});
