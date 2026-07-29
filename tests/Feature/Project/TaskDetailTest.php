<?php

use App\Livewire\Project\TaskDetail;
use App\Models\Project\Task;
use App\Models\Project\TaskAttachment;
use App\Models\Project\TaskChecklist;
use App\Models\Project\TaskChecklistItem;
use App\Models\Project\TaskComment;
use App\Models\Project\TaskLabel;
use App\Models\Project\TaskStatus;
use App\Models\Project\TimeTracking;
use App\Models\Project\Workspace;
use App\Models\User;
use Livewire\Livewire;

/**
 * Builds an owner + a task with two statuses, and returns everything needed by the tests.
 *
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
