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
            <flux:menu.item icon="pencil-square" wire:click="openEditNote({{ $note->id }})">Edit
            </flux:menu.item>
            <flux:menu.separator />
            <flux:menu.item variant="danger" icon="trash" wire:click="confirmDeleteNote({{ $note->id }})">
              Hapus
            </flux:menu.item>
          </flux:menu>
        </flux:dropdown>
      @endif
    </div>

    @if ($note->content)
      <p class="mt-2 whitespace-pre-wrap break-words text-sm text-zinc-600 dark:text-zinc-300">
        {{ $note->content }}</p>
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
