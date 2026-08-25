<div class="flex h-full min-h-0 flex-col">
  <div class="shrink-0">
    @include('livewire.partials.board.toolbar')
  </div>

  <div class="kanban-board flex min-h-[20rem] flex-1 gap-4 overflow-x-auto overflow-y-hidden pb-3 -mx-3 px-3 sm:mx-0 sm:px-0"
    x-data="kanbanBoard({{ $canManage ? 'true' : 'false' }})" x-init="init()" @pointerdown="startDrag"
    @pointerup="stopDrag" @pointercancel="stopDrag" @lostpointercapture="stopDrag" @pointermove="doDrag"
    @wheel="onWheel" @auxclick.prevent>
    @foreach ($this->statuses as $status)
      <div wire:key="status-{{ $status->id }}"
        class="kanban-col-wrapper group/col flex max-h-full w-72 shrink-0 flex-col rounded-xl border border-zinc-200/80 bg-zinc-50 dark:border-zinc-700/60 dark:bg-zinc-800/50"
        data-column-id="{{ $status->id }}">

        @include('livewire.partials.board.column-header')

        <div class="kanban-column custom-scrollbar flex min-h-0 flex-1 flex-col gap-2 overflow-y-auto overscroll-y-contain px-2 pb-2"
          data-status-id="{{ $status->id }}" data-status-type="{{ $status->type }}"
          wire:key="col-status-{{ $status->id }}">
          @foreach ($status->tasks as $task)
            @include('livewire.partials.board.task-card')
          @endforeach

          <div data-empty-hint @class([
              'kanban-empty pointer-events-none shrink-0 py-8 text-center text-xs text-zinc-400 transition-colors dark:border-zinc-700 dark:text-zinc-500',
              'hidden' => $status->tasks->isNotEmpty(),
          ])>
            Belum ada tugas
          </div>
        </div>

      </div>
    @endforeach

    @if ($canManage)
      @include('livewire.partials.board.add-column')
    @endif
  </div>

  @livewire('task-form-modal', ['space' => $space, 'taskList' => $taskList])
  @livewire('task-delete-modal')

  <x-task-detail-modal name="task-detail-board" keyPrefix="board-detail" :selectedTaskId="$selectedTaskId" />

  @include('livewire.partials.board.column-modals')
</div>

@script
  <script>
    @include('livewire.partials.board.kanban-script')
  </script>
@endscript
