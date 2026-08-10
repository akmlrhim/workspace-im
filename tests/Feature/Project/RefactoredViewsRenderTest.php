<?php

use App\Livewire\Project\DailyTaskView;
use App\Livewire\Project\GeneralTaskboard;
use App\Livewire\Project\ListNotes;
use App\Livewire\Project\MyTasks;
use App\Livewire\Project\TaskBoard;
use App\Livewire\Project\WorkloadDashboard;
use App\Livewire\Users\UserIndex;
use App\Models\Project\DailyTask;
use App\Models\Project\DailyTaskLog;
use App\Models\Project\Task;
use App\Models\Project\TaskLabel;
use App\Models\Project\TaskStatus;
use App\Models\Project\Workspace;
use App\Models\Project\WorkspaceMember;
use App\Models\User;
use Livewire\Livewire;

/**
 * Guards the partial split: every extracted partial must still be reachable and
 * render. Each branch below corresponds to a partial that only appears under a
 * specific condition (member view, calendar tab, non-empty attachments, ...),
 * so a missing include or a variable that no longer reaches its partial fails
 * here rather than silently rendering a blank section in production.
 */

/**
 * A workspace with one list, an open/active/closed status, and the acting user
 * as a list member so every "canManage" branch renders.
 *
 * @return array<string, mixed>
 */
function makeRenderContext(string $role = 'administrator'): array
{
    $user = User::factory()->create(['role' => $role, 'name' => 'Andi Pratama']);
    test()->actingAs($user);

    $workspace = Workspace::create(['name' => 'Workspace Utama', 'owner_id' => $user->id]);
    WorkspaceMember::create(['workspace_id' => $workspace->id, 'user_id' => $user->id, 'role' => 'owner']);

    $space = $workspace->spaces()->create(['name' => 'Ruang Desain', 'position' => 0, 'color' => '#6366f1', 'icon' => 'folder']);
    $list = $space->lists()->create(['name' => 'Daftar Backlog', 'position' => 0]);
    $list->members()->attach($user->id);

    $openStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Todo', 'position' => 0, 'type' => 'open', 'color' => '#6b7280']);
    $activeStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Dikerjakan', 'position' => 1, 'type' => 'active', 'color' => '#3b82f6']);
    $closedStatus = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Selesai', 'position' => 2, 'type' => 'closed', 'color' => '#10b981']);

    return compact('user', 'workspace', 'space', 'list', 'openStatus', 'activeStatus', 'closedStatus');
}

/**
 * @param  array<string, mixed>  $ctx
 * @param  array<string, mixed>  $overrides
 */
function makeRenderTask(array $ctx, array $overrides = []): Task
{
    return Task::create(array_merge([
        'task_list_id' => $ctx['list']->id,
        'task_status_id' => $ctx['openStatus']->id,
        'title' => 'Tugas Contoh',
        'priority' => 'normal',
        'created_by' => $ctx['user']->id,
    ], $overrides));
}

// ─── Workload dashboard ────────────────────────────────────────────

test('workload task view renders stat cards, groups and task rows together', function () {
    $ctx = makeRenderContext();

    $onTime = makeRenderTask($ctx, ['title' => 'Selesai Tepat Waktu', 'task_status_id' => $ctx['closedStatus']->id, 'due_date' => now()->endOfMonth()]);
    $onTime->assignees()->attach($ctx['user']->id);

    makeRenderTask($ctx, ['title' => 'Tugas Terlambat', 'priority' => 'urgent', 'due_date' => now()->startOfMonth()]);
    makeRenderTask($ctx, ['title' => 'Tanpa Tenggat', 'task_status_id' => $ctx['closedStatus']->id]);

    Livewire::test(WorkloadDashboard::class)
        // filters partial
        ->assertSee('Semua Anggota')
        ->assertSee('Semua Space')
        // task-stat-cards partial
        ->assertSee('Melewati Tenggat')
        ->assertSee('Penyelesaian')
        ->assertSee('tanpa tenggat')
        // task-groups + task-row partials
        ->assertSee('Daftar Tugas per List')
        ->assertSee('Daftar Backlog')
        ->assertSee('Jam Tercatat')
        ->assertSee('Selesai Tepat Waktu')
        ->assertSee('Tugas Terlambat')
        ->assertSee('Mendesak');
});

