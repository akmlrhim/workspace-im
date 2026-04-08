<div class="flex items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400 mb-3">
  <a href="{{ route('project-management.index') }}" wire:navigate
    class="hover:text-zinc-700 dark:hover:text-zinc-200">Spaces</a>
  <flux:icon name="chevron-right" class="size-3.5" />
  <a href="{{ route('project-management.spaces.show', $space) }}" wire:navigate
    class="hover:text-zinc-700 dark:hover:text-zinc-200">{{ $space->name }}</a>
  <flux:icon name="chevron-right" class="size-3.5" />
  <span class="font-medium text-zinc-900 dark:text-white">{{ $taskList->name }}</span>
</div>
