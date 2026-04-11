<div>
  <div class="mb-6">
    @include('livewire.project.partials.breadcrumb')

    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $taskList->name }}</h1>
      <div class="flex items-center gap-2">
        @include('livewire.project.partials.view-toggle', ['active' => 'calendar'])
      </div>
    </div>
  </div>
  <div class="mb-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <flux:button icon="chevron-left" size="sm" variant="ghost" wire:click="previousMonth" />
      <flux:button size="sm" variant="ghost" wire:click="goToToday">Hari Ini</flux:button>
      <flux:button icon="chevron-right" size="sm" variant="ghost" wire:click="nextMonth" />
    </div>
    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $monthLabel }}</h2>
  </div>
  <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
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
            class="min-h-[120px] border-r border-zinc-100 p-1.5 last:border-r-0 dark:border-zinc-800 transition-colors
            {{ !$day['isCurrentMonth'] ? 'bg-zinc-50/50 dark:bg-zinc-900/30' : 'bg-white dark:bg-zinc-900' }}
            {{ $day['date']->isWeekend() ? 'bg-zinc-50 dark:bg-zinc-800/20' : '' }}
            {{ $day['isToday'] ? 'bg-indigo-50/50 dark:bg-indigo-900/10' : '' }}"
            wire:key="cal-{{ $day['date']->format('Y-m-d') }}">
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
                <div class="group relative flex w-full">
                  <button wire:click="openTaskDetail({{ $task->id }})"
                    class="flex w-full items-center gap-1 rounded-md px-1.5 py-1 text-left text-[11px] font-medium transition-all group-hover:opacity-80 pr-10"
                    style="background-color: {{ $task->status->color ?? '#6366f1' }}15; color: {{ $task->status->color ?? '#6366f1' }};"
                    title="{{ $task->title }}">
                    <div class="h-1.5 w-1.5 shrink-0 rounded-full"
                      style="background-color: {{ $task->priority_color }}"></div>
                    <span class="truncate">{{ $task->title }}</span>
                  </button>

                </div>
              @endforeach

              @if ($day['tasks']->count() > 3)
                <span class="block px-1.5 text-[10px] text-zinc-400 dark:text-zinc-500">
                  +{{ $day['tasks']->count() - 3 }} more
                </span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endforeach
  </div>
  @livewire('project.task-form-modal', ['space' => $space, 'taskList' => $taskList])
  @livewire('project.task-delete-modal')
  @if ($showTaskDetail && $selectedTaskId)
    <flux:modal wire:model="showTaskDetail"
      class="w-full max-w-5xl max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
      <div class="max-h-[85vh] overflow-y-auto pr-1 max-sm:max-h-none max-sm:h-[calc(100dvh-4rem)]">
        <livewire:project.task-detail :taskId="$selectedTaskId" :key="'cal-detail-' . $selectedTaskId" />
      </div>
    </flux:modal>
  @endif
</div>
