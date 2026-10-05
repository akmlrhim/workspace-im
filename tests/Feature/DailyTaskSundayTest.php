<?php

use App\Livewire\DailyTaskView;
use App\Models\DailyTask;
use App\Models\DailyTaskLog;
use App\Models\Space;
use App\Models\TaskList;
use App\Models\User;
use App\Models\Workspace;
use Carbon\Carbon;
use Livewire\Livewire;

/**
 * @return array{0: User, 1: Space, 2: TaskList}
 */
function makeSundayDailyTaskList(): array
{
    $sunday = Carbon::parse('2026-08-02')->startOfDay();

    expect($sunday->isSunday())->toBeTrue();

    test()->travelTo($sunday);

    $admin = User::factory()->create(['role' => 'administrator']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $admin->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);

    return [$admin, $space, $list];
}

test('daily tasks are listed on a Sunday', function () {
    [$admin, $space, $list] = makeSundayDailyTaskList();

    $list->dailyTasks()->create([
        'title' => 'Rutinitas',
        'created_by' => $admin->id,
        'position' => 0,
        'date' => today()->toDateString(),
        'type' => DailyTask::TYPE_ROUTINE,
    ]);

    $component = Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->assertOk()
        ->assertSee('Rutinitas');

    expect($component->instance()->dailyTasks)->toHaveCount(1);
});

test('a daily task can be added and completed on a Sunday', function () {
    [$admin, $space, $list] = makeSundayDailyTaskList();

    $component = Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->call('addDailyTask', 'Task akhir pekan', '', DailyTask::TYPE_ON_DEMAND);

    $dailyTask = DailyTask::where('task_list_id', $list->id)->firstOrFail();

    expect($dailyTask->title)->toBe('Task akhir pekan')
        ->and($dailyTask->date->toDateString())->toBe(today()->toDateString());

    $component->call('toggleComplete', $dailyTask->id)->assertReturned(true);

    expect(DailyTaskLog::where('daily_task_id', $dailyTask->id)->where('is_completed', true)->exists())->toBeTrue();
});
