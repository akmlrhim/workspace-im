@php
  $isOverdue = !$isClosedType && $task->due_date && $task->due_date->toDateString() < now()->toDateString();
  $isDueToday = !$isClosedType && $task->due_date && $task->due_date->isToday();
  $daysOverdue = $isOverdue
      ? (int) ceil(
          $task->due_date
              ->copy()
              ->startOfDay()
              ->diffInDays(now()->startOfDay(), true),
      )
      : null;
@endphp

<div wire:key="task-{{ $task->id }}"
  class="group rounded-xl border bg-white p-3 sm:p-4 transition duration-150 hover:shadow-sm dark:bg-zinc-900
    {{ $isClosedType ? 'border-zinc-100 opacity-70 dark:border-zinc-800' : 'border-zinc-200 hover:border-indigo-200 dark:border-zinc-700 dark:hover:border-indigo-500/30' }}">

  <div class="flex items-center gap-3">
    <div class="min-w-0 flex-1">
      <button
        @click="$flux.modal('task-detail-mytasks').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
        class="block w-full cursor-pointer truncate text-left text-sm font-semibold transition-colors
          {{ $isClosedType ? 'text-zinc-400 line-through dark:text-zinc-500' : 'text-zinc-900 hover:text-indigo-600 dark:text-zinc-100 dark:hover:text-indigo-400' }}">
        {{ $task->title }}
      </button>

      <div class="mt-0.5 flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
        <span class="font-medium" style="color: {{ $task->taskList->space->color ?? 'inherit' }}">
          {{ $task->taskList->space->name ?? '-' }}
        </span>
        <span>&rsaquo;</span>
        <span class="truncate">{{ $task->taskList->name ?? '-' }}</span>
      </div>
    </div>

    @if ($task->assignees->isNotEmpty())
      <div class="hidden shrink-0 items-center -space-x-1.5 sm:flex">
        @foreach ($task->assignees->take(3) as $assignee)
          <flux:avatar circle :name="$assignee->name" :initials="$assignee->initials()"
            :src="$assignee->avatar" size="xs" class="ring-2 ring-white dark:ring-zinc-900" />
        @endforeach
        @if ($task->assignees->count() > 3)
          <span
            class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-200 text-[10px] font-medium text-zinc-600 ring-2 ring-white dark:bg-zinc-700 dark:text-zinc-300 dark:ring-zinc-900">
            +{{ $task->assignees->count() - 3 }}
          </span>
        @endif
      </div>
    @endif

    <span
      class="hidden shrink-0 text-[11px] font-medium capitalize sm:block
      @if ($task->priority === 'urgent') text-red-500
      @elseif ($task->priority === 'high') text-orange-500
      @elseif ($task->priority === 'normal') text-blue-500
      @else text-zinc-400 @endif">
      {{ ucfirst($task->priority ?? 'none') }}
    </span>

    @if ($task->due_date)
      <div
        class="hidden shrink-0 items-center gap-1 rounded-md px-2 py-1 text-xs font-medium sm:flex
        @if ($isClosedType) bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400
        @elseif ($isOverdue) bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400
        @elseif ($isDueToday) bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400
        @else bg-zinc-50 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 @endif">
        @if ($isClosedType)
          <flux:icon name="check-circle" class="size-3" />
        @elseif ($isOverdue)
          <flux:icon name="exclamation-circle" class="size-3" />
        @elseif ($isDueToday)
          <flux:icon name="clock" class="size-3" />
        @else
          <flux:icon name="calendar" class="size-3" />
        @endif
        {{ $task->due_date->format('d M') }}
      </div>
    @endif
  </div>

  {{-- Mobile: show priority & due date below --}}
  <div class="mt-2 flex items-center gap-2 pl-4 sm:hidden">
    <span
      class="rounded-full px-1.5 py-0.5 text-[10px] font-medium
      @if ($task->priority === 'urgent') bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400
      @elseif ($task->priority === 'high') bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400
      @elseif ($task->priority === 'normal') bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400
      @else bg-zinc-50 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 @endif">
      {{ ucfirst($task->priority ?? 'none') }}
    </span>

    @if ($task->due_date)
      <span
        class="flex items-center gap-1 text-[10px] font-medium
        @if ($isClosedType) text-green-500 dark:text-green-400
        @elseif ($isOverdue) text-red-500
        @elseif ($isDueToday) text-amber-500
        @else text-zinc-400 dark:text-zinc-500 @endif">
        @if ($isClosedType)
          <flux:icon name="check-circle" class="size-3" /> {{ $task->due_date->format('d M') }}
        @elseif ($isOverdue)
          <flux:icon name="exclamation-circle" class="size-3" /> {{ $daysOverdue }}h lalu
        @elseif ($isDueToday)
          <flux:icon name="clock" class="size-3" /> Hari ini
        @else
          <flux:icon name="calendar" class="size-3" /> {{ $task->due_date->format('d M') }}
        @endif
      </span>
    @endif

    @if ($task->assignees->isNotEmpty())
      <div class="ml-auto flex -space-x-1">
        @foreach ($task->assignees->take(2) as $assignee)
          <flux:avatar circle :name="$assignee->name" :initials="$assignee->initials()"
            :src="$assignee->avatar" size="xs" class="ring-1 ring-white dark:ring-zinc-900" />
        @endforeach
        @if ($task->assignees->count() > 2)
          <span
            class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-200 text-[9px] font-medium text-zinc-600 ring-1 ring-white dark:bg-zinc-700 dark:text-zinc-300 dark:ring-zinc-900">
            +{{ $task->assignees->count() - 2 }}
          </span>
        @endif
      </div>
    @endif
  </div>
</div>
