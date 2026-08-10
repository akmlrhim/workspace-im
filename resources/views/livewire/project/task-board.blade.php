<div>
  @include('livewire.project.partials.board.toolbar')

  <div class="kanban-board flex gap-4 overflow-x-auto overflow-y-hidden pb-4 -mx-3 px-3 sm:mx-0 sm:px-0"
    x-data="kanbanBoard({{ $canManage ? 'true' : 'false' }})" x-init="init()" @pointerdown="startDrag" @pointerleave="stopDrag"
    @pointerup="stopDrag" @pointercancel="stopDrag" @pointermove="doDrag">
    @foreach ($this->statuses as $status)
      <div wire:key="status-{{ $status->id }}"
        class="kanban-col-wrapper group/col flex w-72 shrink-0 flex-col rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border-t-4"
        style="border-top-color: {{ $status->color }}" data-column-id="{{ $status->id }}">

        @include('livewire.project.partials.board.column-header')

        <div class="kanban-column flex min-h-[100px] flex-col gap-2 px-2 pb-2" data-status-id="{{ $status->id }}"
          data-status-type="{{ $status->type }}" wire:key="col-status-{{ $status->id }}">
          @foreach ($status->tasks as $task)
            @include('livewire.project.partials.board.task-card')
          @endforeach
        </div>

      </div>
    @endforeach

    @if ($canManage)
      @include('livewire.project.partials.board.add-column')
    @endif
  </div>

  @livewire('project.task-form-modal', ['space' => $space, 'taskList' => $taskList])
  @livewire('project.task-delete-modal')

  <x-task-detail-modal name="task-detail-board" keyPrefix="board-detail" :selectedTaskId="$selectedTaskId" />

  @include('livewire.project.partials.board.column-modals')
</div>

@script
  <script>
    @include('livewire.project.partials.board.kanban-script')
  </script>
@endscript
