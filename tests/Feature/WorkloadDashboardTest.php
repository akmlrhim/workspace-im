<?php

use App\Livewire\WorkloadDashboard;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Workspace;
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
        ->assertSee('View By Task')
        ->assertSee('Peringkat')
        ->assertSee('Total Tugas')
        ->assertSee('Daftar Tugas per List');
});

test('workload dashboard can switch to member view', function () {
    makeWorkloadContext();

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'member')
        ->assertSet('view', 'member')
        ->assertSee('Anggota Aktif')
        ->assertSee('memiliki tugas bulan ini');
});

test('task view shows task data with correct labels', function () {
    $ctx = makeWorkloadContext();

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Contoh',
        'priority' => 'high',
        'due_date' => now(),
        'created_by' => $ctx['owner']->id,
    ]);

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Tugas Contoh')
        ->assertSee('Tinggi')
        ->assertSee('Belum Dimulai');
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
        ->assertSee('Penyelesaian')
        ->assertViewHas('totalTasks', 2)
        ->assertViewHas('completedTasks', 1);
});

test('task view excludes tasks with status named Note', function () {
    $ctx = makeWorkloadContext();

    $noteStatus = TaskStatus::create(['task_list_id' => $ctx['list']->id, 'name' => 'Note', 'position' => 2, 'type' => 'active']);
    $lowerNoteStatus = TaskStatus::create(['task_list_id' => $ctx['list']->id, 'name' => 'note', 'position' => 3, 'type' => 'active']);

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Aktif',
        'priority' => 'normal',
        'due_date' => now(),
        'created_by' => $ctx['owner']->id,
    ]);

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $noteStatus->id,
        'title' => 'Catatan Status',
        'priority' => 'normal',
        'due_date' => now(),
        'created_by' => $ctx['owner']->id,
    ]);

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $lowerNoteStatus->id,
        'title' => 'Catatan Huruf Kecil',
        'priority' => 'normal',
        'due_date' => now(),
        'created_by' => $ctx['owner']->id,
    ]);

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'task')
        ->assertSee('Tugas Aktif')
        ->assertDontSee('Catatan Status')
        ->assertDontSee('Catatan Huruf Kecil');
});

test('member view shows the leaderboard for assigned members', function () {
    $ctx = makeWorkloadContext();

    $alice = User::factory()->create(['name' => 'Alice', 'role' => 'member']);
    $task = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Alice',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);
    $task->assignees()->attach($alice->id);

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'member')
        ->assertSee('Papan Peringkat Anggota')
        ->assertSee('Anggota Aktif')
        ->assertSee('Alice')
        ->assertSee('Terlambat');
});

test('an invalid month value snaps back to the current month', function () {
    makeWorkloadContext();

    Livewire::test(WorkloadDashboard::class)
        ->set('selectedMonth', '')
        ->assertSet('selectedMonth', now()->format('Y-m'));
});

test('a task completed before its deadline stays on time after the deadline passes', function () {
    $ctx = makeWorkloadContext();

    $due = now()->subDay();

    $task = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['closedStatus']->id,
        'title' => 'Selesai Duluan',
        'priority' => 'normal',
        'due_date' => $due,
        'created_by' => $ctx['owner']->id,
    ]);
    $task->forceFill(['completed_at' => $due->copy()->subDays(2)])->saveQuietly();

    Livewire::test(WorkloadDashboard::class)
        ->set('selectedMonth', $due->format('Y-m'))
        ->assertViewHas('completedOnTime', 1)
        ->assertViewHas('completedLate', 0);
});

test('a task completed after its deadline counts as late', function () {
    $ctx = makeWorkloadContext();

    $due = now()->subDay();

    $task = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['closedStatus']->id,
        'title' => 'Selesai Telat',
        'priority' => 'normal',
        'due_date' => $due,
        'created_by' => $ctx['owner']->id,
    ]);
    $task->forceFill(['completed_at' => $due->copy()->addDays(2)])->saveQuietly();

    Livewire::test(WorkloadDashboard::class)
        ->set('selectedMonth', $due->format('Y-m'))
        ->assertViewHas('completedOnTime', 0)
        ->assertViewHas('completedLate', 1);
});

test('changing the month clears a member that has no tasks in the new scope', function () {
    $ctx = makeWorkloadContext();

    $alice = User::factory()->create(['name' => 'Alice', 'role' => 'member']);

    $task = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Alice',
        'priority' => 'normal',
        'due_date' => null,
        'created_by' => $ctx['owner']->id,
    ]);
    $task->assignees()->attach($alice->id);

    Livewire::test(WorkloadDashboard::class)
        ->set('selectedMemberId', $alice->id)
        ->set('selectedMonth', now()->subMonthsNoOverflow(1)->format('Y-m'))
        ->assertSet('selectedMemberId', null);
});

