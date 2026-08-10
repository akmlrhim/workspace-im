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
      {{-- `whitespace-pre-wrap` merender spasi apa adanya, jadi isi catatan harus
        menempel pada tag pembuka. Kalau dipindah ke baris baru, newline dan
        indentasi Blade ikut tercetak dan baris pertama tampak menjorok. --}}
      <p class="mt-2 whitespace-pre-wrap break-words text-sm text-zinc-600 dark:text-zinc-300">{{ $note->content }}</p>
    @endif

    {{-- Attachments --}}
    @if ($note->attachments->isNotEmpty())
      <div class="mt-3 space-y-1">
        @foreach ($note->attachments as $attachment)
          {{-- Link dan file dibedakan warnanya: link mengarah keluar aplikasi,
            file tersimpan di server. Aksen indigo dipakai konsisten dengan
            ikon link dan warna hover tautan. --}}
          <div
            class="group flex items-center justify-between rounded-lg border px-2.5 py-1.5 {{ $attachment->is_link
                ? 'border-indigo-100 bg-indigo-50/50 dark:border-indigo-500/20 dark:bg-indigo-500/5'
                : 'border-zinc-100 dark:border-zinc-800' }}"
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
      <div class="mt-3" x-data="fileUpload()"
        x-on:livewire-upload-start="resetFeedback(); uploading = true"
        x-on:livewire-upload-finish="uploading = false; progress = 100"
        x-on:livewire-upload-error="uploading = false; progress = 0; error = 'Gagal mengunggah file. Coba lagi.'"
        x-on:livewire-upload-progress="uploading = true; progress = Number($event.detail?.progress ?? 0)">
        <div class="flex items-center gap-2 border-t border-zinc-100 pt-3 dark:border-zinc-800">
          <label
            class="relative inline-flex cursor-pointer items-center gap-1.5 overflow-hidden rounded-lg px-2 py-1 text-xs font-medium text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-indigo-600 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-indigo-400"
            wire:target="uploadFiles.{{ $note->id }}" wire:loading.class="opacity-50 pointer-events-none">
            <flux:icon name="paper-clip" class="size-3.5" />
            <span x-show="!uploading" x-cloak>Lampirkan file</span>
            <span x-show="uploading" x-cloak>Mengunggah... <span x-text="progress + '%'">0%</span></span>
            <input type="file" multiple wire:model="uploadFiles.{{ $note->id }}"
              @change.capture="validate($event)" class="hidden"
              accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip" />
          </label>
          <button type="button" wire:click="openLinkForm({{ $note->id }})"
            class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-xs font-medium text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-indigo-600 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-indigo-400">
            <flux:icon name="link" class="size-3.5" />
            Tambah link
          </button>
        </div>
        <div x-show="uploading" x-cloak class="mt-1.5 h-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
          <div class="h-full rounded-full bg-indigo-500 transition-all duration-150" :style="`width: ${progress}%`"></div>
        </div>
        <p x-show="error" x-text="error" x-cloak class="mt-1 text-xs text-red-500"></p>
        @error('uploadFiles.' . $note->id . '.*')
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
      </div>
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
