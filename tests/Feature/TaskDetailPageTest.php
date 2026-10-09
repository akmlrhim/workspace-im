<?php

use App\Livewire\NavbarNotifications;
use App\Livewire\TaskDetail;
use App\Livewire\TaskDetailPage;
use App\Models\Space;
use App\Models\Task;
use App\Models\TaskList;
use App\Models\TaskStatus;
use App\Models\User;
use App\Models\Workspace;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * @return array{owner: User, task: Task, space: Space, list: TaskList}
 */
function taskDetailPageFixture(): array
{
    $owner = User::factory()->create(['role' => 'member', 'position' => 'Web Developer']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $list->members()->attach($owner->id);

    $open = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);

    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $open->id,
        'title' => 'Refactor target',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    return compact('owner', 'task', 'space', 'list');
}

test('the task detail route renders the standalone detail page', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailPageFixture();

    $this->actingAs($owner)
        ->get(route('tasks.show', $task->id))
        ->assertOk()
        ->assertSeeLivewire(TaskDetailPage::class)
        ->assertSeeLivewire(TaskDetail::class)
        ->assertSee('Refactor target');
});

test('the task detail route requires authentication', function () {
    ['task' => $task] = taskDetailPageFixture();

    $this->get(route('tasks.show', $task->id))->assertRedirect(route('login'));
});

test('an outsider without access to the list gets a 403', function () {
    ['task' => $task] = taskDetailPageFixture();

    $outsider = User::factory()->create(['role' => 'member', 'position' => 'Web Developer']);

    $this->actingAs($outsider)
        ->get(route('tasks.show', $task->id))
        ->assertForbidden();
});

test('an assigned non member can still open the standalone detail page', function () {
    ['task' => $task] = taskDetailPageFixture();

    $assignee = User::factory()->create(['role' => 'member', 'position' => 'Web Developer']);
    $task->assignees()->attach($assignee->id);

    $this->actingAs($assignee)
        ->get(route('tasks.show', $task->id))
        ->assertOk()
        ->assertSee('Refactor target');
});

test('the standalone page renders a back button instead of the modal close control', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailPageFixture();

    $html = Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id, 'standalone' => true])
        ->html();

    expect($html)
        ->toContain('title="Kembali"')
        ->not->toContain('title="Tutup"');
});

test('the modal variant keeps the flux modal close control', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailPageFixture();

    $html = Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->html();

    expect($html)
        ->toContain('title="Tutup"')
        ->not->toContain('title="Kembali"');
});

test('closing the standalone detail panel redirects back to the task board', function () {
    ['owner' => $owner, 'task' => $task, 'space' => $space, 'list' => $list] = taskDetailPageFixture();

    Livewire::actingAs($owner)
        ->test(TaskDetailPage::class, ['task' => $task])
        ->call('close')
        ->assertRedirect(route('lists.board', [$space, $list]));
});

test('the back url points at the list board that owns the task', function () {
    ['owner' => $owner, 'task' => $task, 'space' => $space, 'list' => $list] = taskDetailPageFixture();

    $component = Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id, 'standalone' => true]);

    expect($component->instance()->backUrl())->toBe(route('lists.board', [$space, $list]));
});

test('deleting the task from the standalone page redirects away from the dead page', function () {
    ['owner' => $owner, 'task' => $task, 'space' => $space, 'list' => $list] = taskDetailPageFixture();

    Livewire::actingAs($owner)
        ->test(TaskDetailPage::class, ['task' => $task])
        ->dispatch('task-deleted', taskId: $task->id)
        ->assertRedirect(route('lists.board', [$space, $list]));
});

test('clicking a task notification opens the standalone task page', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailPageFixture();

    $owner->notify(new TaskAssignedNotification($task, 'Someone'));

    $notification = $owner->notifications()->firstOrFail();

    Livewire::actingAs($owner)
        ->test(NavbarNotifications::class)
        ->call('openNotification', $notification->id)
        ->assertRedirect(route('tasks.show', $task->id));

    expect($owner->notifications()->firstOrFail()->read_at)->not->toBeNull();
});

test('a notification without a task id is only marked as read', function () {
    ['owner' => $owner] = taskDetailPageFixture();

    $owner->notifications()->create([
        'id' => (string) Str::uuid(),
        'type' => 'App\\Notifications\\GenericNotification',
        'data' => ['title' => 'Hello', 'body' => 'No task here'],
        'read_at' => null,
    ]);

    $notification = $owner->notifications()->firstOrFail();

    Livewire::actingAs($owner)
        ->test(NavbarNotifications::class)
        ->call('openNotification', $notification->id)
        ->assertNoRedirect();

    expect($owner->notifications()->firstOrFail()->read_at)->not->toBeNull();
});
