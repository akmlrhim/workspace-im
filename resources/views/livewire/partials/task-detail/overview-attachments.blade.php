<div>
  <h3 class="mb-3 text-sm font-semibold text-zinc-700 dark:text-zinc-300">
    Lampiran
    @if ($attachments->isNotEmpty())
      <span class="ml-1 text-zinc-400">({{ $attachments->count() }})</span>
    @endif
  </h3>

  @if ($canManage)
    <div x-data="fileUpload()">
      <div class="mb-1 grid grid-cols-2 gap-2" wire:loading.class="opacity-50 pointer-events-none"
        wire:target="uploadFiles,uploadAttachment"
        x-on:livewire-upload-start="resetFeedback(); uploading = true"
        x-on:livewire-upload-finish="uploading = false; progress = 100"
        x-on:livewire-upload-error="uploading = false; progress = 0; error = 'Gagal mengunggah file. Coba lagi.'"
        x-on:livewire-upload-progress="uploading = true; progress = Number($event.detail?.progress ?? 0)">
        <label
          class="relative flex cursor-pointer flex-col items-center justify-center gap-1 overflow-hidden rounded-lg border-2 border-dashed border-zinc-300 px-3 py-3 text-center text-sm text-zinc-500 transition-colors hover:border-indigo-400 hover:bg-indigo-50/30 hover:text-zinc-700 dark:border-zinc-600 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10 dark:hover:text-zinc-300">
          <span x-show="!uploading" x-cloak>
            <flux:icon name="cloud-arrow-up" class="size-5" />
          </span>
          <span x-show="uploading" x-cloak>
            <flux:icon name="arrow-path" class="size-5 animate-spin text-indigo-500" />
          </span>
          <span x-show="!uploading" x-cloak class="font-medium">Upload file</span>
          <span x-show="uploading" x-cloak class="font-medium text-indigo-500">Mengupload... <span
              x-text="progress + '%'">0%</span></span>
          <input type="file" wire:model="uploadFiles" multiple class="hidden" @change.capture="validate($event)"
            accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip" />
        </label>
        <button type="button" wire:click="$toggle('showLinkForm')"
          class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-zinc-300 px-3 py-3 text-sm text-zinc-500 transition-colors hover:border-indigo-400 hover:bg-indigo-50/30 hover:text-zinc-700 dark:border-zinc-600 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/10 dark:hover:text-zinc-300">
          <flux:icon name="link" class="size-5" />
          <span class="font-medium">Tambah link</span>
        </button>
      </div>
      <div x-show="uploading" x-cloak class="mb-2 h-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
        <div class="h-full rounded-full bg-indigo-500 transition-all duration-150" :style="`width: ${progress}%`"></div>
      </div>
      <p x-show="error" x-text="error" x-cloak class="mb-2 text-[11px] font-medium text-red-500"></p>
      <p class="mb-3 text-[11px] text-zinc-400 dark:text-zinc-500">
        Maks. {{ round(config('erp.attachments.max_size_kb') / 1024) }} MB · Gambar, PDF, Dokumen Office, Teks, CSV, ZIP
      </p>

      @if ($showLinkForm)
        <div class="mb-3 space-y-2 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
          <flux:input type="url" wire:model="newLinkUrl" placeholder="https://example.com" size="sm" />
          <flux:input wire:model="newLinkLabel" placeholder="Label (opsional)" size="sm" />
          <div class="flex justify-end gap-2">
            <flux:button size="xs" variant="ghost" wire:click="$set('showLinkForm', false)">Batal
            </flux:button>
            <flux:button size="xs" variant="primary" wire:click="addLinkAttachment">Simpan</flux:button>
          </div>
        </div>
      @endif
    </div>
  @endif

  @forelse ($attachments as $attachment)
    <div class="group flex items-center justify-between rounded-lg px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800"
      wire:key="attach-{{ $attachment->id }}">
      <a href="{{ $attachment->is_link ? $attachment->path : Storage::disk('public')->url($attachment->path) }}"
        target="_blank" rel="noopener noreferrer"
        class="flex min-w-0 items-center gap-2 text-zinc-700 hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400 transition-colors">
        @if ($attachment->is_link)
          <flux:icon name="link" class="size-4 shrink-0 text-indigo-400" />
        @elseif (str_starts_with($attachment->mime_type, 'image/'))
          <flux:icon name="photo" class="size-4 shrink-0 text-indigo-400" />
        @else
          <flux:icon name="document" class="size-4 shrink-0 text-zinc-400" />
        @endif
        <span class="truncate text-sm">{{ $attachment->filename }}</span>
        @if (!$attachment->is_link)
          <span class="shrink-0 text-xs text-zinc-400">{{ number_format(($attachment->size ?? 0) / 1024, 1) }}
            KB</span>
        @endif
        <flux:icon name="arrow-top-right-on-square" class="size-3.5 shrink-0 text-zinc-400" />
      </a>
      <div class="flex shrink-0 items-center gap-1">
        @if (!$attachment->is_link)
          <a href="{{ Storage::disk('public')->url($attachment->path) }}" download="{{ $attachment->filename }}"
            class="text-zinc-400 hover:text-indigo-500 transition-all" title="Download">
            <flux:icon name="arrow-down-tray" class="size-4" />
          </a>
        @endif
        @if ($canManage)
          <flux:button icon="trash" size="xs" variant="ghost" wire:click="deleteAttachment({{ $attachment->id }})"
            class="text-red-500" />
        @endif
      </div>
    </div>
  @empty
    <p class="text-xs italic text-zinc-400">Belum ada lampiran.</p>
  @endforelse
</div>
