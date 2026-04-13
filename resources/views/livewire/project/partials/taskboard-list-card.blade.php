<div
  class="group relative flex flex-col rounded-lg border border-zinc-200 bg-white p-4 transition-all hover:border-zinc-300 hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600">

  <a href="{{ route('project-management.lists.board', [$list->space, $list]) }}" wire:navigate
    class="absolute inset-0 z-0 rounded-lg"></a>

  <div class="relative z-10 pointer-events-none">
    <div class="flex items-center gap-3">
      <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-700">
        <flux:icon name="queue-list" class="size-4 text-zinc-500 dark:text-zinc-400" />
      </div>
      <div class="min-w-0 flex-1">
        <span class="font-medium text-zinc-900 dark:text-white group-hover:underline">{{ $list->name }}</span>
        <div class="flex items-center gap-2 mt-0.5">
          <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $list->tasks_count }} tasks</span>
        </div>
      </div>
    </div>

    @if ($list->members->isNotEmpty())
      <div class="mt-3 flex items-center">
        <flux:avatar.group>
          @foreach ($list->members->take(4) as $member)
            <flux:avatar :name="$member->name" :initials="$member->initials()" :src="$member->avatar" size="xs"
              class="ring-1 ring-white dark:ring-zinc-800" />
          @endforeach
          @if ($list->members->count() > 4)
            <flux:avatar size="xs" class="ring-1 ring-white dark:ring-zinc-800">
              +{{ $list->members->count() - 4 }}
            </flux:avatar>
          @endif
        </flux:avatar.group>
      </div>
    @endif
  </div>

  <div class="relative z-10 mt-3 flex items-center gap-1 border-t border-zinc-100 pt-3 dark:border-zinc-700">
    <a href="{{ route('project-management.lists.board', [$list->space, $list]) }}" wire:navigate
      class="rounded-md px-2 py-1 text-xs text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
      title="Board view">
      <flux:icon name="view-columns" class="inline size-3.5 mr-0.5" /> Board
    </a>
    <a href="{{ route('project-management.lists.show', [$list->space, $list]) }}" wire:navigate
      class="rounded-md px-2 py-1 text-xs text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
      title="List view">
      <flux:icon name="queue-list" class="inline size-3.5 mr-0.5" /> List
    </a>
    <a href="{{ route('project-management.lists.gantt', [$list->space, $list]) }}" wire:navigate
      class="rounded-md px-2 py-1 text-xs text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
      title="Gantt view">
      <flux:icon name="chart-bar" class="inline size-3.5 mr-0.5" /> Gantt
    </a>
    <a href="{{ route('project-management.lists.calendar', [$list->space, $list]) }}" wire:navigate
      class="rounded-md px-2 py-1 text-xs text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
      title="Calendar view">
      <flux:icon name="calendar-days" class="inline size-3.5 mr-0.5" /> Calendar
    </a>
  </div>
</div>
