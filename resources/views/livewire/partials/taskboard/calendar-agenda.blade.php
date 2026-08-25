<div class="space-y-2 transition-opacity duration-150 sm:hidden" wire:loading.class="opacity-40"
  wire:target="calPrevMonth,calNextMonth,calToday">
  @foreach ($weeks as $week)
    @foreach ($week as $day)
      @if ($day['isCurrentMonth'])
        @php $hasTasks = $day['tasks']->isNotEmpty(); @endphp
        <div
          class="rounded-xl border px-3 py-2.5 transition-colors
          {{ $day['isToday'] ? 'border-indigo-400 bg-indigo-50 ring-1 ring-indigo-300 dark:border-indigo-600 dark:bg-indigo-950/40 dark:ring-indigo-700' : 'border-zinc-100 dark:border-zinc-800' }}
          {{ !$hasTasks && !$day['isToday'] ? 'opacity-50' : '' }}"
          wire:key="gcal-m-{{ $day['date']->format('Y-m-d') }}">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span
                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold
                {{ $day['isToday'] ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-300 dark:shadow-indigo-900' : 'text-zinc-700 dark:text-zinc-300' }}">
                {{ $day['date']->day }}
              </span>
              <span
                class="text-xs font-medium {{ $day['isToday'] ? 'font-bold text-indigo-600 dark:text-indigo-400' : 'text-zinc-400 dark:text-zinc-500' }}">
                {{ $day['date']->isoFormat('ddd') }}
              </span>
            </div>
            @if ($hasTasks)
              <span
                class="rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400">
                {{ $day['tasks']->count() }}
              </span>
            @endif
          </div>

          @if ($hasTasks)
            <div class="mt-2 space-y-1 pl-9">
              @foreach ($day['tasks'] as $task)
                @php $calDone = $task->status?->type === 'closed'; @endphp
                <button
                  @click="$flux.modal('gen-task-detail').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
                  class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left text-xs font-medium transition hover:bg-zinc-50 dark:hover:bg-zinc-800">
                  <span class="h-2 w-2 shrink-0 rounded-full"
                    style="background-color: {{ $task->priority_color }}"></span>
                  <div class="min-w-0 flex-1">
                    <span x-data="{ title: @js($task->title) }"
                      @task-title-updated.window="if ($event.detail.taskId === {{ $task->id }}) title = $event.detail.title"
                      x-text="title" class="block truncate text-zinc-900 dark:text-zinc-100"></span>
                    <span class="block truncate text-[10px] text-zinc-400 dark:text-zinc-500">
                      {{ $task->taskList->space->name ?? '-' }} / {{ $task->taskList->name ?? '-' }}
                    </span>
                  </div>

                  @if ($calDone)
                    <flux:icon name="check-circle" class="size-4 font-bold text-green-600 dark:text-green-400" />
                  @endif
                </button>
              @endforeach
            </div>
          @endif
        </div>
      @endif
    @endforeach
  @endforeach
</div>
