<div class="space-y-6">
  {{-- Header --}}
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">Tugas Saya</flux:heading>
      <flux:subheading>
        {{ $totalCount }} tugas ditugaskan kepada Anda di seluruh ruang
      </flux:subheading>
    </div>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
      <div
        class="flex items-center rounded-lg border border-zinc-200 bg-zinc-50 p-0.5 dark:border-zinc-700 dark:bg-zinc-800">
        <button type="button" wire:click="switchView('list')" title="List"
          class="rounded-md px-2 py-1.5 text-xs font-medium transition-all sm:px-2.5
          {{ $view === 'list' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
          <flux:icon name="queue-list" class="inline size-3.5 sm:mr-0.5" /><span class="hidden sm:inline"> List</span>
        </button>
        <button type="button" wire:click="switchView('calendar')" title="Calendar"
          class="rounded-md px-2 py-1.5 text-xs font-medium transition-all sm:px-2.5
          {{ $view === 'calendar' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-700 dark:text-white' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
          <flux:icon name="calendar-days" class="inline size-3.5 sm:mr-0.5" /><span class="hidden sm:inline"> Calendar</span>
        </button>
      </div>

      <flux:select wire:model.live="filterPriority" size="sm" class="w-full sm:w-44">
        <flux:select.option value="">Semua Prioritas</flux:select.option>
        <flux:select.option value="urgent">🔴 Urgent</flux:select.option>
        <flux:select.option value="high">🟠 High</flux:select.option>
        <flux:select.option value="normal">🔵 Normal</flux:select.option>
        <flux:select.option value="low">⚪ Low</flux:select.option>
      </flux:select>
    </div>
  </div>

  @if ($view === 'calendar')
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
                  <button wire:click="openTaskDetail({{ $task->id }})"
                    class="flex w-full items-center gap-1 rounded-md px-1.5 py-1 text-left text-[11px] font-medium transition-all hover:opacity-80"
                    style="background-color: {{ $task->status->color ?? '#6366f1' }}15; color: {{ $task->status->color ?? '#6366f1' }};"
                    title="{{ $task->title }} — {{ $task->taskList->space->name ?? '' }}">
                    <div class="h-1.5 w-1.5 shrink-0 rounded-full"
                      style="background-color: {{ $task->priority_color }}"></div>
                    <span class="truncate">{{ $task->title }}</span>
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
                    <button wire:click="openTaskDetail({{ $task->id }})"
                      class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-xs font-medium transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800"
                      style="color: {{ $task->status->color ?? '#6366f1' }};">
                      <div class="h-2 w-2 shrink-0 rounded-full"
                        style="background-color: {{ $task->priority_color }}"></div>
                      <span class="truncate">{{ $task->title }}</span>
                      <span class="ml-auto shrink-0 text-[10px] text-zinc-400 dark:text-zinc-500">
                        {{ $task->taskList->space->name ?? '' }}
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
  @elseif ($totalCount === 0)
    <div
      class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 py-20 dark:border-zinc-700 dark:bg-zinc-800/20">
      <div class="mb-4 rounded-full bg-green-100 p-4 dark:bg-green-500/20">
        <flux:icon name="check-circle" class="size-9 text-green-600 dark:text-green-400" />
      </div>
      <flux:heading size="lg">Tidak ada tugas</flux:heading>
      <flux:subheading class="mt-1">Anda tidak memiliki tugas yang ditugaskan saat ini.</flux:subheading>
    </div>
  @else
    {{-- Grouped by status --}}
    @foreach ($grouped as $group)
      @php
        $status = $group['status'];
        $statusTasks = $group['tasks'];
        $isClosedType = $status->type === 'closed';
      @endphp

      <div class="space-y-2" wire:key="group-{{ $status->id }}">

        <div class="flex items-center gap-3">
          <div class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $status->color }}"></div>
          <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $status->name }}</span>
          <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold text-white"
            style="background-color: {{ $status->color }}">
            {{ $statusTasks->count() }}
          </span>
          <div class="flex-1 border-t border-zinc-200 dark:border-zinc-700"></div>
        </div>

        <div class="space-y-2 pl-6">
          @foreach ($statusTasks as $task)
            @php
              $isOverdue = !$isClosedType && $task->due_date && $task->due_date->isPast();
              $isDueToday = !$isClosedType && $task->due_date && $task->due_date->isToday();
            @endphp

            <div wire:key="task-{{ $task->id }}"
              class="group rounded-xl border bg-white p-3 sm:p-4 transition duration-150 hover:shadow-sm dark:bg-zinc-900
                {{ $isClosedType ? 'border-zinc-100 opacity-70 dark:border-zinc-800' : 'border-zinc-200 hover:border-indigo-200 dark:border-zinc-700 dark:hover:border-indigo-500/30' }}">

              <div class="flex items-center gap-3">
                <div
                  class="h-8 w-1 shrink-0 rounded-full
                  @if ($task->priority === 'urgent') bg-red-500
                  @elseif ($task->priority === 'high') bg-orange-400
                  @elseif ($task->priority === 'normal') bg-blue-400
                  @else bg-zinc-200 dark:bg-zinc-600 @endif">
                </div>

                <div class="min-w-0 flex-1">
                  <button wire:click="openTaskDetail({{ $task->id }})"
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
                      <flux:avatar circle :name="$assignee->name" :initials="$assignee->initials()" :src="$assignee->avatar" size="xs"
                        class="ring-2 ring-white dark:ring-zinc-900" />
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
                    @if ($isOverdue) bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400
                    @elseif ($isDueToday) bg-amber-50 text-amber-600 dark:bg-amber-900/20 dark:text-amber-400
                    @else bg-zinc-50 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400 @endif">
                    @if ($isOverdue)
                      <flux:icon name="exclamation-circle" class="size-3" />
                    @elseif ($isDueToday)
                      <flux:icon name="clock" class="size-3" />
                    @else
                      <flux:icon name="calendar" class="size-3" />
                    @endif
                    @if ($isOverdue)
                      {{ now()->diffInDays($task->due_date) }}h lalu
                    @elseif ($isDueToday)
                      Hari ini
                    @else
                      {{ $task->due_date->format('d M') }}
                    @endif
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
                    @if ($isOverdue) text-red-500
                    @elseif ($isDueToday) text-amber-500
                    @else text-zinc-400 dark:text-zinc-500 @endif">
                    @if ($isOverdue)
                      <flux:icon name="exclamation-circle" class="size-3" />
                      {{ now()->diffInDays($task->due_date) }}h lalu
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
                      <flux:avatar circle :name="$assignee->name" :initials="$assignee->initials()" :src="$assignee->avatar" size="xs"
                        class="ring-1 ring-white dark:ring-zinc-900" />
                    @endforeach
                    @if ($task->assignees->count() > 2)
                      <span class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-200 text-[9px] font-medium text-zinc-600 ring-1 ring-white dark:bg-zinc-700 dark:text-zinc-300 dark:ring-zinc-900">
                        +{{ $task->assignees->count() - 2 }}
                      </span>
                    @endif
                  </div>
                @endif
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endforeach

    @if ($ungrouped->isNotEmpty())
      <div class="space-y-2">
        <div class="flex items-center gap-3">
          <div class="h-3 w-3 shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
          <span class="text-sm font-semibold text-zinc-500 dark:text-zinc-400">Tanpa Status</span>
          <flux:badge size="sm" color="zinc">{{ $ungrouped->count() }}</flux:badge>
          <div class="flex-1 border-t border-zinc-200 dark:border-zinc-700"></div>
        </div>
        <div class="space-y-2 pl-6">
          @foreach ($ungrouped as $task)
            <div wire:key="task-ns-{{ $task->id }}"
              class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
              <div class="min-w-0 flex-1">
                <button wire:click="openTaskDetail({{ $task->id }})"
                  class="block w-full cursor-pointer truncate text-left text-sm font-semibold text-zinc-900 hover:text-indigo-600 dark:text-zinc-100 dark:hover:text-indigo-400">
                  {{ $task->title }}
                </button>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

  @endif

  @if ($showTaskDetail && $selectedTaskId)
    <flux:modal wire:model="showTaskDetail"
      class="w-full max-w-5xl max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
      <div class="max-h-[85vh] overflow-y-auto pr-1 max-sm:max-h-none max-sm:h-[calc(100dvh-4rem)]">
        <livewire:project.task-detail :taskId="$selectedTaskId" :key="'my-detail-' . $selectedTaskId" />
      </div>
    </flux:modal>
  @endif
</div>
