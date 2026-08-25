<div
  class="hidden overflow-hidden rounded-xl border border-zinc-200 transition-opacity duration-150 dark:border-zinc-700 sm:block"
  wire:loading.class="opacity-40" wire:target="calPrevMonth,calNextMonth,calToday">
  <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60">
    @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $idx => $dayName)
      <div
        class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-white
        {{ $idx >= 5 ? 'bg-zinc-100/50 dark:bg-zinc-800/30' : '' }}">
        {{ $dayName }}
      </div>
    @endforeach
  </div>

  @foreach ($weeks as $week)
    <div class="grid grid-cols-7 border-b border-zinc-100 last:border-b-0 dark:border-zinc-800">
      @foreach ($week as $day)
        <div
          class="group/cell min-h-[130px] border-r border-zinc-100 p-1.5 transition-colors last:border-r-0 dark:border-zinc-800
          {{ !$day['isCurrentMonth'] ? 'bg-zinc-50/50 dark:bg-zinc-900/30' : 'bg-white dark:bg-zinc-900' }}
          {{ $day['date']->isWeekend() && !$day['isToday'] ? 'bg-zinc-50 dark:bg-zinc-800/20' : '' }}
          {{ $day['isToday'] ? 'border-t-2 border-t-indigo-500 bg-indigo-50 dark:bg-indigo-950/40' : '' }}"
          wire:key="gcal-{{ $day['date']->format('Y-m-d') }}">

          <div class="mb-1 flex items-center justify-between">
            <span
              class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold
              {{ $day['isToday'] ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300 dark:shadow-indigo-900' : '' }}
              {{ !$day['isCurrentMonth'] && !$day['isToday'] ? 'text-zinc-300 dark:text-zinc-600' : (!$day['isToday'] ? 'text-zinc-700 dark:text-zinc-300' : '') }}">
              {{ $day['date']->day }}
            </span>

          </div>

          <div class="space-y-0.5">
            @foreach ($day['tasks'] as $task)
              @php $calDone = $task->status?->type === 'closed'; @endphp
              <button
                @click="$flux.modal('gen-task-detail').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
                class="w-full rounded-md border border-zinc-200 bg-white/80 px-1.5 py-1 text-left shadow-sm transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-800/80"
                title="{{ $task->title }} — {{ $task->taskList->space->name ?? '' }} / {{ $task->taskList->name ?? '' }}">
                <div class="flex min-w-0 items-center gap-1">
                  <span class="h-1.5 w-1.5 shrink-0 rounded-full"
                    style="background-color: {{ $task->priority_color }}"></span>
                  <span x-data="{ title: @js($task->title) }"
                    @task-title-updated.window="if ($event.detail.taskId === {{ $task->id }}) title = $event.detail.title"
                    x-text="title"
                    class="min-w-0 flex-1 truncate text-[11px] font-semibold text-zinc-800 dark:text-zinc-100"></span>
                  @if ($calDone)
                    <flux:icon name="check-circle" class="size-4 font-bold text-green-600 dark:text-green-400" />
                  @endif
                </div>
                <span class="mt-0.5 block truncate rounded px-1 py-0.5 text-[9px] font-medium text-white"
                  style="background-color: {{ $task->status?->color ?? '#6366f1' }}">
                  {{ $task->taskList->space->name ?? '-' }} / {{ $task->taskList->name ?? '-' }}
                </span>
              </button>
            @endforeach
          </div>
        </div>
      @endforeach
    </div>
  @endforeach
</div>
