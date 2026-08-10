{{-- Calendar Navigation --}}
<div class="flex items-center justify-between">
  <div class="flex items-center gap-2">
    <flux:button icon="chevron-left" size="sm" variant="ghost" wire:click="previousMonth" />
    <flux:button size="sm" variant="ghost" wire:click="goToToday">Hari Ini</flux:button>
    <flux:button icon="chevron-right" size="sm" variant="ghost" wire:click="nextMonth" />
  </div>
  <h2 class="text-sm font-semibold text-zinc-900 sm:text-lg dark:text-white">{{ $monthLabel }}</h2>
</div>

{{-- Desktop: Grid calendar --}}
<div class="hidden overflow-hidden rounded-xl border border-zinc-200 sm:block dark:border-zinc-700">
  <div class="grid grid-cols-7 border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60">
    @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
      <div
        class="px-3 py-2.5 text-center text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400
        {{ in_array($dayName, ['Sat', 'Sun']) ? 'bg-zinc-100/50 dark:bg-zinc-800/30' : '' }}">
        {{ $dayName }}
      </div>
    @endforeach
  </div>
  @foreach ($weeks as $week)
    <div class="grid grid-cols-7 border-b border-zinc-100 last:border-b-0 dark:border-zinc-800">
      @foreach ($week as $day)
        <div
          class="min-h-[120px] border-r border-zinc-100 p-1.5 transition-colors last:border-r-0 dark:border-zinc-800
          {{ !$day['isCurrentMonth'] ? 'bg-zinc-50/50 dark:bg-zinc-900/30' : 'bg-white dark:bg-zinc-900' }}
          {{ $day['date']->isWeekend() ? 'bg-zinc-50 dark:bg-zinc-800/20' : '' }}
          {{ $day['isToday'] ? 'bg-indigo-50/50 dark:bg-indigo-900/10' : '' }}"
          wire:key="my-cal-{{ $day['date']->format('Y-m-d') }}">
          <div class="mb-1 flex items-center justify-between">
            <span
              class="inline-flex h-6 w-6 items-center justify-center rounded-full text-xs font-medium
              {{ $day['isToday'] ? 'bg-indigo-600 text-white' : '' }}
              {{ !$day['isCurrentMonth'] ? 'text-zinc-300 dark:text-zinc-600' : 'text-zinc-700 dark:text-zinc-300' }}">
              {{ $day['date']->day }}
            </span>
          </div>
          <div class="space-y-0.5">
            @foreach ($day['tasks']->take(3) as $task)
              <button
                @click="$flux.modal('task-detail-mytasks').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
                class="group/task w-full rounded-md border border-zinc-200 bg-white/70 px-1.5 py-1 text-left shadow-xs transition-all hover:-translate-y-0.5 hover:shadow-sm dark:border-zinc-700 dark:bg-zinc-900/70"
                title="{{ $task->title }} — {{ $task->taskList->space->name ?? '' }}">
                <span class="mb-0.5 flex min-w-0 items-center gap-1">
                  <span class="h-1.5 w-1.5 shrink-0 rounded-full"
                    style="background-color: {{ $task->priority_color }}"></span>
                  <span class="truncate text-[11px] font-semibold text-zinc-800 dark:text-zinc-100">
                    {{ $task->title }}
                  </span>
                </span>
                <span class="flex min-w-0 items-center gap-1 text-[9px] font-medium">
                  <span class="max-w-full truncate rounded px-1 py-0.5 text-white"
                    style="background-color: {{ $task->taskList->space->color ?? '#6366f1' }}">
                    {{ $task->taskList->name ?? '-' }}
                  </span>
                </span>
              </button>
            @endforeach

            @if ($day['tasks']->count() > 3)
              <span class="block px-1.5 text-[10px] text-zinc-400 dark:text-zinc-500">
                +{{ $day['tasks']->count() - 3 }} lagi
              </span>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endforeach
</div>

{{-- Mobile: List-style calendar --}}
<div class="space-y-1 sm:hidden">
  @foreach ($weeks as $week)
    @foreach ($week as $day)
      @if ($day['isCurrentMonth'])
        @php $hasTasks = $day['tasks']->isNotEmpty(); @endphp
        <div
          class="rounded-lg border px-3 py-2 transition-colors
          {{ $day['isToday'] ? 'border-indigo-300 bg-indigo-50/50 dark:border-indigo-700 dark:bg-indigo-900/10' : 'border-zinc-100 dark:border-zinc-800' }}
          {{ !$hasTasks ? 'opacity-60' : '' }}"
          wire:key="my-cal-m-{{ $day['date']->format('Y-m-d') }}">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span
                class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold
                {{ $day['isToday'] ? 'bg-indigo-600 text-white' : 'text-zinc-700 dark:text-zinc-300' }}">
                {{ $day['date']->day }}
              </span>
              <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                {{ $day['date']->format('D') }}
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
                <button
                  @click="$flux.modal('task-detail-mytasks').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
                  class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-xs font-medium transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800">
                  <div class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $task->priority_color }}">
                  </div>
                  <span class="min-w-0 flex-1">
                    <span class="block truncate">{{ $task->title }}</span>
                    <span class="block truncate text-[10px] text-zinc-400 dark:text-zinc-500">
                      {{ $task->taskList->space->name ?? '-' }} / {{ $task->taskList->name ?? '-' }}
                    </span>
                  </span>
                </button>
              @endforeach
            </div>
          @endif
        </div>
      @endif
    @endforeach
  @endforeach
</div>
