<?php

use App\Livewire\Project\ListNotes;
use App\Models\Project\ListNote;
use App\Models\Project\ListNoteAttachment;
use App\Models\Project\Workspace;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function makeNotesList(): array
{
    $admin = User::factory()->create(['role' => 'administrator']);

    $workspace = Workspace::create(['name' => 'WS', 'owner_id' => $admin->id]);
    $space = $workspace->spaces()->create(['name' => 'Engineering', 'position' => 0]);
    $list = $space->lists()->create(['name' => 'Sprint', 'position' => 0]);

    return [$admin, $space, $list];
}

test('a manager can create a note', function () {
    [$admin, $space, $list] = makeNotesList();

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->set('newNoteTitle', 'Catatan rapat')
        ->set('newNoteContent', 'Poin penting dari rapat.')
        ->call('createNote')
        ->assertSet('showNewNoteForm', false)
        ->assertSet('newNoteTitle', '');

    $note = ListNote::where('task_list_id', $list->id)->first();

    expect($note)->not->toBeNull()
        ->and($note->title)->toBe('Catatan rapat')
        ->and($note->content)->toBe('Poin penting dari rapat.')
        ->and($note->created_by)->toBe($admin->id);
});

test('a manager can create a note with staged files and links at once', function () {
    Storage::fake('public');

    [$admin, $space, $list] = makeNotesList();

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->set('newNoteTitle', 'Catatan lengkap')
        ->set('newNoteFiles', [UploadedFile::fake()->create('lampiran.pdf', 100, 'application/pdf')])
        ->set('newNoteLinkUrl', 'https://example.com/spec')
        ->set('newNoteLinkLabel', 'Spesifikasi')
        ->call('addStagedLink')
        ->assertCount('newNoteLinks', 1)
        ->call('createNote')
        ->assertSet('newNoteFiles', [])
        ->assertSet('newNoteLinks', []);

    $note = ListNote::where('task_list_id', $list->id)->first();

    expect($note)->not->toBeNull()
        ->and($note->attachments)->toHaveCount(2);

    $link = $note->attachments->firstWhere('is_link', true);
    $file = $note->attachments->firstWhere('is_link', false);

    expect($link->path)->toBe('https://example.com/spec')
        ->and($link->filename)->toBe('Spesifikasi')
        ->and($file->filename)->toBe('lampiran.pdf');

    Storage::disk('public')->assertExists($file->path);
});

test('a staged link can be removed before saving the note', function () {
    [$admin, $space, $list] = makeNotesList();

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->set('newNoteLinkUrl', 'https://example.com')
        ->call('addStagedLink')
        ->assertCount('newNoteLinks', 1)
        ->call('removeStagedLink', 0)
        ->assertCount('newNoteLinks', 0);
});

test('creating a note requires a title', function () {
    [$admin, $space, $list] = makeNotesList();

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->set('newNoteTitle', '   ')
        ->call('createNote');

    expect(ListNote::where('task_list_id', $list->id)->count())->toBe(0);
});

test('a manager can update a note', function () {
    [$admin, $space, $list] = makeNotesList();

    $note = $list->notes()->create([
        'created_by' => $admin->id,
        'title' => 'Judul lama',
        'content' => 'Isi lama',
        'position' => 0,
    ]);

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->call('openEditNote', $note->id)
        ->assertSet('showEditNoteForm', true)
        ->assertSet('editNoteTitle', 'Judul lama')
        ->set('editNoteTitle', 'Judul baru')
        ->set('editNoteContent', 'Isi baru')
        ->call('updateNote')
        ->assertSet('showEditNoteForm', false);

    $note->refresh();

    expect($note->title)->toBe('Judul baru')
        ->and($note->content)->toBe('Isi baru');
});

