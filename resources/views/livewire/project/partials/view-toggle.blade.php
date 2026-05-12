@php $active = $active ?? ''; @endphp
<div class="overflow-x-auto border-b border-zinc-200 dark:border-zinc-700 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
  <div class="flex min-w-max">
    <a href="{{ route('project-management.lists.board', [$space, $taskList]) }}" wire:navigate
      class="flex shrink-0 items-center gap-1.5 pb-3 pr-6 text-sm font-medium border-b-2 -mb-px transition-colors {{ $active === 'board' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <flux:icon name="view-columns" class="size-4" />
      Board
    </a>
    <a href="{{ route('project-management.lists.show', [$space, $taskList]) }}" wire:navigate
      class="flex shrink-0 items-center gap-1.5 pb-3 pr-6 text-sm font-medium border-b-2 -mb-px transition-colors {{ $active === 'list' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <flux:icon name="queue-list" class="size-4" />
      List
    </a>
    <a href="{{ route('project-management.lists.gantt', [$space, $taskList]) }}" wire:navigate
      class="flex shrink-0 items-center gap-1.5 pb-3 pr-6 text-sm font-medium border-b-2 -mb-px transition-colors {{ $active === 'gantt' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <flux:icon name="chart-bar" class="size-4" />
      Gantt
    </a>
    <a href="{{ route('project-management.lists.calendar', [$space, $taskList]) }}" wire:navigate
      class="flex shrink-0 items-center gap-1.5 pb-3 pr-6 text-sm font-medium border-b-2 -mb-px transition-colors {{ $active === 'calendar' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <flux:icon name="calendar-days" class="size-4" />
      Kalender
    </a>
    <a href="{{ route('project-management.lists.daily', [$space, $taskList]) }}" wire:navigate
      class="flex shrink-0 items-center gap-1.5 pb-3 text-sm font-medium border-b-2 -mb-px transition-colors {{ $active === 'daily' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
      <flux:icon name="clipboard-document-check" class="size-4" />
      Daily Task
    </a>
  </div>
</div>
