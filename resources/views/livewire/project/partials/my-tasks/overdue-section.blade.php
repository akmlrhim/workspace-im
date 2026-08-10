<div class="rounded-xl border border-red-200 bg-red-50/50 p-4 dark:border-red-800/30 dark:bg-red-950/20">
  <div class="mb-3 flex items-center gap-3">
    <flux:icon name="exclamation-triangle" class="size-4 text-red-500" />
    <span class="text-sm font-semibold text-red-600 dark:text-red-400">Tugas Terlambat</span>
    <span class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-600 dark:bg-red-900/30 dark:text-red-400">
      {{ $overdueTasks->count() }}
    </span>
    <div class="flex-1 border-t border-red-200 dark:border-red-800/50"></div>
  </div>

  <div class="space-y-2">
    @foreach ($overdueTasks as $task)
      @php
        $daysLate = (int) $task->due_date->startOfDay()->diffInDays(now()->startOfDay());
      @endphp
      <div wire:key="overdue-{{ $task->id }}"
        class="flex items-center gap-3 rounded-lg border border-red-100 bg-white p-3 dark:border-red-800/20 dark:bg-zinc-900">
        <div class="min-w-0 flex-1">
          <button
            @click="$flux.modal('task-detail-mytasks').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
            class="block w-full cursor-pointer truncate text-left text-sm font-semibold text-zinc-900 hover:text-red-600 dark:text-zinc-100 dark:hover:text-red-400">
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
        <div class="flex shrink-0 flex-col items-end gap-1">
          <span class="flex items-center gap-1 rounded-md bg-red-100 px-2 py-1 text-xs font-medium text-red-600 dark:bg-red-900/30 dark:text-red-400">
            <flux:icon name="exclamation-circle" class="size-3" />
            {{ $daysLate }}h terlambat
          </span>
          <span class="text-[11px] text-zinc-400 dark:text-zinc-500">
            due {{ $task->due_date->format('d M Y') }}
          </span>
        </div>
      </div>
    @endforeach
  </div>
</div>
