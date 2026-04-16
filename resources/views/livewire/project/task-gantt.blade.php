<div>
  <div class="mb-6">
    @include('livewire.project.partials.breadcrumb')

    <div class="flex items-center justify-between">
      <h1 class="hidden lg:block text-2xl font-bold text-zinc-900 dark:text-white">
        {{ $taskList->name }}
      </h1>
      <div class="flex items-center gap-2">
        @include('livewire.project.partials.view-toggle', ['active' => 'gantt'])
      </div>
    </div>
  </div>
  <div class="mb-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <flux:button icon="chevron-left" size="sm" variant="ghost" wire:click="previousPeriod" />
      <flux:button size="sm" variant="ghost" wire:click="resetToToday">Today</flux:button>
      <flux:button icon="chevron-right" size="sm" variant="ghost" wire:click="nextPeriod" />
    </div>
    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
      {{ \Carbon\Carbon::parse($startDate)->format('M d') }} — {{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}
    </span>
  </div>
  {{-- Desktop: Full Gantt chart --}}
  <div class="hidden sm:block overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
    <div class="min-w-[900px]">
      <div class="flex border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60">
        <div
          class="w-64 shrink-0 border-r border-zinc-200 px-4 py-2.5 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
          Task
        </div>
        <div class="flex flex-1">
          @foreach ($days as $day)
            <div
              class="flex-1 border-r border-zinc-100 px-0.5 py-2 text-center last:border-r-0 dark:border-zinc-800
              {{ $day->isToday() ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}
              {{ $day->isWeekend() ? 'bg-zinc-100/50 dark:bg-zinc-800/30' : '' }}">
              <div class="text-[9px] font-medium uppercase text-zinc-400 dark:text-zinc-500">{{ $day->format('D') }}
              </div>
              <div
                class="text-[10px] font-semibold {{ $day->isToday() ? 'text-indigo-600 dark:text-indigo-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                {{ $day->format('d') }}
              </div>
            </div>
          @endforeach
        </div>
      </div>
      @forelse ($tasks as $task)
        @php
          $taskCreated = $task->created_at->startOfDay();
          $taskDue = $task->due_date->startOfDay();
          $barStart = max(0, $timelineStart->diffInDays($taskCreated));
          $barEnd = max(0, $timelineStart->diffInDays($taskDue));

          // Clamp to timeline
          $barStart = max(0, min($barStart, $totalDays - 1));
          $barEnd = max($barStart, min($barEnd, $totalDays - 1));
          $barWidth = $barEnd - $barStart + 1;

          $leftPercent = ($barStart / $totalDays) * 100;
          $widthPercent = ($barWidth / $totalDays) * 100;
        @endphp

        <div
          class="group flex border-b border-zinc-100 last:border-b-0 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
          wire:key="gantt-{{ $task->id }}">
          <div class="w-64 shrink-0 border-r border-zinc-200 px-4 py-3 dark:border-zinc-700 relative">
            <button wire:click="openTaskDetail({{ $task->id }})"
              class="flex items-center gap-2 text-sm font-medium text-zinc-900 hover:text-indigo-600 dark:text-zinc-100 dark:hover:text-indigo-400 text-left truncate w-full pr-12">
              <div class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $task->priority_color }}"></div>
              <span class="truncate">{{ $task->title }}</span>
            </button>
            <div class="mt-0.5 flex items-center gap-2">
              <span class="rounded-full px-1.5 py-0.5 text-[9px] font-medium"
                style="background-color: {{ $task->status->color ?? '#6b7280' }}20; color: {{ $task->status->color ?? '#6b7280' }}">
                {{ $task->status->name ?? '' }}
              </span>
              @if ($task->assignee)
                <span class="text-[10px] text-zinc-400">{{ $task->assignee->name }}</span>
              @endif
            </div>
          </div>
          <div class="relative flex-1 py-3">
            @php
              $todayOffset = $timelineStart->diffInDays(now()->startOfDay());
              $todayPercent = ($todayOffset / $totalDays) * 100;
            @endphp
            @if ($todayPercent >= 0 && $todayPercent <= 100)
              <div class="absolute top-0 bottom-0 w-px bg-indigo-400 dark:bg-indigo-500 z-10"
                style="left: {{ $todayPercent }}%"></div>
            @endif
            <div
              class="absolute top-1/2 -translate-y-1/2 h-6 rounded-full transition-all group-hover:h-7 cursor-pointer"
              style="left: {{ $leftPercent }}%; width: {{ max($widthPercent, 2) }}%; background-color: {{ $task->status->color ?? '#6366f1' }};"
              wire:click="openTaskDetail({{ $task->id }})"
              title="{{ $task->title }} — Due: {{ $task->due_date->format('M d') }}">
              <span class="absolute inset-0 flex items-center px-2 text-[10px] font-medium text-white truncate">
                @if ($widthPercent > 8)
                  {{ $task->due_date->format('M d') }}
                @endif
              </span>
            </div>
          </div>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center py-16 bg-white dark:bg-zinc-900">
          <flux:icon name="chart-bar" class="mb-3 size-10 text-zinc-300 dark:text-zinc-600" />
          <p class="text-sm text-zinc-500 dark:text-zinc-400">No tasks with due dates to display</p>
          <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Add due dates to your tasks to see them on the Gantt
            chart</p>
        </div>
      @endforelse
    </div>
  </div>

  {{-- Mobile: Timeline list --}}
  <div class="sm:hidden space-y-2">
    @forelse ($tasks as $task)
      @php
        $isOverdue = $task->due_date->isPast();
        $daysLeft = now()->startOfDay()->diffInDays($task->due_date->startOfDay(), false);
      @endphp
      <button wire:click="openTaskDetail({{ $task->id }})"
        class="flex w-full items-center gap-3 rounded-xl border bg-white p-3 text-left transition-colors hover:bg-zinc-50 dark:bg-zinc-900 dark:hover:bg-zinc-800
        {{ $isOverdue ? 'border-red-200 dark:border-red-800/40' : 'border-zinc-200 dark:border-zinc-700' }}"
        wire:key="gantt-m-{{ $task->id }}">
        <div class="h-8 w-1 shrink-0 rounded-full" style="background-color: {{ $task->status->color ?? '#6366f1' }}"></div>
        <div class="min-w-0 flex-1">
          <div class="flex items-center gap-2">
            <div class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $task->priority_color }}"></div>
            <span class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</span>
          </div>
          <div class="mt-1 flex items-center gap-2 text-xs text-zinc-400 dark:text-zinc-500">
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-medium"
              style="background-color: {{ $task->status->color ?? '#6b7280' }}20; color: {{ $task->status->color ?? '#6b7280' }}">
              {{ $task->status->name ?? '' }}
            </span>
            @if ($task->assignee)
              <span>{{ $task->assignee->name }}</span>
            @endif
          </div>
        </div>
        <div class="shrink-0 text-right">
          <div class="text-xs font-medium {{ $isOverdue ? 'text-red-500' : 'text-zinc-600 dark:text-zinc-400' }}">
            {{ $task->due_date->format('M d') }}
          </div>
          <div class="text-[10px] {{ $isOverdue ? 'text-red-400' : 'text-zinc-400 dark:text-zinc-500' }}">
            @if ($isOverdue)
              {{ abs($daysLeft) }}h lalu
            @elseif ($daysLeft == 0)
              Hari ini
            @else
              {{ $daysLeft }}h lagi
            @endif
          </div>
        </div>
      </button>
    @empty
      <div class="flex flex-col items-center justify-center rounded-xl border border-zinc-200 bg-white py-12 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:icon name="chart-bar" class="mb-3 size-10 text-zinc-300 dark:text-zinc-600" />
        <p class="text-sm text-zinc-500 dark:text-zinc-400">No tasks with due dates to display</p>
        <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Add due dates to your tasks to see them on the Gantt chart</p>
      </div>
    @endforelse
  </div>
  @livewire('project.task-form-modal', ['space' => $space, 'taskList' => $taskList])
  @livewire('project.task-delete-modal')
  @if ($showTaskDetail && $selectedTaskId)
    <flux:modal wire:model="showTaskDetail"
      class="w-full max-w-5xl max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
      <div class="max-h-[85vh] overflow-y-auto pr-1 max-sm:max-h-none max-sm:h-[calc(100dvh-4rem)]">
        <livewire:project.task-detail :taskId="$selectedTaskId" :key="'gantt-detail-' . $selectedTaskId" />
      </div>
    </flux:modal>
  @endif
</div>
