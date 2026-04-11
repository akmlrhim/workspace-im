<div class="space-y-6">
  {{-- Header --}}
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">Tugas Saya</flux:heading>
      <flux:subheading>
        {{ $totalCount }} tugas ditugaskan kepada Anda di seluruh ruang
      </flux:subheading>
    </div>

    <flux:select wire:model.live="filterPriority" size="sm" class="w-full sm:w-44">
      <flux:select.option value="">Semua Prioritas</flux:select.option>
      <flux:select.option value="urgent">🔴 Urgent</flux:select.option>
      <flux:select.option value="high">🟠 High</flux:select.option>
      <flux:select.option value="normal">🔵 Normal</flux:select.option>
      <flux:select.option value="low">⚪ Low</flux:select.option>
    </flux:select>
  </div>

  @if ($totalCount === 0)
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
              class="group flex items-center gap-3 rounded-xl border bg-white p-4 transition duration-150 hover:shadow-sm dark:bg-zinc-900
                {{ $isClosedType ? 'border-zinc-100 opacity-70 dark:border-zinc-800' : 'border-zinc-200 hover:border-indigo-200 dark:border-zinc-700 dark:hover:border-indigo-500/30' }}">

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
                    <flux:avatar :name="$assignee->name" :src="$assignee->avatar ?? null" size="xs"
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
                  class="flex shrink-0 items-center gap-1 rounded-md px-2 py-1 text-xs font-medium
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
