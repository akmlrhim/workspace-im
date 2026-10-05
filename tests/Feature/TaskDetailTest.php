<?php

use App\Livewire\TaskDetail;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Models\TaskChecklist;
use App\Models\TaskChecklistItem;
use App\Models\TaskComment;
use App\Models\TaskLabel;
use App\Models\TaskStatus;
use App\Models\TimeTracking;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * @return array{owner: User, task: Task, open: TaskStatus, done: TaskStatus, workspace: Workspace}
 */
function taskDetailFixture(): array
{
    $owner = User::factory()->create(['role' => 'member']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $owner->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);
    $list->members()->attach($owner->id);

    $open = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Open', 'position' => 0, 'type' => 'open']);
    $done = TaskStatus::create(['task_list_id' => $list->id, 'name' => 'Done', 'position' => 1, 'type' => 'closed']);

    $task = Task::create([
        'task_list_id' => $list->id,
        'task_status_id' => $open->id,
        'title' => 'Refactor target',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    return compact('owner', 'task', 'open', 'done', 'workspace');
}

test('it renders the detail view with every tab partial', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->assertOk()
        ->assertSet('taskTitle', 'Refactor target')
        ->assertSee('Overview')
        ->assertSee('Checklist')
        ->assertSee('Komentar')
        ->assertSee('Aktivitas')
        ->assertSee('Deskripsi / Catatan')
        ->assertSee('Lampiran')
        ->assertSee('Time Tracking')
        ->assertSee('Riwayat Aktivitas');
});

test('it updates core task fields', function () {
    ['owner' => $owner, 'task' => $task, 'done' => $done] = taskDetailFixture();

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('taskTitle', 'Judul baru')
        ->call('saveTitle')
        ->set('taskDescription', 'Catatan singkat')
        ->call('saveDescription')
        ->call('updateStatus', $done->id)
        ->call('updatePriority', 'urgent')
        ->set('taskDueDate', '2026-08-01')
        ->call('updateDueDate');

    $task->refresh();

    expect($task->title)->toBe('Judul baru')
        ->and($task->description)->toBe('Catatan singkat')
        ->and($task->task_status_id)->toBe($done->id)
        ->and($task->priority)->toBe('urgent')
        ->and($task->due_date->format('Y-m-d'))->toBe('2026-08-01');
});

test('it manages comments and replies', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    $component = Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('newComment', 'Komentar pertama')
        ->call('addComment')
        ->assertSet('newComment', '');

    $comment = TaskComment::where('task_id', $task->id)->firstOrFail();

    $component->call('startReply', $comment->id)
        ->set('replyBody', 'Balasan')
        ->call('addReply')
        ->assertSet('replyingToCommentId', null);

    expect(TaskComment::where('parent_id', $comment->id)->count())->toBe(1);

    $reply = TaskComment::where('parent_id', $comment->id)->firstOrFail();

    $component->call('deleteReply', $reply->id)
        ->call('deleteComment', $comment->id);

    expect(TaskComment::where('task_id', $task->id)->count())->toBe(0);
});

test('it manages checklists and their items', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    $component = Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('newChecklistName', 'Persiapan')
        ->call('addChecklist')
        ->assertSet('newChecklistName', '');

    $checklist = TaskChecklist::where('task_id', $task->id)->firstOrFail();

    $component->call('openAddChecklistItem', $checklist->id)
        ->set('newChecklistItemTitle', 'Item pertama')
        ->call('addChecklistItem');

    $item = TaskChecklistItem::where('task_checklist_id', $checklist->id)->firstOrFail();

    $component->call('toggleChecklistItem', $item->id)
        ->call('editChecklistItemTitle', $item->id, 'Item diperbarui');

    expect($item->refresh()->is_completed)->toBeTrue()
        ->and($item->title)->toBe('Item diperbarui');

    $component->call('deleteChecklistItem', $item->id)
        ->call('deleteChecklist', $checklist->id);

    expect(TaskChecklist::where('task_id', $task->id)->count())->toBe(0);
});

test('it opens the checklist item panel and stores a link attachment on the item', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    $checklist = TaskChecklist::create(['task_id' => $task->id, 'name' => 'Persiapan', 'position' => 0]);
    $item = TaskChecklistItem::create([
        'task_checklist_id' => $checklist->id,
        'title' => 'Item',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->call('openChecklistItemPanel', $item->id)
        ->assertSet('activeChecklistItemId', $item->id)
        ->assertSee('Sematkan Link')
        ->set('newItemLinkUrl', 'https://example.com/spec')
        ->set('newItemLinkLabel', 'Spesifikasi')
        ->call('addChecklistItemLink')
        ->assertSet('newItemLinkUrl', '')
        ->call('closeChecklistItemPanel')
        ->assertSet('activeChecklistItemId', null);

    $attachment = TaskAttachment::where('task_checklist_item_id', $item->id)->firstOrFail();

    expect($attachment->task_id)->toBe($task->id)
        ->and($attachment->is_link)->toBeTrue()
        ->and($attachment->filename)->toBe('Spesifikasi')
        ->and($attachment->path)->toBe('https://example.com/spec');
});

test('it stores and deletes task level link attachments', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    $component = Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('newLinkUrl', 'https://example.com')
        ->call('addLinkAttachment')
        ->assertSet('showLinkForm', false);

    $attachment = TaskAttachment::where('task_id', $task->id)->firstOrFail();

    expect($attachment->is_link)->toBeTrue()
        ->and($attachment->filename)->toBe('https://example.com')
        ->and($attachment->task_checklist_item_id)->toBeNull();

    $component->call('deleteAttachment', $attachment->id);

    expect(TaskAttachment::where('task_id', $task->id)->count())->toBe(0);
});

