<?php

use App\Livewire\Project\WorkloadDashboard;
use App\Models\Project\Task;
use App\Models\Project\TaskStatus;
use App\Models\Project\Workspace;
use App\Models\User;
use Livewire\Livewire;

function makeWorkloadContext(): array
{
    $owner = User::factory()->create(['role' => 'member']);
    test()->actingAs($owner);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Design', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Backlog', 'position' => 0]);
    $openStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);
    $closedStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Done', 'position' => 1, 'type' => 'closed']);

    return compact('owner', 'list', 'openStatus', 'closedStatus');
}

test('workload dashboard renders task view by default', function () {
    makeWorkloadContext();

    Livewire::test(WorkloadDashboard::class)
        ->assertSee('Lihat berdasarkan anggota')
        ->assertSee('Lihat berdasarkan tugas')
        ->assertDontSee('Lihat berdasarkan proyek')
        ->assertSee('Distribusi Prioritas')
        ->assertSee('Distribusi Status');
});

test('workload dashboard can switch to task view', function () {
    makeWorkloadContext();

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Distribusi Prioritas')
        ->assertSee('Distribusi Status')
        ->assertSee('Daftar Tugas per Proyek');
});

test('task view shows task data with correct labels', function () {
    $ctx = makeWorkloadContext();

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Contoh',
        'priority' => 'high',
        'created_by' => $ctx['owner']->id,
    ]);

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Tugas Contoh')
        ->assertSee('Tinggi')
        ->assertSee('Belum Dikerjakan');
});

test('task view shows correct stats', function () {
    $ctx = makeWorkloadContext();

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Open Task',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['closedStatus']->id,
        'title' => 'Closed Task',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Total Tugas')
        ->assertSee('Tugas Selesai')
        ->assertSee('Tingkat Penyelesaian');
});

test('task view excludes tasks from lists named Note', function () {
    $ctx = makeWorkloadContext();

    $space = $ctx['list']->space;
    $noteList = $space->lists()->create(['name' => 'Note', 'position' => 1]);
    $lowerNoteList = $space->lists()->create(['name' => 'note', 'position' => 2]);
    $noteOpenStatus = TaskStatus::create(['task_list_id' => $noteList->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);
    $lowerOpenStatus = TaskStatus::create(['task_list_id' => $lowerNoteList->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Utama',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);

    Task::create([
        'task_list_id' => $noteList->id,
        'task_status_id' => $noteOpenStatus->id,
        'title' => 'Catatan Rahasia',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);

    Task::create([
        'task_list_id' => $lowerNoteList->id,
        'task_status_id' => $lowerOpenStatus->id,
        'title' => 'Catatan Kecil',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Tugas Utama')
        ->assertDontSee('Catatan Rahasia')
        ->assertDontSee('Catatan Kecil');
});

test('member view labels are in Indonesian', function () {
    makeWorkloadContext();

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'member')
        ->assertSee('Anggota Tim')
        ->assertSee('Total Tugas')
        ->assertSee('Tugas Terlambat')
        ->assertSee('Beban Kerja Anggota');
});