test('a manager can add a link to a note', function () {
    [$admin, $space, $list] = makeNotesList();

    $note = $list->notes()->create([
        'created_by' => $admin->id,
        'title' => 'Catatan',
        'position' => 0,
    ]);

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->call('openLinkForm', $note->id)
        ->set('newLinkUrl', 'https://example.com/doc')
        ->set('newLinkLabel', 'Dokumen')
        ->call('addLink')
        ->assertSet('linkFormFor', null);

    $attachment = $note->attachments()->first();

    expect($attachment)->not->toBeNull()
        ->and($attachment->is_link)->toBeTrue()
        ->and($attachment->path)->toBe('https://example.com/doc')
        ->and($attachment->filename)->toBe('Dokumen');
});

test('an invalid url is rejected when adding a link', function () {
    [$admin, $space, $list] = makeNotesList();

    $note = $list->notes()->create([
        'created_by' => $admin->id,
        'title' => 'Catatan',
        'position' => 0,
    ]);

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->call('openLinkForm', $note->id)
        ->set('newLinkUrl', 'not-a-url')
        ->call('addLink');

    expect($note->attachments()->count())->toBe(0);
});

test('a manager can upload a file to a note', function () {
    Storage::fake('public');

    [$admin, $space, $list] = makeNotesList();

    $note = $list->notes()->create([
        'created_by' => $admin->id,
        'title' => 'Catatan',
        'position' => 0,
    ]);

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->set("uploadFiles.{$note->id}", [UploadedFile::fake()->create('laporan.pdf', 200, 'application/pdf')]);

    $attachment = $note->attachments()->first();

    expect($attachment)->not->toBeNull()
        ->and($attachment->is_link)->toBeFalse()
        ->and($attachment->filename)->toBe('laporan.pdf');

    Storage::disk('public')->assertExists($attachment->path);
});

test('deleting a note removes its stored files', function () {
    Storage::fake('public');

    [$admin, $space, $list] = makeNotesList();

    $note = $list->notes()->create([
        'created_by' => $admin->id,
        'title' => 'Catatan',
        'position' => 0,
    ]);

    Storage::disk('public')->put('list-note-attachments/file.pdf', 'content');

    $attachment = $note->attachments()->create([
        'user_id' => $admin->id,
        'filename' => 'file.pdf',
        'path' => 'list-note-attachments/file.pdf',
        'mime_type' => 'application/pdf',
        'size' => 100,
        'is_link' => false,
    ]);

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->call('confirmDeleteNote', $note->id)
        ->assertSet('showDeleteNoteConfirm', true)
        ->call('deleteNote')
        ->assertSet('showDeleteNoteConfirm', false);

    expect(ListNote::find($note->id))->toBeNull()
        ->and(ListNoteAttachment::find($attachment->id))->toBeNull();

    Storage::disk('public')->assertMissing('list-note-attachments/file.pdf');
});

test('a manager can delete an attachment', function () {
    [$admin, $space, $list] = makeNotesList();

    $note = $list->notes()->create([
        'created_by' => $admin->id,
        'title' => 'Catatan',
        'position' => 0,
    ]);

    $attachment = $note->attachments()->create([
        'user_id' => $admin->id,
        'filename' => 'Dokumen',
        'path' => 'https://example.com',
        'mime_type' => 'link',
        'size' => 0,
        'is_link' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->call('confirmDeleteAttachment', $attachment->id)
        ->assertSet('showDeleteAttachmentConfirm', true)
        ->call('deleteAttachment')
        ->assertSet('showDeleteAttachmentConfirm', false);

    expect(ListNoteAttachment::find($attachment->id))->toBeNull();
});

test('a guest without list membership cannot create a note', function () {
    [$admin, $space, $list] = makeNotesList();

    $guest = User::factory()->create(['role' => 'guest']);

    Livewire::actingAs($guest)
        ->test(ListNotes::class, ['space' => $space, 'taskList' => $list])
        ->set('newNoteTitle', 'Tidak boleh')
        ->call('createNote');

    expect(ListNote::where('task_list_id', $list->id)->count())->toBe(0);
});
