{{-- Expanded detail panel of a checklist item. Expects: $item, $task, $activeItem* state --}}
<div
  class="mx-3 mb-3 mt-1 space-y-4 rounded-lg border border-zinc-200/60 bg-white p-4 shadow-sm dark:border-zinc-700/50 dark:bg-zinc-800/80">

  <div>
    <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Assign
      Ke</label>
    @if ($task->assignees->isEmpty())
      <p class="text-xs text-zinc-400 italic">Belum ada anggota di task ini.</p>
    @else
      <div class="flex flex-wrap gap-2">
        @foreach ($task->assignees as $member)
          @php $checked = in_array($member->id, $activeItemAssigneeIds); @endphp
          <label
            class="flex cursor-pointer items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-medium transition-all duration-200
              {{ $checked ? 'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm dark:border-indigo-500 dark:bg-indigo-900/40 dark:text-indigo-200' : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400' }}">
            <input type="checkbox" wire:model="activeItemAssigneeIds" value="{{ $member->id }}"
              wire:change="updateChecklistItemAssignees" class="hidden" />
            <flux:avatar circle :name="$member->name" :initials="$member->initials()" :src="$member->avatar"
              size="xs" class="size-5" />
            {{ $member->name }}
          </label>
        @endforeach
      </div>
    @endif
  </div>

  <hr class="border-zinc-100 dark:border-zinc-700/50">

  <div class="flex flex-wrap gap-6">
    <div class="flex-1 min-w-[200px]">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tenggat
        Waktu</label>
      <div class="flex items-center gap-2">
        <flux:input type="date" wire:model="activeItemDueDate" onclick="this.showPicker()"
          wire:change="updateChecklistItemDueDate" size="sm" class="w-full max-w-[200px]" />
        @if ($activeItemDueDate)
          <button wire:click="clearChecklistItemDueDate"
            class="text-xs font-medium text-red-500 hover:text-red-600 transition-colors">Batal</button>
        @endif
      </div>
    </div>

    <div class="flex-1 min-w-[200px]">
      <label class="mb-2 block text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tambah
        Lampiran</label>
      <div class="grid grid-cols-2 gap-2" wire:loading.class="opacity-50 pointer-events-none"
        wire:target="activeItemFiles,uploadChecklistItemFiles">
        <label
          class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 py-2 text-xs font-medium text-zinc-600 transition-colors hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
          <span wire:loading.remove wire:target="activeItemFiles,uploadChecklistItemFiles">
            <flux:icon name="cloud-arrow-up" class="size-4" />
          </span>
          <span wire:loading wire:target="activeItemFiles,uploadChecklistItemFiles">
            <flux:icon name="arrow-path" class="size-4 animate-spin text-indigo-500" />
          </span>
          <span wire:loading.remove wire:target="activeItemFiles,uploadChecklistItemFiles">Upload File</span>
          <span wire:loading wire:target="activeItemFiles,uploadChecklistItemFiles" class="text-indigo-500">Mengupload...</span>
          <input type="file" wire:model="activeItemFiles" multiple class="hidden"
            accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip" />
        </label>
        <button type="button" wire:click="$toggle('showItemLinkForm')"
          class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border border-dashed border-zinc-300 bg-zinc-50 py-2 text-xs font-medium text-zinc-600 transition-colors hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 dark:border-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-400 dark:hover:border-indigo-500 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300">
          <flux:icon name="link" class="size-4" />
          <span>Sematkan Link</span>
        </button>
      </div>
      <p class="mt-1.5 text-[10px] text-zinc-400 dark:text-zinc-500">
        Maks. 5 MB · Gambar, PDF, Dokumen, Teks, ZIP
      </p>
    </div>
  </div>

  @if ($showItemLinkForm)
    <div class="rounded-lg border border-indigo-100 bg-indigo-50/50 p-3 dark:border-indigo-900/30 dark:bg-indigo-900/10">
      <div class="space-y-2">
        <flux:input type="url" wire:model="newItemLinkUrl" placeholder="https://..." size="sm" />
        <flux:input wire:model="newItemLinkLabel" placeholder="Teks Label (Opsional)" size="sm" />
      </div>
      <div class="mt-3 flex justify-end gap-2">
        <flux:button size="sm" variant="ghost" wire:click="$set('showItemLinkForm', false)">
          Batal</flux:button>
        <flux:button size="sm" variant="primary" wire:click="addChecklistItemLink">Simpan
          Link
        </flux:button>
      </div>
    </div>
  @endif

  @if ($activeItemAttachments->isNotEmpty())
    <div class="mt-4 rounded-lg border border-zinc-200 bg-zinc-50/50 p-2 dark:border-zinc-700 dark:bg-zinc-800/30">
      <p class="mb-2 px-1 text-[10px] font-bold uppercase tracking-wider text-zinc-400">Daftar
        Lampiran ({{ $activeItemAttachments->count() }})</p>
      <div class="space-y-1">
        @foreach ($activeItemAttachments as $att)
          <div
            class="group flex items-center justify-between rounded-md px-2 py-1.5 transition-colors hover:bg-white dark:hover:bg-zinc-700 shadow-sm"
            wire:key="cli-att-{{ $att->id }}">
            <a href="{{ $att->is_link ? $att->path : Storage::disk('public')->url($att->path) }}" target="_blank"
              rel="noopener noreferrer"
              class="flex min-w-0 items-center gap-2 text-xs font-medium text-zinc-700 hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400 transition-colors">
              @if ($att->is_link)
                <flux:icon name="link" class="size-4 shrink-0 text-indigo-500" />
              @elseif (str_starts_with($att->mime_type, 'image/'))
                <flux:icon name="photo" class="size-4 shrink-0 text-indigo-500" />
              @else
                <flux:icon name="document" class="size-4 shrink-0 text-zinc-400" />
              @endif
              <span class="truncate">{{ $att->filename }}</span>
              @if (!$att->is_link)
                <span class="shrink-0 text-zinc-400 font-normal">{{ number_format(($att->size ?? 0) / 1024, 1) }}
                  KB</span>
              @endif
            </a>
            <div class="flex shrink-0 items-center gap-2 ml-3">
              @if (!$att->is_link)
                <a href="{{ Storage::disk('public')->url($att->path) }}" download="{{ $att->filename }}"
                  class="text-zinc-400 hover:text-indigo-600 transition-colors" title="Download">
                  <flux:icon name="arrow-down-tray" class="size-4" />
                </a>
              @endif
              <button wire:click="deleteChecklistItemAttachment({{ $att->id }})"
                class="text-zinc-400 hover:text-red-500 transition-colors" title="Hapus Lampiran">
                <flux:icon name="trash" class="size-4" />
              </button>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endif

  <div class="mt-4 flex justify-end border-t border-zinc-100 pt-3 dark:border-zinc-700/50">
    <flux:button size="sm" variant="ghost" wire:click="closeChecklistItemPanel">Selesai
    </flux:button>
  </div>
</div>
