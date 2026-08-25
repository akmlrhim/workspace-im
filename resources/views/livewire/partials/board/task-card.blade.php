@php
  $dd = $task->due_date;
  $isClosed = $status->type === 'closed';
  $isToday = $dd?->isToday();
  $isPast = $dd && $dd->isPast() && !$isToday;
  $isTomorrow = $dd?->isTomorrow();

  if (!$dd) {
      $ddClass = '';
      $ddLabel = null;
      $ddIcon = 'clock';
  } elseif ($isClosed) {
      $ddClass = 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400';
      $ddLabel = $dd->isoFormat('D MMM');
      $ddIcon = 'check-circle';
  } elseif ($isPast) {
      $ddClass = 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400 font-semibold';
      $ddLabel = $dd->isoFormat('D MMM');
      $ddIcon = 'clock';
  } elseif ($isToday) {
      $ddClass = 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400 font-semibold';
      $ddLabel = 'Hari ini';
      $ddIcon = 'clock';
  } elseif ($isTomorrow) {
      $ddClass = 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400';
      $ddLabel = 'Besok';
      $ddIcon = 'clock';
  } else {
      $ddClass = 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700/60 dark:text-zinc-300';
      $ddLabel = $dd->isoFormat('D MMM');
      $ddIcon = 'clock';
  }
@endphp

<div wire:key="task-{{ $task->id }}"
  class="task-card group/card relative shrink-0 cursor-pointer overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-zinc-300 {{ $task->can_drag ? 'active:cursor-grabbing active:shadow-lg active:ring-2 active:ring-indigo-400/30' : 'task-locked' }} dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
  data-task-id="{{ $task->id }}" data-can-drag="{{ $task->can_drag ? '1' : '0' }}"
  @click="if (!_isDraggingTask && !$event.target.closest('[data-no-drag]')) { $flux.modal('task-detail-board').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); } }">

  <div class="p-3">
    @if ($task->labels->isNotEmpty())
      <div class="mb-2 flex flex-wrap gap-1">
        @foreach ($task->labels as $label)
          <span class="rounded-full px-2 py-0.5 text-[10px] font-medium text-white"
            style="background-color: {{ $label->color }}">
            {{ $label->name }}
          </span>
        @endforeach
      </div>
    @endif

    <div x-data="{ title: @js($task->title) }"
      @task-title-updated.window="if ($event.detail.taskId === {{ $task->id }}) title = $event.detail.title"
      x-text="title" class="mb-2 pr-8 text-sm font-medium text-zinc-900 dark:text-zinc-100"></div>

    <div class="flex items-center justify-between gap-2">
      <div class="flex flex-wrap items-center gap-1.5 text-zinc-400 dark:text-zinc-500">
        @if (filled($task->description))
          <span title="Ada Deskripsi">
            <flux:icon name="document-text" class="size-3.5" />
          </span>
        @endif

        @if ($task->subtasks_count > 0)
          <span class="flex items-center gap-0.5 text-[10px]" title="Subtask">
            <flux:icon name="bars-3-bottom-left" class="size-3.5" />
            {{ $task->completed_subtasks_count }}/{{ $task->subtasks_count }}
          </span>
        @endif

        @if ($task->comments_count > 0)
          <span class="flex items-center gap-0.5 text-[10px]" title="Komentar">
            <flux:icon name="chat-bubble-left" class="size-3.5" />
            {{ $task->comments_count }}
          </span>
        @endif

        @if ($task->attachments_count > 0)
          <span class="flex items-center gap-0.5 text-[10px]" title="Lampiran">
            <flux:icon name="paper-clip" class="size-3.5" />
            {{ $task->attachments_count }}
          </span>
        @endif

        @if ($dd)
          <span data-due-badge data-open-class="{{ $ddClass }}" data-open-label="{{ $ddLabel }}"
            data-closed-label="{{ $dd->isoFormat('D MMM') }}"
            class="inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 text-[10px] {{ $ddClass }}"
            title="Tenggat {{ $dd->format('d M Y') }}">
            <flux:icon name="{{ $ddIcon }}" class="size-3" />
            <span data-due-label>{{ $ddLabel }}</span>
          </span>
        @endif
      </div>

      @if ($task->assignees->isNotEmpty())
        <div class="flex shrink-0 -space-x-1.5">
          @foreach ($task->assignees->take(3) as $assignee)
            <flux:avatar circle :name="$assignee->name" :initials="$assignee->initials()" :src="$assignee->avatar"
              size="xs" class="ring-2 ring-white dark:ring-zinc-800" />
          @endforeach
          @if ($task->assignees->count() > 3)
            <div
              class="flex h-5 w-5 items-center justify-center rounded-full bg-zinc-200 text-[9px] font-medium text-zinc-600 ring-2 ring-white dark:bg-zinc-700 dark:text-zinc-400 dark:ring-zinc-800">
              +{{ $task->assignees->count() - 3 }}
            </div>
          @endif
        </div>
      @endif
    </div>
  </div>
</div>
