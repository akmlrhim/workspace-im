<div class="flex items-center rounded-lg border border-zinc-200 bg-zinc-50 p-0.5 dark:border-zinc-700 dark:bg-zinc-800">
  <a href="{{ route('project-management.lists.board', [$space, $taskList]) }}" wire:navigate title="Board"
    class="rounded-md px-2 py-1.5 sm:px-2.5 text-xs font-medium transition-all {{ ($active ?? '') === 'board' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
    <flux:icon name="view-columns" class="inline size-3.5 sm:mr-0.5" /><span class="hidden sm:inline"> Board</span>
  </a>
  <a href="{{ route('project-management.lists.show', [$space, $taskList]) }}" wire:navigate title="List"
    class="rounded-md px-2 py-1.5 sm:px-2.5 text-xs font-medium transition-all {{ ($active ?? '') === 'list' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
    <flux:icon name="queue-list" class="inline size-3.5 sm:mr-0.5" /><span class="hidden sm:inline"> List</span>
  </a>
  <a href="{{ route('project-management.lists.gantt', [$space, $taskList]) }}" wire:navigate title="Gantt"
    class="rounded-md px-2 py-1.5 sm:px-2.5 text-xs font-medium transition-all {{ ($active ?? '') === 'gantt' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
    <flux:icon name="chart-bar" class="inline size-3.5 sm:mr-0.5" /><span class="hidden sm:inline"> Gantt</span>
  </a>
  <a href="{{ route('project-management.lists.calendar', [$space, $taskList]) }}" wire:navigate title="Calendar"
    class="rounded-md px-2 py-1.5 sm:px-2.5 text-xs font-medium transition-all {{ ($active ?? '') === 'calendar' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
    <flux:icon name="calendar-days" class="inline size-3.5 sm:mr-0.5" /><span class="hidden sm:inline"> Calendar</span>
  </a>
</div>