test('workload member view renders podium and the ranked list beyond third place', function () {
    $ctx = makeRenderContext();

    // Five assignees so the podium (top 3) and rank-list (#4 onwards) both render.
    foreach (['Budi', 'Citra', 'Dewi', 'Eko', 'Fajar'] as $index => $name) {
        $member = User::factory()->create(['name' => $name, 'role' => 'member']);
        $task = makeRenderTask($ctx, [
            'title' => "Tugas {$name}",
            'task_status_id' => $index < 3 ? $ctx['closedStatus']->id : $ctx['openStatus']->id,
            'due_date' => now(),
        ]);
        $task->assignees()->attach($member->id);
    }

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'member')
        // member-stat-cards partial
        ->assertSee('Anggota Aktif')
        ->assertSee('ditetapkan ke anggota')
        // leaderboard + podium partials
        ->assertSee('Papan Peringkat Anggota')
        ->assertSee('tingkat penyelesaian')
        ->assertSee('#1')
        // rank-list partial (only renders when more than three members)
        ->assertSee('Peringkat Selanjutnya')
        ->assertSee('#4');
});

test('workload member view renders its empty state without a podium', function () {
    makeRenderContext();

    Livewire::test(WorkloadDashboard::class)
        ->call('switchView', 'member')
        ->assertSee('Tidak ada data anggota untuk periode ini')
        ->assertDontSee('Peringkat Selanjutnya');
});

// ─── Task board ────────────────────────────────────────────────────

test('task board renders toolbar, columns, cards and column modals', function () {
    $ctx = makeRenderContext();

    $label = TaskLabel::create(['workspace_id' => $ctx['workspace']->id, 'name' => 'Prioritas Klien', 'color' => '#ef4444']);

    $task = makeRenderTask($ctx, [
        'title' => 'Kartu Papan',
        'description' => 'Deskripsi kartu',
        'priority' => 'high',
        'due_date' => now(),
    ]);
    $task->assignees()->attach($ctx['user']->id);
    $task->labels()->attach($label->id);

    Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        // toolbar partial
        ->assertSee('Cari tugas...')
        ->assertSee('Semua Prioritas')
        ->assertSee('Semua Label')
        ->assertSee('Prioritas Klien')
        // column-header partial
        ->assertSee('Todo')
        ->assertSee('Dikerjakan')
        // task-card partial
        ->assertSee('Kartu Papan')
        ->assertSee('Hari ini')
        // add-column + column-modals partials
        ->assertSee('Tambah Kolom Baru')
        ->assertSee('Hapus Kolom?')
        ->assertSee('Ubah Kolom');
});

