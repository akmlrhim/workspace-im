<div
  class="group relative flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">

  <a href="{{ route('project-management.lists.show', [$list->space, $list]) }}" wire:navigate
    class="absolute inset-0 z-0 rounded-lg"></a>

  <div class="relative z-10 flex items-center gap-3 pointer-events-none">
    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-700">
      <flux:icon name="queue-list" class="size-4 text-zinc-500 dark:text-zinc-400" />
    </div>
    <div>
      <span class="font-medium text-zinc-900 dark:text-white group-hover:underline">{{ $list->name }}</span>
      <span class="mx-2 text-xs text-zinc-400 dark:text-zinc-500">{{ $list->tasks_count }} tasks</span>
    </div>
  </div>

  <div class="flex items-center gap-1 relative z-10">
    <a href="{{ route('project-management.lists.board', [$list->space, $list]) }}" wire:navigate
      class="rounded-md p-1.5 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-white transition-colors"
      title="Board view">
      <flux:icon name="view-columns" class="size-4" />
    </a>
    <button wire:click.prevent="openEditList({{ $list->id }})"
      class="rounded-md p-1.5 text-zinc-400 opacity-0 transition-all group-hover:opacity-100 hover:bg-zinc-100 hover:text-blue-600 dark:hover:bg-zinc-700 dark:hover:text-blue-400"
      title="Edit list">
      <flux:icon name="pencil-square" class="size-4" />
    </button>
    <button wire:click.prevent="confirmDeleteList({{ $list->id }})"
      class="rounded-md p-1.5 text-zinc-400 opacity-0 transition-all group-hover:opacity-100 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/30 dark:hover:text-red-400"
      title="Delete list">
      <flux:icon name="trash" class="size-4" />
    </button>
  </div>
</div>
