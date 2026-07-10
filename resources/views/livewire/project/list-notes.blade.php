<div>
  {{-- Header --}}
  <div class="mb-6">
    @include('livewire.project.partials.breadcrumb')

    <div class="mt-3 flex items-center justify-between gap-3 mb-4">
      <h1 class="hidden lg:block lg:text-2xl font-bold text-zinc-900 dark:text-white">{{ $taskList->name }}</h1>
    </div>

    @include('livewire.project.partials.view-toggle', ['active' => 'notes'])
  </div>

  {{-- Toolbar --}}
  <div class="mb-4 flex items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <span
        class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:ring-indigo-700">
        <flux:icon name="document-text" class="size-3.5" />
        {{ $this->notes->count() }} Catatan
      </span>
    </div>

    @if ($canManage)
      <flux:button variant="primary" size="sm" icon="plus" wire:click="$set('showNewNoteForm', true)">
        Tambah Catatan
      </flux:button>
    @endif
  </div>

  {{-- Notes list --}}
  @if ($this->notes->isEmpty())
    <div
      class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 py-16 dark:border-zinc-700">
      <flux:icon name="document-text" class="size-10 text-zinc-300 dark:text-zinc-600" />
      <p class="mt-3 text-sm font-medium text-zinc-500 dark:text-zinc-400">Belum ada catatan</p>
      @if ($canManage)
        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Tambah catatan untuk menyimpan informasi, link, dan
          file.</p>
      @endif
    </div>
  @else
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
      @foreach ($this->notes as $note)
        <div wire:key="note-{{ $note->id }}"
          class="flex flex-col rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

          <div class="flex flex-1 flex-col p-4">
            <div class="flex items-start justify-between gap-2">
              <h3 class="min-w-0 flex-1 break-words text-base font-semibold text-zinc-900 dark:text-zinc-100">
                {{ $note->title }}</h3>

              @if ($canManage)
                <flux:dropdown position="bottom" align="end" class="shrink-0">
                  <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" class="h-7 w-7 p-0 text-zinc-400" />
                  <flux:menu>
                    <flux:menu.item icon="pencil-square" wire:click="openEditNote({{ $note->id }})">Edit</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item variant="danger" icon="trash" wire:click="confirmDeleteNote({{ $note->id }})">
                      Hapus
                    </flux:menu.item>
                  </flux:menu>
                </flux:dropdown>
              @endif
            </div>

            @if ($note->content)
              <p class="mt-2 whitespace-pre-wrap break-words text-sm text-zinc-600 dark:text-zinc-300">{{ $note->content }}</p>
            @endif

            {{-- Attachments --}}
            @if ($note->attachments->isNotEmpty())
              <div class="mt-3 space-y-1">
                @foreach ($note->attachments as $attachment)
                  <div
                    class="group flex items-center justify-between rounded-lg border border-zinc-100 px-2.5 py-1.5 dark:border-zinc-800"
                    wire:key="note-att-{{ $attachment->id }}">
                    <a href="{{ $attachment->is_link ? $attachment->path : Storage::disk('public')->url($attachment->path) }}"
                      target="_blank" rel="noopener noreferrer"
                      class="flex min-w-0 items-center gap-2 text-zinc-700 transition-colors hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400">
                      @if ($attachment->is_link)
                        <flux:icon name="link" class="size-4 shrink-0 text-indigo-400" />
                      @elseif (str_starts_with($attachment->mime_type, 'image/'))
                        <flux:icon name="photo" class="size-4 shrink-0 text-zinc-400" />
                      @else
                        <flux:icon name="document" class="size-4 shrink-0 text-zinc-400" />
                      @endif
                      <span class="truncate text-sm">{{ $attachment->filename }}</span>
                      @unless ($attachment->is_link)
                        <span class="shrink-0 text-xs text-zinc-400">{{ number_format(($attachment->size ?? 0) / 1024, 1) }}
                          KB</span>
                      @endunless
                    </a>
                    @if ($canManage)
                      <button type="button" wire:click="confirmDeleteAttachment({{ $attachment->id }})"
                        class="ml-2 shrink-0 text-zinc-400 opacity-0 transition-all hover:text-red-500 group-hover:opacity-100">
                        <flux:icon name="x-mark" class="size-4" />
                      </button>
                    @endif
                  </div>
                @endforeach
              </div>
            @endif

            {{-- Attach actions --}}
            @if ($canManage)
              <div class="mt-3 flex items-center gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
                <label
                  class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-medium text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-indigo-600 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-indigo-400"
                  wire:target="uploadFiles.{{ $note->id }}" wire:loading.class="opacity-50 pointer-events-none">
                  <flux:icon name="paper-clip" class="size-3.5" />
                  <span wire:loading.remove wire:target="uploadFiles.{{ $note->id }}">Lampirkan file</span>
                  <span wire:loading wire:target="uploadFiles.{{ $note->id }}">Mengunggah...</span>
                  <input type="file" multiple wire:model="uploadFiles.{{ $note->id }}" class="hidden" />
                </label>
                <button type="button" wire:click="openLinkForm({{ $note->id }})"
                  class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-medium text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-indigo-600 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-indigo-400">
                  <flux:icon name="link" class="size-3.5" />
                  Tambah link
                </button>
              </div>
              @error('uploadFiles.' . $note->id . '.*')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
              @enderror
            @endif

            {{-- Meta --}}
            <div class="mt-3 flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
              <flux:avatar circle :name="$note->creator?->name ?? '?'" :src="$note->creator?->avatar ?? null" size="xs" />
              <span>{{ $note->creator?->name ?? 'Pengguna' }}</span>
              <span>·</span>
              <span>{{ $note->updated_at?->locale('id')->diffForHumans() }}</span>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif

  {{-- ─── Modals ─────────────────────────────────────────────── --}}

  @if ($canManage)
    {{-- Add note modal --}}
    <flux:modal wire:model="showNewNoteForm" class="w-full max-w-lg">
      <div class="space-y-4">
        <flux:heading size="lg">Tambah Catatan</flux:heading>

        <flux:field>
          <flux:label>Judul</flux:label>
          <flux:input wire:model="newNoteTitle" placeholder="Judul catatan..." maxlength="255" />
        </flux:field>

        <flux:field>
          <flux:label>Isi <span class="font-normal text-zinc-400">(opsional)</span></flux:label>
          <flux:textarea wire:model="newNoteContent" placeholder="Tulis catatan di sini..." rows="4" />
        </flux:field>

        {{-- Staged attachments --}}
        @if (!empty($newNoteFiles) || !empty($newNoteLinks))
          <div class="space-y-1">
            @foreach ($newNoteLinks as $i => $link)
              <div class="flex items-center justify-between rounded-lg border border-zinc-100 px-2.5 py-1.5 dark:border-zinc-800"
                wire:key="staged-link-{{ $i }}">
                <span class="flex min-w-0 items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                  <flux:icon name="link" class="size-4 shrink-0 text-indigo-400" />
                  <span class="truncate">{{ $link['label'] }}</span>
                </span>
                <button type="button" wire:click="removeStagedLink({{ $i }})"
                  class="ml-2 shrink-0 text-zinc-400 transition-colors hover:text-red-500">
                  <flux:icon name="x-mark" class="size-4" />
                </button>
              </div>
            @endforeach
            @foreach ($newNoteFiles as $i => $file)
              <div class="flex items-center justify-between rounded-lg border border-zinc-100 px-2.5 py-1.5 dark:border-zinc-800"
                wire:key="staged-file-{{ $i }}">
                <span class="flex min-w-0 items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                  <flux:icon name="document" class="size-4 shrink-0 text-zinc-400" />
                  <span class="truncate">{{ $file->getClientOriginalName() }}</span>
                </span>
                <button type="button" wire:click="removeStagedFile({{ $i }})"
                  class="ml-2 shrink-0 text-zinc-400 transition-colors hover:text-red-500">
                  <flux:icon name="x-mark" class="size-4" />
                </button>
              </div>
            @endforeach
          </div>
        @endif

        {{-- Inline link adder --}}
        <div x-data="{ showLink: false }">
          <div x-show="showLink" x-cloak class="mb-2 flex flex-col gap-2 sm:flex-row">
            <flux:input x-ref="linkUrl" wire:model="newNoteLinkUrl" type="url" placeholder="https://..."
              size="sm" class="flex-1" wire:keydown.enter.prevent="addStagedLink" />
            <flux:input wire:model="newNoteLinkLabel" placeholder="Label (opsional)" maxlength="255" size="sm"
              class="sm:w-40" wire:keydown.enter.prevent="addStagedLink" />
            <flux:button variant="filled" size="sm" wire:click="addStagedLink" class="shrink-0">Tambah</flux:button>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <label
              class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-indigo-600 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-indigo-400"
              wire:target="newNoteFiles" wire:loading.class="opacity-50 pointer-events-none">
              <flux:icon name="paper-clip" class="size-3.5" />
              <span wire:loading.remove wire:target="newNoteFiles">Lampirkan file</span>
              <span wire:loading wire:target="newNoteFiles">Mengunggah...</span>
              <input type="file" multiple wire:model="newNoteFiles" class="hidden" />
            </label>
            <button type="button" @click="showLink = !showLink; $nextTick(() => showLink && $refs.linkUrl?.focus())"
              class="inline-flex items-center gap-1.5 rounded-lg border border-zinc-200 px-2.5 py-1 text-xs font-medium text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-indigo-600 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-indigo-400">
              <flux:icon name="link" class="size-3.5" />
              Tambah link
            </button>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <flux:button variant="ghost" wire:click="cancelNewNote">Batal</flux:button>
          <flux:button variant="primary" wire:click="createNote" wire:loading.attr="disabled" wire:target="createNote">
            Simpan
          </flux:button>
        </div>
      </div>
    </flux:modal>

    {{-- Edit note modal --}}
    <flux:modal wire:model="showEditNoteForm" class="w-full max-w-lg">
      <div class="space-y-4">
        <flux:heading size="lg">Edit Catatan</flux:heading>

        <flux:field>
          <flux:label>Judul</flux:label>
          <flux:input wire:model="editNoteTitle" placeholder="Judul catatan..." maxlength="255" />
        </flux:field>

        <flux:field>
          <flux:label>Isi <span class="font-normal text-zinc-400">(opsional)</span></flux:label>
          <flux:textarea wire:model="editNoteContent" placeholder="Tulis catatan di sini..." rows="4" />
        </flux:field>

        <div class="flex justify-end gap-2 pt-2">
          <flux:button variant="ghost" wire:click="$set('showEditNoteForm', false)">Batal</flux:button>
          <flux:button variant="primary" wire:click="updateNote" wire:loading.attr="disabled" wire:target="updateNote">
            Simpan
          </flux:button>
        </div>
      </div>
    </flux:modal>

    {{-- Add link modal --}}
    <flux:modal wire:model="showLinkForm" class="w-full max-w-md">
      <div class="space-y-4">
        <flux:heading size="lg">Tambah Link</flux:heading>

        <flux:field>
          <flux:label>URL</flux:label>
          <flux:input wire:model="newLinkUrl" type="url" placeholder="https://..." wire:keydown.enter="addLink" />
        </flux:field>

        <flux:field>
          <flux:label>Label <span class="font-normal text-zinc-400">(opsional)</span></flux:label>
          <flux:input wire:model="newLinkLabel" placeholder="Nama link..." maxlength="255"
            wire:keydown.enter="addLink" />
        </flux:field>

        <div class="flex justify-end gap-2 pt-2">
          <flux:button variant="ghost" wire:click="$set('showLinkForm', false)">Batal</flux:button>
          <flux:button variant="primary" wire:click="addLink" wire:loading.attr="disabled" wire:target="addLink">
            Simpan
          </flux:button>
        </div>
      </div>
    </flux:modal>

    {{-- Delete note confirm --}}
    <flux:modal wire:model="showDeleteNoteConfirm" class="w-full max-w-sm">
      <div class="space-y-4 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
          <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
        </div>
        <flux:heading size="lg">Hapus Catatan?</flux:heading>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
          Catatan beserta semua lampirannya akan dihapus. Tindakan ini tidak bisa dibatalkan.
        </p>
        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-center">
          <flux:button variant="ghost" class="w-full sm:w-auto"
            wire:click="$set('showDeleteNoteConfirm', false)">Batal</flux:button>
          <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteNote" wire:loading.attr="disabled"
            wire:target="deleteNote">Hapus</flux:button>
        </div>
      </div>
    </flux:modal>

    {{-- Delete attachment confirm --}}
    <flux:modal wire:model="showDeleteAttachmentConfirm" class="w-full max-w-sm">
      <div class="space-y-4 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
          <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
        </div>
        <flux:heading size="lg">Hapus Lampiran?</flux:heading>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
          Lampiran ini akan dihapus dari catatan. Tindakan ini tidak bisa dibatalkan.
        </p>
        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-center">
          <flux:button variant="ghost" class="w-full sm:w-auto"
            wire:click="$set('showDeleteAttachmentConfirm', false)">Batal</flux:button>
          <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteAttachment"
            wire:loading.attr="disabled" wire:target="deleteAttachment">Hapus</flux:button>
        </div>
      </div>
    </flux:modal>
  @endif
</div>
