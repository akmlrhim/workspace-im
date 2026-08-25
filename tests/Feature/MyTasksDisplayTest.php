<?php

use App\Livewire\MyTasks;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Workspace;
use App\Models\User;
use Livewire\Livewire;

test('my tasks calendar distinguishes tasks by their list', function () {
    $user = User::factory()->create(['role' => 'member']);
    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $user->id]);
    $space = $workspace->spaces()->create(['name' => 'IT', 'position' => 0, 'color' => '#f59e0b']);
    $briefList = $space->lists()->create(['name' => 'Project Brief', 'position' => 0]);
    $supportList = $space->lists()->create(['name' => 'Support Queue', 'position' => 1]);
    $briefStatus = TaskStatus::create(['task_list_id' => $briefList->id, 'name' => 'In Review', 'position' => 0, 'type' => 'active', 'color' => '#f59e0b']);
    $supportStatus = TaskStatus::create(['task_list_id' => $supportList->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open', 'color' => '#3b82f6']);

    $briefTask = Task::create([
        'task_list_id' => $briefList->id,
        'task_status_id' => $briefStatus->id,
        'title' => 'PB: Project Brief Management',
        'due_date' => now()->toDateString(),
        'created_by' => $user->id,
    ]);
    $supportTask = Task::create([
        'task_list_id' => $supportList->id,
        'task_status_id' => $supportStatus->id,
        'title' => 'Answer support request',
        'due_date' => now()->toDateString(),
        'created_by' => $user->id,
    ]);
    $briefTask->assignees()->attach($user->id);
    $supportTask->assignees()->attach($user->id);

    Livewire::actingAs($user)
        ->test(MyTasks::class)
        ->call('switchView', 'calendar')
        ->assertSee('Project Brief')
        ->assertSee('Support Queue')
        ->assertSee('PB: Project Brief Management')
        ->assertSee('Answer support request');
});

test('my tasks overdue labels render whole days only', function () {
    $user = User::factory()->create(['role' => 'member']);
    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $user->id]);
    $space = $workspace->spaces()->create(['name' => 'IT', 'position' => 0, 'color' => '#f59e0b']);
    $list = $space->lists()->create(['name' => 'Project Brief', 'position' => 0]);
    $status = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'In Review', 'position' => 0, 'type' => 'active', 'color' => '#f59e0b']);
    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $status->id,
        'title' => 'PB: Project Brief Management',
        'priority' => 'high',
        'due_date' => now()->subDay()->toDateString(),
        'created_by' => $user->id,
    ]);
    $task->assignees()->attach($user->id);

    Livewire::actingAs($user)
        ->test(MyTasks::class)
        ->assertSee('1h lalu')
        ->assertDontSee('1.0h lalu')
        ->assertDontSee('1,0h lalu')
        ->assertDontSee('-1h lalu');
});
