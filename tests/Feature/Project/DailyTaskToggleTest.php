<?php

use App\Livewire\Project\DailyTaskView;
use App\Models\Project\DailyTask;
use App\Models\Project\DailyTaskLog;
use App\Models\Project\Space;
use App\Models\Project\TaskList;
use App\Models\Project\Workspace;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{0: User, 1: Space, 2: TaskList}
 */
function makeDailyTaskList(): array
{
    $admin = User::factory()->create(['role' => 'administrator']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $admin->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);

    return [$admin, $space, $list];
}

function makeDailyTask(TaskList $list, User $creator, int $position = 0): DailyTask
{
    return $list->dailyTasks()->create([
        'title' => 'Task '.$position,
        'created_by' => $creator->id,
        'position' => $position,
        'date' => today()->toDateString(),
        'type' => DailyTask::TYPE_ROUTINE,
    ]);
}

test('tasks can be checked one after another with no waiting period', function () {
    [$admin, $space, $list] = makeDailyTaskList();

    $first = makeDailyTask($list, $admin, 0);
    $second = makeDailyTask($list, $admin, 1);
    $third = makeDailyTask($list, $admin, 2);

    $component = Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list]);

    $component->call('toggleComplete', $first->id)->assertReturned(true);
    $component->call('toggleComplete', $second->id)->assertReturned(true);
    $component->call('toggleComplete', $third->id)->assertReturned(true);

    expect(DailyTaskLog::where('is_completed', true)->count())->toBe(3);
});

test('toggleComplete returns the resulting state, not merely success', function () {
    [$admin, $space, $list] = makeDailyTaskList();

    $task = makeDailyTask($list, $admin, 0);

    $component = Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list]);

    $component->call('toggleComplete', $task->id)->assertReturned(true);
    $component->call('toggleComplete', $task->id)->assertReturned(false);
});

test('unchecking right after checking stays unchecked without needing a refresh', function () {
    [$admin, $space, $list] = makeDailyTaskList();

    $task = makeDailyTask($list, $admin, 0);

    $component = Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list]);

    $component->call('toggleComplete', $task->id)->assertReturned(true);
    $component->call('toggleComplete', $task->id)->assertReturned(false);

    expect(DailyTaskLog::where('daily_task_id', $task->id)->first())
        ->is_completed->toBeFalse()
        ->completed_at->toBeNull();
});

test('rapidly toggling the same task ends on the last requested state', function () {
    [$admin, $space, $list] = makeDailyTaskList();

    $task = makeDailyTask($list, $admin, 0);

    $component = Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list]);

    $component->call('toggleComplete', $task->id);
    $component->call('toggleComplete', $task->id);
    $component->call('toggleComplete', $task->id)->assertReturned(true);

    expect(DailyTaskLog::where('daily_task_id', $task->id)->count())->toBe(1)
        ->and(DailyTaskLog::where('daily_task_id', $task->id)->first()->is_completed)->toBeTrue();
});

test('a user without manage rights is rejected with a null result', function () {
    [$admin, $space, $list] = makeDailyTaskList();

    $task = makeDailyTask($list, $admin, 0);

    $outsider = User::factory()->create(['role' => 'employee']);

    Livewire::actingAs($outsider)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->call('toggleComplete', $task->id)
        ->assertReturned(null);

    expect(DailyTaskLog::where('daily_task_id', $task->id)->exists())->toBeFalse();
});

test('completion state is scoped per user', function () {
    [$admin, $space, $list] = makeDailyTaskList();

    $other = User::factory()->create(['role' => 'administrator']);

    $task = makeDailyTask($list, $admin, 0);

    Livewire::actingAs($admin)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->call('toggleComplete', $task->id);

    Livewire::actingAs($other)
        ->test(DailyTaskView::class, ['space' => $space, 'taskList' => $list])
        ->call('toggleComplete', $task->id)
        ->assertReturned(true);

    expect(DailyTaskLog::where('daily_task_id', $task->id)->count())->toBe(2);
});