test('it creates and toggles labels', function () {
    ['owner' => $owner, 'task' => $task, 'workspace' => $workspace] = taskDetailFixture();

    $component = Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('newLabelName', 'Backend')
        ->set('newLabelColor', '#ef4444')
        ->call('createLabel')
        ->assertSet('showLabelForm', false);

    $label = TaskLabel::where('workspace_id', $workspace->id)->firstOrFail();

    expect($task->labels()->count())->toBe(1);

    $component->call('toggleLabel', $label->id);

    expect($task->labels()->count())->toBe(0);
});

test('it starts and stops the timer', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    $component = Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->call('startTimer');

    $timer = TimeTracking::where('task_id', $task->id)->firstOrFail();

    $component->assertSet('activeTimerId', $timer->id)
        ->call('stopTimer')
        ->assertSet('activeTimerId', null);

    expect($timer->refresh()->stopped_at)->not->toBeNull();
});

test('a non member sees the task in read only mode and cannot change it', function () {
    ['task' => $task] = taskDetailFixture();

    $outsider = User::factory()->create(['role' => 'member']);

    Livewire::actingAs($outsider)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->assertSee('lihat saja')
        ->set('taskTitle', 'Judul dibajak')
        ->call('saveTitle');

    expect($task->refresh()->title)->toBe('Refactor target');
});

test('it stores task level file uploads on the public disk', function () {
    Storage::fake('public');

    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('uploadFiles', [
            UploadedFile::fake()->create('spesifikasi.pdf', 200, 'application/pdf'),
            UploadedFile::fake()->image('sketsa.jpg'),
        ])
        ->assertSet('uploadFiles', []);

    $attachments = TaskAttachment::where('task_id', $task->id)
        ->whereNull('task_comment_id')
        ->whereNull('task_checklist_item_id')
        ->get();

    expect($attachments)->toHaveCount(2);

    $pdf = $attachments->firstWhere('filename', 'spesifikasi.pdf');

    expect($pdf)->not->toBeNull()
        ->and($pdf->is_link)->toBeFalse()
        ->and($pdf->user_id)->toBe($owner->id);

    Storage::disk('public')->assertExists($pdf->path);
});

test('it rejects an oversized task file without persisting anything', function () {
    Storage::fake('public');

    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    $oversized = config('project.attachments.max_size_kb') + 1;

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('uploadFiles', [UploadedFile::fake()->create('terlalu-besar.pdf', $oversized, 'application/pdf')])
        ->assertSet('uploadFiles', []);

    expect(TaskAttachment::where('task_id', $task->id)->count())->toBe(0);
});

test('it accepts office documents whose contents sniff as another mime type', function () {
    Storage::fake('public');

    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('uploadFiles', [
            UploadedFile::fake()->create('laporan.docx', 40, 'application/zip'),
            UploadedFile::fake()->create('data.csv', 10, 'text/plain'),
        ])
        ->assertSet('uploadFiles', []);

    $filenames = TaskAttachment::where('task_id', $task->id)->pluck('filename');

    expect($filenames)->toContain('laporan.docx')
        ->and($filenames)->toContain('data.csv');
});

test('it still saves a task edit when the broadcaster is unreachable', function () {
    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    Broadcast::shouldReceive('queue')
        ->andThrow(new BroadcastException('Pusher unreachable'));

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('taskTitle', 'Tetap tersimpan')
        ->call('saveTitle');

    expect($task->refresh()->title)->toBe('Tetap tersimpan');
});

test('it rejects a task file with an unsupported format', function () {
    Storage::fake('public');

    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->set('uploadFiles', [UploadedFile::fake()->create('virus.exe', 20, 'application/x-msdownload')])
        ->assertSet('uploadFiles', []);

    expect(TaskAttachment::where('task_id', $task->id)->count())->toBe(0);
});

test('it stores file uploads on an open checklist item', function () {
    Storage::fake('public');

    ['owner' => $owner, 'task' => $task] = taskDetailFixture();

    $checklist = TaskChecklist::create(['task_id' => $task->id, 'name' => 'Persiapan', 'position' => 0]);
    $item = TaskChecklistItem::create([
        'task_checklist_id' => $checklist->id,
        'title' => 'Item',
        'position' => 0,
        'created_by' => $owner->id,
    ]);

    Livewire::actingAs($owner)
        ->test(TaskDetail::class, ['taskId' => $task->id])
        ->call('openChecklistItemPanel', $item->id)
        ->set('activeItemFiles', [UploadedFile::fake()->create('foto.png', 300, 'image/png')])
        ->assertSet('activeItemFiles', []);

    $attachment = TaskAttachment::where('task_checklist_item_id', $item->id)->firstOrFail();

    expect($attachment->task_id)->toBe($task->id)
        ->and($attachment->filename)->toBe('foto.png');

    Storage::disk('public')->assertExists($attachment->path);
});
