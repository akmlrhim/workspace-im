<div class="flex shrink-0 items-center justify-between px-3 py-3">
  <div class="flex items-center gap-1.5 min-w-0 flex-1">
    @if ($canManage)
      <div
        class="kanban-col-handle mr-0.5 flex shrink-0 touch-none cursor-grab items-center opacity-60 transition-opacity hover:opacity-100 active:cursor-grabbing">
        <svg class="size-3.5 text-zinc-400" viewBox="0 0 16 16" fill="currentColor">
          <circle cx="5.5" cy="3" r="1.3" />
          <circle cx="5.5" cy="8" r="1.3" />
          <circle cx="5.5" cy="13" r="1.3" />
          <circle cx="10.5" cy="3" r="1.3" />
          <circle cx="10.5" cy="8" r="1.3" />
          <circle cx="10.5" cy="13" r="1.3" />
        </svg>
      </div>
    @endif
    <div class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $status->color }}"></div>

    <span class="truncate text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $status->name }}</span>
    <span data-task-count
      class="rounded-md bg-zinc-200 px-1.5 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
      {{ $status->tasks->count() }}
    </span>
  </div>

  @if ($canManage)
    <div class="relative flex items-center gap-0.5" x-data="{ open: false }">
      <button wire:click="$dispatch('open-create-task-form', { statusId: {{ $status->id }} })" title="Tambah tugas"
        class="rounded-md p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 transition-colors">
        <flux:icon name="plus" class="size-4" />
      </button>
      <button @click="open = !open"
        class="rounded-md p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 transition-colors">
        <flux:icon name="ellipsis-horizontal" class="size-4" />
      </button>

      <div x-show="open" x-cloak @click.away="open = false" x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-8 z-30 w-44 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
        <button
          @click="open = false; $dispatch('open-column-rename', { id: {{ $status->id }}, name: @js($status->name), color: @js($status->color) }); $flux.modal('rename-column-modal').show()"
          class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700">
          <flux:icon name="pencil" class="size-3.5" /> Ubah Nama
        </button>
        <button @click="open = false; $wire.confirmDeleteColumn({{ $status->id }})"
          class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-zinc-700">
          <flux:icon name="trash" class="size-3.5" /> Hapus Kolom
        </button>
      </div>
    </div>
  @endif
</div>
