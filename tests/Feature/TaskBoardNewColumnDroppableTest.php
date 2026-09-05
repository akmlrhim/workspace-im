<?php

use App\Livewire\TaskBoard;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Livewire\Livewire;

/**
 * @return array<string, mixed>
 */
function makeNewColumnContext(): array
{
    $user = User::factory()->create(['role' => 'administrator']);
    test()->actingAs($user);

    $workspace = Workspace::create(['name' => 'Workspace Utama', 'owner_id' => $user->id]);
    WorkspaceMember::create(['workspace_id' => $workspace->id, 'user_id' => $user->id, 'role' => 'owner']);

    $space = $workspace->spaces()->create(['name' => 'Ruang Desain', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Daftar Backlog', 'position' => 0]);
    $list->members()->attach($user->id);

    $todo = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);

    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $todo->id,
        'title' => 'Rancang ulang kartu',
        'position' => 0,
        'created_by' => $user->id,
    ]);

    return compact('user', 'space', 'list', 'todo', 'task');
}

test('a column added during the session is rendered as a drop target in the same response', function () {
    $ctx = makeNewColumnContext();

    $component = Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        ->set('newColumn.name', 'Review')
        ->set('newColumn.color', '#8b5cf6')
        ->call('addColumn');

    $newColumn = TaskStatus::where('task_list_id', $ctx['list']->id)->where('name', 'Review')->sole();

    expect($component->html())
        ->toContain('data-status-id="'.$newColumn->id.'"')
        ->toContain('x-init="registerColumn($el)"');
});

test('a task can be moved into a column created moments earlier without reloading the board', function () {
    $ctx = makeNewColumnContext();

    $component = Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        ->set('newColumn.name', 'Review')
        ->call('addColumn');

    $newColumn = TaskStatus::where('task_list_id', $ctx['list']->id)->where('name', 'Review')->sole();

    $component->call('moveTask', $ctx['task']->id, $newColumn->id, [$ctx['task']->id])
        ->assertDispatched('task-status-updated-from-board');

    expect($ctx['task']->refresh()->task_status_id)->toBe($newColumn->id);
});

test('the board script registers every column it receives, including ones morphed in later', function () {
    $script = file_get_contents(resource_path('views/livewire/partials/board/kanban-script.blade.php'));

    expect($script)
        ->toContain('registerColumn(columnEl)')
        ->toContain('_pruneDetachedSortables()');
});
