<?php

use App\Livewire\Project\WorkloadDashboard;
use App\Models\Project\Task;
use App\Models\Project\TaskStatus;
use App\Models\Project\Workspace;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->owner = User::factory()->create(['role' => 'member']);
    $this->actingAs($this->owner);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $this->owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Design', 'position' => 0]);
    $this->list = $space->lists()->create(['name' => 'Backlog', 'position' => 0]);
    $this->openStatus = TaskStatus::create(['task_list_id' => $this->list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open']);
    $this->closedStatus = TaskStatus::create(['task_list_id' => $this->list->id, 'name' => 'Done', 'position' => 1, 'type' => 'closed']);
});

test('workload dashboard renders project view by default', function () {
    Livewire::test(WorkloadDashboard::class)
        ->assertSee('Lihat berdasarkan proyek')
        ->assertSee('Lihat berdasarkan anggota')
        ->assertSee('Lihat berdasarkan tugas')
        ->assertSee('Ringkasan Beban Kerja Proyek');
});

test('workload dashboard can switch to task view', function () {
    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Distribusi Prioritas')
        ->assertSee('Distribusi Status')
        ->assertSee('Daftar Tugas');
});

test('task view shows task data with correct labels', function () {
    Task::create([
        'task_list_id' => $this->list->id,
        'task_status_id' => $this->openStatus->id,
        'title' => 'Tugas Contoh',
        'priority' => 'high',
        'created_by' => $this->owner->id,
    ]);

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Tugas Contoh')
        ->assertSee('Tinggi')
        ->assertSee('Belum Dikerjakan');
});

test('task view shows correct stats', function () {
    Task::create([
        'task_list_id' => $this->list->id,
        'task_status_id' => $this->openStatus->id,
        'title' => 'Open Task',
        'priority' => 'normal',
        'created_by' => $this->owner->id,
    ]);

    Task::create([
        'task_list_id' => $this->list->id,
        'task_status_id' => $this->closedStatus->id,
        'title' => 'Closed Task',
        'priority' => 'normal',
        'created_by' => $this->owner->id,
    ]);

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Total Tugas')
        ->assertSee('Tugas Selesai')
        ->assertSee('Tingkat Penyelesaian');
});

test('member view labels are in Indonesian', function () {
    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'member')
        ->assertSee('Anggota Tim')
        ->assertSee('Total Tugas')
        ->assertSee('Tugas Terlambat')
        ->assertSee('Beban Kerja Anggota');
});
