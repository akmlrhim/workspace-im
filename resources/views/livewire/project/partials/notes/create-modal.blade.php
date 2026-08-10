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
          <div
            class="flex items-center justify-between rounded-lg border border-zinc-100 px-2.5 py-1.5 dark:border-zinc-800"
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
          <div
            class="flex items-center justify-between rounded-lg border border-zinc-100 px-2.5 py-1.5 dark:border-zinc-800"
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
        <flux:button variant="filled" size="sm" wire:click="addStagedLink" class="shrink-0">Tambah
        </flux:button>
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
