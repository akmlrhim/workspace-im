<div x-data="{ openMenu: false }">
  <div class="mb-3 flex items-center justify-between border-b border-zinc-100 pb-2 dark:border-zinc-700/50">
    <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Labels</span>
    @if ($canManage)
      <flux:button icon="plus" size="xs" variant="ghost" @click="openMenu = !openMenu"
        class="h-7 px-2 text-xs text-zinc-500 hover:text-indigo-600 dark:hover:text-indigo-400">
        Atur Label
      </flux:button>
    @endif
  </div>

  <div class="flex flex-wrap gap-1.5">
    @forelse ($task->labels as $label)
      <span
        class="group relative inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-[11px] font-semibold text-white shadow-sm transition-all"
        style="background-color: {{ $label->color }}">
        {{ $label->name }}
        @if ($canManage)
          <button wire:click="toggleLabel({{ $label->id }})"
            class="ml-1 -mr-1 rounded-sm p-0.5 opacity-0 transition-opacity hover:bg-white/20 group-hover:opacity-100"
            title="Hapus label">
            <flux:icon name="x-mark" class="size-3" />
          </button>
        @endif
      </span>
    @empty
      <span class="text-xs italic text-zinc-400">Belum ada label</span>
    @endforelse
  </div>

  @if ($canManage)
    <div x-show="openMenu" x-collapse x-cloak class="mt-3 space-y-3">
      @if ($allLabels->isNotEmpty())
        <div
          class="max-h-40 overflow-y-auto rounded-lg border border-zinc-200 bg-zinc-50/50 p-2 dark:border-zinc-700 dark:bg-zinc-900/50 custom-scrollbar">
          @foreach ($allLabels as $label)
            <button wire:click="toggleLabel({{ $label->id }})"
              class="flex w-full items-center gap-3 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-white dark:hover:bg-zinc-800">
              <div
                class="flex h-4 w-4 shrink-0 items-center justify-center rounded border transition-colors
                  {{ $task->labels->contains('id', $label->id) ? 'border-indigo-500 bg-indigo-500' : 'border-zinc-300 dark:border-zinc-600' }}">
                @if ($task->labels->contains('id', $label->id))
                  <flux:icon name="check" class="size-3 text-white" />
                @endif
              </div>
              <span class="h-3 w-3 shrink-0 rounded-full shadow-sm"
                style="background-color: {{ $label->color }}"></span>
              <span class="text-zinc-700 dark:text-zinc-300">{{ $label->name }}</span>
            </button>
          @endforeach
        </div>
      @endif

      @if (!$showLabelForm)
        <button type="button" wire:click="$toggle('showLabelForm')"
          class="flex w-full items-center justify-center gap-2 rounded-lg border border-dashed border-zinc-300 px-3 py-2 text-xs font-medium text-zinc-500 hover:border-indigo-400 hover:text-indigo-600 dark:border-zinc-600 dark:hover:border-indigo-500 dark:hover:text-indigo-400 transition-colors">
          <flux:icon name="plus" class="size-3" /> Buat Label Baru
        </button>
      @else
        <form wire:submit="createLabel"
          class="space-y-3 rounded-lg border border-indigo-100 bg-indigo-50/50 p-3 dark:border-indigo-900/30 dark:bg-indigo-900/10">
          <flux:input wire:model="newLabelName" placeholder="Nama label..." size="sm" autofocus />
          <div class="space-y-1.5">
            <span class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Pilih Warna</span>
            <div class="flex flex-wrap gap-2">
              @foreach (['#6366f1', '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#6b7280'] as $color)
                <button type="button" @click="$wire.set('newLabelColor', '{{ $color }}')"
                  class="h-5 w-5 rounded-full border-2 transition-transform hover:scale-110 focus:outline-none
                    {{ $newLabelColor === $color ? 'border-zinc-900 scale-110 shadow-sm dark:border-white' : 'border-transparent' }}"
                  style="background-color: {{ $color }}"></button>
              @endforeach
            </div>
          </div>
          <div class="flex justify-end gap-2 pt-1">
            <flux:button size="sm" variant="ghost" wire:click="$toggle('showLabelForm')">Batal
            </flux:button>
            <flux:button size="sm" variant="primary" type="submit">Simpan</flux:button>
          </div>
        </form>
      @endif
    </div>
  @endif
</div>
