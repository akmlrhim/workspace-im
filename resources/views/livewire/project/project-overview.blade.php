<div class="space-y-6">
  {{-- Header --}}
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Project Overview</h1>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
        Ringkasan kondisi workspace <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $workspace->name }}</span>
      </p>
    </div>
    <a href="{{ route('project-management.index') }}" wire:navigate>
      <flux:button icon="squares-2x2" variant="ghost" size="sm">Lihat Spaces</flux:button>
    </a>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
    {{-- Total Tasks --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium uppercase tracking-wider text-zinc-400">Total Task</span>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-900/30">
          <flux:icon name="clipboard-document-list" class="size-4 text-indigo-600 dark:text-indigo-400" />
        </div>
      </div>
      <p class="mt-3 text-3xl font-bold text-zinc-900 dark:text-white">{{ $totalTasks }}</p>
      <p class="mt-1 text-xs text-zinc-400">di {{ $totalLists }} list · {{ $totalSpaces }} space</p>
    </div>

    {{-- Completed --}}
    <div class="rounded-xl border border-green-100 bg-green-50/50 p-4 dark:border-green-900/30 dark:bg-green-900/10">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium uppercase tracking-wider text-green-600 dark:text-green-500">Selesai</span>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
          <flux:icon name="check-circle" class="size-4 text-green-600 dark:text-green-400" />
        </div>
      </div>
      <p class="mt-3 text-3xl font-bold text-green-700 dark:text-green-400">{{ $completedTasks }}</p>
      <p class="mt-1 text-xs text-green-600 dark:text-green-500">{{ $completionRate }}% completion rate</p>
    </div>

    {{-- In Progress --}}
    <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-4 dark:border-blue-900/30 dark:bg-blue-900/10">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium uppercase tracking-wider text-blue-600 dark:text-blue-500">Berjalan</span>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
          <flux:icon name="arrow-path" class="size-4 text-blue-600 dark:text-blue-400" />
        </div>
      </div>
      <p class="mt-3 text-3xl font-bold text-blue-700 dark:text-blue-400">{{ $inProgressTasks }}</p>
      <p class="mt-1 text-xs text-blue-500 dark:text-blue-400">{{ $openTasks }} belum dimulai</p>
    </div>

    {{-- Overdue --}}
    <div class="rounded-xl border border-red-100 bg-red-50/50 p-4 dark:border-red-900/30 dark:bg-red-900/10">
      <div class="flex items-center justify-between">
        <span class="text-xs font-medium uppercase tracking-wider text-red-500">Terlambat</span>
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
          <flux:icon name="exclamation-circle" class="size-4 text-red-600 dark:text-red-400" />
        </div>
      </div>
      <p class="mt-3 text-3xl font-bold text-red-600 dark:text-red-400">{{ $overdueTasks }}</p>
      <p class="mt-1 text-xs text-red-400">{{ $dueTodayTasks }} jatuh tempo hari ini</p>
    </div>
  </div>

  {{-- Progress bar + Priority breakdown --}}
  <div class="grid gap-4 lg:grid-cols-2">
    {{-- Completion progress --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
      <h3 class="mb-4 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Progress Keseluruhan</h3>

      <div class="mb-2 flex items-end justify-between">
        <span class="text-4xl font-bold text-zinc-900 dark:text-white">{{ $completionRate }}%</span>
        <span class="text-sm text-zinc-400">{{ $completedTasks }} / {{ $totalTasks }} task selesai</span>
      </div>

      <div class="h-3 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
        <div
          class="h-full rounded-full transition-all duration-500 {{ $completionRate === 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
          style="width: {{ $completionRate }}%"></div>
      </div>

      <div class="mt-4 grid grid-cols-3 gap-2 text-center">
        <div class="rounded-lg bg-zinc-50 p-2 dark:bg-zinc-800">
          <p class="text-lg font-bold text-zinc-700 dark:text-zinc-200">{{ $openTasks }}</p>
          <p class="text-[11px] text-zinc-400">To Do</p>
        </div>
        <div class="rounded-lg bg-blue-50 p-2 dark:bg-blue-900/20">
          <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $inProgressTasks }}</p>
          <p class="text-[11px] text-blue-400">In Progress</p>
        </div>
        <div class="rounded-lg bg-green-50 p-2 dark:bg-green-900/20">
          <p class="text-lg font-bold text-green-600 dark:text-green-400">{{ $completedTasks }}</p>
          <p class="text-[11px] text-green-400">Done</p>
        </div>
      </div>
    </div>

    {{-- Priority breakdown --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
      <h3 class="mb-4 text-sm font-semibold text-zinc-700 dark:text-zinc-300">Task Berdasarkan Prioritas</h3>

      @php
        $priorities = [
          'urgent' => ['label' => 'Urgent', 'color' => 'bg-red-500', 'text' => 'text-red-600 dark:text-red-400', 'ring' => 'bg-red-100 dark:bg-red-900/30'],
          'high'   => ['label' => 'High',   'color' => 'bg-orange-500', 'text' => 'text-orange-600 dark:text-orange-400', 'ring' => 'bg-orange-100 dark:bg-orange-900/30'],
          'normal' => ['label' => 'Normal', 'color' => 'bg-blue-500', 'text' => 'text-blue-600 dark:text-blue-400', 'ring' => 'bg-blue-100 dark:bg-blue-900/30'],
          'low'    => ['label' => 'Low',    'color' => 'bg-zinc-400', 'text' => 'text-zinc-500 dark:text-zinc-400', 'ring' => 'bg-zinc-100 dark:bg-zinc-800'],
        ];
      @endphp

      <div class="space-y-3">
        @foreach ($priorities as $key => $meta)
          @php $count = $tasksByPriority[$key] ?? 0; @endphp
          <div class="flex items-center gap-3">
            <span class="w-14 text-xs font-medium {{ $meta['text'] }}">{{ $meta['label'] }}</span>
            <div class="flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800" style="height: 8px">
              @if ($totalTasks > 0)
                <div class="h-full rounded-full {{ $meta['color'] }}" style="width: {{ round(($count / $totalTasks) * 100) }}%"></div>
              @endif
            </div>
            <span class="w-6 text-right text-xs font-semibold text-zinc-600 dark:text-zinc-300">{{ $count }}</span>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Bottom section: Overdue + Upcoming + Top Lists --}}
  <div class="grid gap-4 lg:grid-cols-3">

    {{-- Overdue Tasks --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
      <div class="mb-4 flex items-center gap-2">
        <flux:icon name="clock" class="size-4 text-red-500" />
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Terlambat</h3>
        @if ($overdueTasks > 0)
          <span class="ml-auto rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-600 dark:bg-red-900/30 dark:text-red-400">
            {{ $overdueTasks }}
          </span>
        @endif
      </div>

      @forelse ($overdueTaskList as $task)
        <div class="mb-2.5 flex items-start gap-2.5 rounded-lg px-2 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800"
          wire:key="overdue-{{ $task->id }}">
          <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-red-400"></div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $task->title }}</p>
            <p class="text-[11px] text-zinc-400">
              {{ $task->taskList?->space?->name }} · {{ $task->due_date->format('d M Y') }}
            </p>
          </div>
          <span class="shrink-0 rounded-full px-1.5 py-0.5 text-[10px] font-medium text-white"
            style="background-color: {{ $task->status?->color ?? '#6b7280' }}">
            {{ $task->status?->name }}
          </span>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center py-6 text-center">
          <flux:icon name="check-circle" class="mb-2 size-8 text-green-400" />
          <p class="text-sm text-zinc-400">Tidak ada task terlambat</p>
        </div>
      @endforelse
    </div>

    {{-- Upcoming Tasks --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
      <div class="mb-4 flex items-center gap-2">
        <flux:icon name="calendar-days" class="size-4 text-indigo-500" />
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">7 Hari ke Depan</h3>
      </div>

      @forelse ($upcomingTasks as $task)
        <div class="mb-2.5 flex items-start gap-2.5 rounded-lg px-2 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-800"
          wire:key="upcoming-{{ $task->id }}">
          <div class="mt-0.5 h-2 w-2 shrink-0 rounded-full bg-indigo-400"></div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $task->title }}</p>
            <p class="text-[11px] text-zinc-400">{{ $task->due_date->format('d M Y') }}</p>
          </div>
          @php
            $diff = today()->diffInDays($task->due_date);
          @endphp
          <span class="shrink-0 text-[11px] font-medium {{ $diff <= 2 ? 'text-orange-500' : 'text-zinc-400' }}">
            {{ $diff === 1 ? 'besok' : $diff . ' hari' }}
          </span>
        </div>
      @empty
        <div class="flex flex-col items-center justify-center py-6 text-center">
          <flux:icon name="calendar" class="mb-2 size-8 text-zinc-300 dark:text-zinc-600" />
          <p class="text-sm text-zinc-400">Tidak ada task mendatang</p>
        </div>
      @endforelse
    </div>

    {{-- Top Lists by Task Count --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
      <div class="mb-4 flex items-center gap-2">
        <flux:icon name="queue-list" class="size-4 text-zinc-500" />
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">List Terpadat</h3>
      </div>

      @forelse ($topLists as $list)
        <div class="mb-2 flex items-center gap-3" wire:key="list-{{ $list->id }}">
          <a href="{{ route('project-management.lists.show', [$list->space, $list]) }}"
            wire:navigate
            class="min-w-0 flex-1 truncate text-sm font-medium text-zinc-700 hover:text-indigo-600 dark:text-zinc-300 dark:hover:text-indigo-400">
            {{ $list->name }}
          </a>
          <div class="flex items-center gap-2">
            <div class="h-1.5 w-16 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
              @php $maxCount = $topLists->first()->tasks_count; @endphp
              <div class="h-full rounded-full bg-indigo-400"
                style="width: {{ $maxCount > 0 ? round(($list->tasks_count / $maxCount) * 100) : 0 }}%"></div>
            </div>
            <span class="w-6 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $list->tasks_count }}</span>
          </div>
        </div>
      @empty
        <p class="text-sm text-zinc-400">Belum ada list.</p>
      @endforelse
    </div>
  </div>

  {{-- Recent Activity --}}
  <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
    <div class="mb-4 flex items-center gap-2">
      <flux:icon name="bolt" class="size-4 text-amber-500" />
      <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Task Terbaru Diperbarui</h3>
    </div>

    @if ($recentTasks->isEmpty())
      <p class="text-sm text-zinc-400">Belum ada task.</p>
    @else
      <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
        @foreach ($recentTasks as $task)
          <div class="flex items-center gap-4 py-2.5" wire:key="recent-{{ $task->id }}">
            {{-- Priority dot --}}
            <div class="h-2 w-2 shrink-0 rounded-full"
              style="background-color: {{ $task->priority_color }}"></div>

            {{-- Title + list --}}
            <div class="min-w-0 flex-1">
              <p class="truncate text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $task->title }}</p>
              <p class="text-[11px] text-zinc-400">
                {{ $task->taskList?->space?->name }} › {{ $task->taskList?->name }}
              </p>
            </div>

            {{-- Status badge --}}
            <span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-medium text-white"
              style="background-color: {{ $task->status?->color ?? '#6b7280' }}">
              {{ $task->status?->name }}
            </span>

            {{-- Assignees --}}
            <div class="hidden shrink-0 items-center -space-x-1.5 sm:flex">
              @foreach ($task->assignees->take(3) as $assignee)
                <flux:avatar :name="$assignee->name" :initials="$assignee->initials()" :src="$assignee->avatar" size="xs"
                  class="ring-2 ring-white dark:ring-zinc-900" />
              @endforeach
              @if ($task->assignees->count() > 3)
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-200 text-[10px] font-medium text-zinc-600 ring-2 ring-white dark:bg-zinc-700 dark:text-zinc-300 dark:ring-zinc-900">
                  +{{ $task->assignees->count() - 3 }}
                </span>
              @endif
            </div>

            {{-- Updated at --}}
            <span class="hidden shrink-0 text-xs text-zinc-400 lg:block">
              {{ $task->updated_at->diffForHumans() }}
            </span>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</div>