test('task view can be filtered to a single assignee', function () {
    $ctx = makeWorkloadContext();

    $alice = User::factory()->create(['name' => 'Alice', 'role' => 'member']);
    $bob = User::factory()->create(['name' => 'Bob', 'role' => 'member']);

    $aliceTask = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Alice',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);
    $aliceTask->assignees()->attach($alice->id);

    $bobTask = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Bob',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);
    $bobTask->assignees()->attach($bob->id);

    Livewire::test(WorkloadDashboard::class)
        ->assertSee('Tugas Alice')
        ->assertSee('Tugas Bob')
        ->set('selectedMemberId', $alice->id)
        ->assertSee('Tugas Alice')
        ->assertDontSee('Tugas Bob');
});

test('the member dropdown lists assignees that have tasks in scope', function () {
    $ctx = makeWorkloadContext();

    $alice = User::factory()->create(['name' => 'Alice', 'role' => 'member']);

    $task = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Alice',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);
    $task->assignees()->attach($alice->id);

    $members = Livewire::test(WorkloadDashboard::class)->instance()->members;

    expect($members->pluck('id')->all())->toContain($alice->id);
});

test('open tasks without a deadline appear only in the current month', function () {
    $ctx = makeWorkloadContext();

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Backlog Terbuka',
        'priority' => 'normal',
        'due_date' => null,
        'created_by' => $ctx['owner']->id,
    ]);

    $lastMonth = now()->subMonthsNoOverflow(1)->format('Y-m');

    Livewire::test(WorkloadDashboard::class)
        ->assertSee('Backlog Terbuka')
        ->set('selectedMonth', $lastMonth)
        ->assertDontSee('Backlog Terbuka');
});

test('completed tasks without a deadline appear in the month they were completed', function () {
    $ctx = makeWorkloadContext();

    Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['closedStatus']->id,
        'title' => 'Selesai Tanpa Tenggat',
        'priority' => 'normal',
        'due_date' => null,
        'created_by' => $ctx['owner']->id,
    ]);

    $lastMonth = now()->subMonthsNoOverflow(1)->format('Y-m');

    Livewire::test(WorkloadDashboard::class)
        ->assertSee('Selesai Tanpa Tenggat')
        ->set('selectedMonth', $lastMonth)
        ->assertDontSee('Selesai Tanpa Tenggat');
});

test('changing the space filter clears the selected member', function () {
    $ctx = makeWorkloadContext();

    $alice = User::factory()->create(['name' => 'Alice', 'role' => 'member']);
    $task = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Alice',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);
    $task->assignees()->attach($alice->id);

    Livewire::test(WorkloadDashboard::class)
        ->set('selectedMemberId', $alice->id)
        ->call('selectSpace', null)
        ->assertSet('selectedMemberId', null);
});

test('the member picker belongs to the task view only', function () {
    $ctx = makeWorkloadContext();

    $alice = User::factory()->create(['name' => 'Alice', 'role' => 'member']);
    $task = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Alice',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);
    $task->assignees()->attach($alice->id);

    Livewire::test(WorkloadDashboard::class)
        ->assertSee('Semua Anggota')
        ->call('switchView', 'member')
        ->assertDontSee('Semua Anggota')
        ->call('switchView', 'task')
        ->assertSee('Semua Anggota');
});

test('a selected member never narrows the leaderboard', function () {
    $ctx = makeWorkloadContext();

    $alice = User::factory()->create(['name' => 'Alice', 'role' => 'member']);
    $bob = User::factory()->create(['name' => 'Bob', 'role' => 'member']);

    foreach ([[$alice, 'Tugas Alice'], [$bob, 'Tugas Bob']] as [$user, $title]) {
        $task = Task::create([
            'task_list_id' => $ctx['list']->id,
            'task_status_id' => $ctx['openStatus']->id,
            'title' => $title,
            'priority' => 'normal',
            'created_by' => $ctx['owner']->id,
        ]);
        $task->assignees()->attach($user->id);
    }

    $component = Livewire::test(WorkloadDashboard::class)
        ->set('selectedMemberId', $alice->id)
        ->assertSee('Tugas Alice')
        ->assertDontSee('Tugas Bob');

    $component->call('switchView', 'member')
        ->assertSee('Alice')
        ->assertSee('Bob')
        ->assertViewHas('totalMembers', 2);
});

test('the member selection survives a trip through the leaderboard tab', function () {
    $ctx = makeWorkloadContext();

    $alice = User::factory()->create(['name' => 'Alice', 'role' => 'member']);
    $task = Task::create([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Alice',
        'priority' => 'normal',
        'created_by' => $ctx['owner']->id,
    ]);
    $task->assignees()->attach($alice->id);

    Livewire::test(WorkloadDashboard::class)
        ->set('selectedMemberId', $alice->id)
        ->call('switchView', 'member')
        ->assertSet('selectedMemberId', $alice->id)
        ->call('switchView', 'task')
        ->assertSet('selectedMemberId', $alice->id)
        ->assertSee('Alice');
});
