@if ($overdueTasks->isNotEmpty())
  @include('livewire.partials.my-tasks.overdue-section')
@endif

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
        @include('livewire.partials.my-tasks.task-card')
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
            <button
              @click="$flux.modal('task-detail-mytasks').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); }"
              class="block w-full cursor-pointer truncate text-left text-sm font-semibold text-zinc-900 hover:text-indigo-600 dark:text-zinc-100 dark:hover:text-indigo-400">
              {{ $task->title }}
            </button>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endif