test('task board hides management affordances for a user who cannot manage it', function () {
    $ctx = makeRenderContext();
    $ctx['list']->members()->detach();

    $outsider = User::factory()->create(['role' => 'member']);
    $this->actingAs($outsider);

    Livewire::test(TaskBoard::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        ->assertSee('Todo')
        ->assertDontSee('Tambah Kolom Baru');
});

// ─── Daily task view ───────────────────────────────────────────────

test('daily task view renders navigator, both task sections, add row and modals', function () {
    $ctx = makeRenderContext();

    $routine = DailyTask::create([
        'task_list_id' => $ctx['list']->id,
        'created_by' => $ctx['user']->id,
        'title' => 'Cek Email Pagi',
        'description' => 'Setiap hari kerja',
        'position' => 0,
        'date' => today()->toDateString(),
        'type' => DailyTask::TYPE_ROUTINE,
    ]);

    DailyTask::create([
        'task_list_id' => $ctx['list']->id,
        'created_by' => $ctx['user']->id,
        'title' => 'Rapat Klien',
        'position' => 0,
        'date' => today()->toDateString(),
        'type' => DailyTask::TYPE_ON_DEMAND,
    ]);

    // A completed log renders the "Selesai HH:MM" branch inside the row partial.
    DailyTaskLog::create([
        'daily_task_id' => $routine->id,
        'user_id' => $ctx['user']->id,
        'date' => today()->toDateString(),
        'is_completed' => true,
        'completed_at' => now(),
    ]);

    Livewire::test(DailyTaskView::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        // date-navigator partial
        ->assertSee('Hari ini')
        ->assertSee('Lompat ke Hari Ini')
        // section headers render only when both types are present
        ->assertSee('Rutin harian')
        ->assertSee('Khusus hari ini')
        // task-row partial
        ->assertSee('Cek Email Pagi')
        ->assertSee('Setiap hari kerja')
        ->assertSee('Rapat Klien')
        // add-row + modals partials
        ->assertSee('Tambah task')
        ->assertSee('Hapus Daily Task?');
});

test('daily task view renders the reason modal branch', function () {
    $ctx = makeRenderContext();

    $daily = DailyTask::create([
        'task_list_id' => $ctx['list']->id,
        'created_by' => $ctx['user']->id,
        'title' => 'Tugas Beralasan',
        'position' => 0,
        'date' => today()->toDateString(),
        'type' => DailyTask::TYPE_ON_DEMAND,
    ]);

    Livewire::test(DailyTaskView::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        ->call('openReasonModal', $daily->id)
        ->assertSee('Kenapa belum selesai?')
        ->assertSee('Tulis alasanmu di sini...');
});

test('daily task view renders read-only empty state without the add row', function () {
    $ctx = makeRenderContext();
    $ctx['list']->members()->detach();

    $outsider = User::factory()->create(['role' => 'member']);
    $this->actingAs($outsider);

    Livewire::test(DailyTaskView::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        ->assertSee('Belum ada task untuk hari')
        ->assertDontSee('Tambah task');
});

// ─── General taskboard ─────────────────────────────────────────────

test('general taskboard lists tab renders the spaces grid and every modal', function () {
    makeRenderContext();

    Livewire::test(GeneralTaskboard::class)
        // header partial
        ->assertSee('General Taskboard')
        ->assertSee('Kalender Deadline')
        // spaces-grid partial
        ->assertSee('Ruang Desain')
        ->assertSee('Daftar Backlog')
        // modals partial
        ->assertSee('Buat Space Baru')
        ->assertSee('Buat List Baru')
        ->assertSee('Edit Space')
        ->assertSee('Kelola Anggota List')
        ->assertSee('Hapus Space');
});

test('general taskboard calendar tab renders month grid and mobile agenda', function () {
    $ctx = makeRenderContext();

    makeRenderTask($ctx, ['title' => 'Tenggat Kalender', 'due_date' => today()]);

    Livewire::test(GeneralTaskboard::class)
        ->set('activeTab', 'calendar')
        // calendar partial header
        ->assertSee('Hari Ini')
        ->assertSee(now()->isoFormat('MMMM Y'))
        // calendar-month partial
        ->assertSee('Sen')
        ->assertSee('Min')
        // both calendar-month and calendar-agenda render the day cell
        ->assertSeeHtml('gcal-'.today()->format('Y-m-d'))
        ->assertSeeHtml('gcal-m-'.today()->format('Y-m-d'))
        ->assertSee('Tenggat Kalender');
});

test('general taskboard renders its empty state when no space has lists', function () {
    $user = User::factory()->create(['role' => 'administrator']);
    $this->actingAs($user);

    Livewire::test(GeneralTaskboard::class)
        ->assertSee('Belum ada list')
        ->assertSee('Mulai dengan membuat Space dan List pertama Anda.');
});

// ─── My tasks ──────────────────────────────────────────────────────

test('my tasks list view renders overdue, grouped and status sections', function () {
    $ctx = makeRenderContext();

    $overdue = makeRenderTask($ctx, ['title' => 'Tugas Kedaluwarsa', 'due_date' => now()->subDays(3), 'priority' => 'urgent']);
    $overdue->assignees()->attach($ctx['user']->id);

    $done = makeRenderTask($ctx, ['title' => 'Tugas Rampung', 'task_status_id' => $ctx['closedStatus']->id, 'due_date' => now()]);
    $done->assignees()->attach($ctx['user']->id);

    Livewire::test(MyTasks::class)
        // header partial
        ->assertSee('Tugas Saya')
        ->assertSee('Semua Prioritas')
        // overdue-section partial
        ->assertSee('Tugas Terlambat')
        ->assertSee('terlambat')
        ->assertSee('Tugas Kedaluwarsa')
        // grouped-list + task-card partials
        ->assertSee('Selesai')
        ->assertSee('Tugas Rampung');
});

test('my tasks calendar view renders the grid and mobile agenda', function () {
    $ctx = makeRenderContext();

    $task = makeRenderTask($ctx, ['title' => 'Agenda Saya', 'due_date' => today()]);
    $task->assignees()->attach($ctx['user']->id);

    Livewire::test(MyTasks::class)
        ->call('switchView', 'calendar')
        ->assertSee('Hari Ini')
        ->assertSee('Mon')
        ->assertSeeHtml('my-cal-'.today()->format('Y-m-d'))
        ->assertSeeHtml('my-cal-m-'.today()->format('Y-m-d'))
        ->assertSee('Agenda Saya');
});

test('my tasks renders its empty state', function () {
    makeRenderContext();

    Livewire::test(MyTasks::class)
        ->assertSee('Tidak ada tugas')
        ->assertSee('Anda tidak memiliki tugas yang ditugaskan saat ini.');
});

// ─── List notes ────────────────────────────────────────────────────

test('list notes renders note cards with file and link attachments plus modals', function () {
    $ctx = makeRenderContext();

    $note = $ctx['list']->notes()->create([
        'created_by' => $ctx['user']->id,
        'title' => 'Catatan Rapat',
        'content' => 'Isi catatan rapat',
        'position' => 0,
    ]);

    $note->attachments()->create([
        'user_id' => $ctx['user']->id,
        'filename' => 'notulen.pdf',
        'path' => 'list-note-attachments/notulen.pdf',
        'mime_type' => 'application/pdf',
        'size' => 2048,
        'is_link' => false,
    ]);

    $note->attachments()->create([
        'user_id' => $ctx['user']->id,
        'filename' => 'Tautan Referensi',
        'path' => 'https://example.test/referensi',
        'mime_type' => 'link',
        'size' => 0,
        'is_link' => true,
    ]);

    Livewire::test(ListNotes::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        // note-card partial
        ->assertSee('Catatan Rapat')
        ->assertSee('Isi catatan rapat')
        ->assertSee('notulen.pdf')
        ->assertSee('Tautan Referensi')
        ->assertSee('Lampirkan file')
        // create-modal + edit-modals partials
        ->assertSee('Tambah Catatan')
        ->assertSee('Edit Catatan')
        ->assertSee('Tambah Link')
        ->assertSee('Hapus Catatan?')
        ->assertSee('Hapus Lampiran?');
});

test('list notes renders its empty state', function () {
    $ctx = makeRenderContext();

    Livewire::test(ListNotes::class, ['space' => $ctx['space'], 'taskList' => $ctx['list']])
        ->assertSee('Belum ada catatan')
        ->assertSee('Tambah catatan untuk menyimpan informasi, link, dan');
});

// ─── User index ────────────────────────────────────────────────────

test('user index renders the table with role badges and every modal', function () {
    $admin = User::factory()->create(['role' => 'super_user', 'name' => 'Super Admin']);
    $this->actingAs($admin);

    User::factory()->create(['role' => 'administrator', 'name' => 'Rina Admin', 'position' => 'Kepala Divisi']);
    User::factory()->create(['role' => 'manager', 'name' => 'Tono Manager']);
    User::factory()->create(['role' => 'member', 'name' => 'Sari Member']);

    Livewire::test(UserIndex::class)
        // user-table partial
        ->assertSee('User Management')
        ->assertSee('Bergabung')
        ->assertSee('Rina Admin')
        ->assertSee('Kepala Divisi')
        ->assertSee('Administrator')
        ->assertSee('Manager')
        ->assertSee('Member')
        ->assertSee('Anda')
        // create-modal + edit-modal partials
        ->assertSee('Tambah Pengguna Baru')
        ->assertSee('Edit Pengguna')
        ->assertSee('Reset Password')
        ->assertSee('Hapus Pengguna?');
});

test('user index renders the no-match state when a filter excludes everyone', function () {
    $admin = User::factory()->create(['role' => 'super_user']);
    $this->actingAs($admin);

    Livewire::test(UserIndex::class)
        ->set('search', 'tidak-ada-pengguna-seperti-ini')
        ->assertSee('Tidak ada pengguna yang cocok dengan filter.');
});
