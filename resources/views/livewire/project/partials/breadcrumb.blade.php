<div class="flex items-center gap-1.5 sm:gap-2 text-xs sm:text-sm text-zinc-500 dark:text-zinc-400 mb-3 min-w-0">
  <a href="{{ route('project-management.index') }}" wire:navigate
    class="shrink-0 hover:text-zinc-700 dark:hover:text-zinc-200">Spaces</a>
  <flux:icon name="chevron-right" class="size-3 sm:size-3.5 shrink-0" />
  <a href="{{ route('project-management.spaces.show', $space) }}" wire:navigate
    class="truncate max-w-[80px] sm:max-w-none hover:text-zinc-700 dark:hover:text-zinc-200">{{ $space->name }}</a>
  <flux:icon name="chevron-right" class="size-3 sm:size-3.5 shrink-0" />
  <span class="truncate font-medium text-zinc-900 dark:text-white">{{ $taskList->name }}</span>
</div>
