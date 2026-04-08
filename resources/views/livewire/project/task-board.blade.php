<div>
  <div class="mb-6">
    @include('livewire.project.partials.breadcrumb')
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $taskList->name }}</h1>
      <div class="flex items-center gap-2">
        @include('livewire.project.partials.view-toggle', ['active' => 'board'])
      </div>
    </div>
  </div>

  <div class="flex gap-4 overflow-x-auto pb-4" x-data="kanbanBoard()" x-init="init()" @mousedown="startDrag"
    @mouseleave="stopDrag" @mouseup="stopDrag" @mousemove="doDrag" @wheel.passive="handleWheel">
    @foreach ($statuses as $status)
      <div
        class="kanban-col-wrapper flex w-72 shrink-0 flex-col rounded-xl bg-zinc-50 dark:bg-zinc-800/50 cursor-grab border-t-4"
        style="border-top-color: {{ $status->color }}" wire:key="status-col-{{ $status->id }}"
        data-column-id="{{ $status->id }}">
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-2 min-w-0 flex-1">
            <div class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $status->color }}"></div>

            @if ($renamingColumnId === $status->id)
              <form wire:submit="saveColumnRename" class="flex items-center gap-1 min-w-0 flex-1">
                <input type="text" wire:model="renamingColumnName"
                  class="w-full rounded border border-indigo-300 bg-white px-1.5 py-0.5 text-sm font-semibold text-zinc-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200"
                  autofocus @keydown.escape="$wire.cancelColumnRename()" />
                <button type="submit" class="p-0.5 text-emerald-500 hover:text-emerald-600">
                  <flux:icon name="check" class="size-3.5" />
                </button>
                <button type="button" wire:click="cancelColumnRename" class="p-0.5 text-zinc-400 hover:text-zinc-600">
                  <flux:icon name="x-mark" class="size-3.5" />
                </button>
              </form>
            @else
              <span class="truncate text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $status->name }}</span>
              <span
                class="rounded-full bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                {{ $status->tasks->count() }}
              </span>
            @endif
          </div>

          <div class="relative flex items-center gap-1" x-data="{ open: false }">
            <button wire:click="$set('createInStatusId', {{ $status->id }})"
              class="rounded-md p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 transition-colors"
              title="Tambah Tugas">
              <flux:icon name="plus" class="size-4" />
            </button>

            <button @click="open = !open"
              class="rounded-md p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 transition-colors">
              <flux:icon name="ellipsis-horizontal" class="size-4" />
            </button>

            <div x-show="open" x-cloak @click.away="open = false" x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
              x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
              x-transition:leave-end="opacity-0 scale-95"
              class="absolute right-0 top-8 z-30 w-44 rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
              <button @click="open = false; $wire.startRenamingColumn({{ $status->id }})"
                class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700">
                <flux:icon name="pencil" class="size-3.5" /> Ubah Nama
              </button>
              <button @click="open = false; $wire.confirmDeleteColumn({{ $status->id }})"
                class="flex w-full items-center gap-2 px-3 py-1.5 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-zinc-700">
                <flux:icon name="trash" class="size-3.5" /> Hapus Kolom
              </button>
            </div>
          </div>
        </div>

        <div class="kanban-column flex min-h-[100px] flex-col gap-2 px-2 pb-2" data-status-id="{{ $status->id }}">
          @foreach ($status->tasks as $task)
            <div
              class="task-card group/card relative cursor-pointer rounded-lg border border-zinc-200 bg-white p-3 shadow-sm transition-all hover:shadow-md active:cursor-grabbing active:shadow-lg active:ring-2 active:ring-indigo-400/30 dark:border-zinc-700 dark:bg-zinc-800"
              data-task-id="{{ $task->id }}" wire:key="board-task-{{ $task->id }}"
              @click.stop="$wire.openTaskDetail({{ $task->id }})">
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

              <div class="mb-2 pr-10 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</div>

              <div class="absolute right-2 top-2 opacity-0 group-hover/card:opacity-60 transition-opacity">
                <flux:icon name="bars-2" class="size-4 text-zinc-400" />
              </div>

              <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="h-2 w-2 shrink-0 rounded-full" style="background-color: {{ $task->priority_color }}"
                    title="Priority: {{ ucfirst($task->priority) }}"></div>

                  @if (filled($task->description))
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500" title="Ada Deskripsi">
                      <flux:icon name="document-text" class="inline size-3" />
                    </span>
                  @endif

                  @if ($task->subtasks->isNotEmpty())
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500" title="Checklist">
                      <flux:icon name="bars-3-bottom-left" class="inline size-3" />
                      {{ $task->subtasks->where('is_completed', true)->count() }}/{{ $task->subtasks->count() }}
                    </span>
                  @endif

                  @if ($task->comments_count > 0)
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500" title="Komentar">
                      <flux:icon name="chat-bubble-left" class="inline size-[11px] mb-0.5" />
                      {{ $task->comments_count }}
                    </span>
                  @endif

                  @if ($task->attachments_count > 0)
                    <span class="text-[10px] text-zinc-400 dark:text-zinc-500" title="Lampiran">
                      <flux:icon name="paper-clip" class="inline size-3" /> {{ $task->attachments_count }}
                    </span>
                  @endif

                  @if ($task->due_date)
                    <span
                      class="text-[10px] {{ $task->due_date->isPast() ? 'text-red-500 font-medium' : 'text-zinc-400 dark:text-zinc-500' }}"
                      title="Tenggat Waktu: {{ $task->due_date->format('d M') }}">
                      <flux:icon name="clock" class="inline size-3" /> {{ $task->due_date->format('M d') }}
                    </span>
                  @endif
                </div>

                @if ($task->assignees->isNotEmpty())
                  <div class="flex -space-x-1.5">
                    @foreach ($task->assignees->take(3) as $assignee)
                      <flux:avatar :name="$assignee->name" :initials="$assignee->initials()" size="xs"
                        class="ring-2 ring-white dark:ring-zinc-800" />
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
          @endforeach
        </div>

        <div class="px-2 pb-2">
          @if ($createInStatusId === $status->id)
            <form wire:submit="createTaskInStatus({{ $status->id }})" class="space-y-2"
              wire:key="create-in-{{ $status->id }}">
              <flux:input wire:model="newTaskTitle" placeholder="Nama tugas..." size="sm" autofocus />
              <div class="flex justify-end gap-1">
                <flux:button size="xs" variant="ghost" wire:click="$set('createInStatusId', null)">Batal
                </flux:button>
                <flux:button size="xs" variant="primary" type="submit">Tambah</flux:button>
              </div>
            </form>
          @else
            <button wire:click="$set('createInStatusId', {{ $status->id }})"
              class="flex w-full items-center gap-1.5 rounded-lg px-2 py-1.5 text-sm text-zinc-400 transition-colors hover:bg-zinc-200/60 hover:text-zinc-600 dark:hover:bg-zinc-700/60 dark:hover:text-zinc-300">
              <flux:icon name="plus" class="size-4" />
              <span>Tambah Tugas</span>
            </button>
          @endif
        </div>
      </div>
    @endforeach

    <div class="flex w-72 shrink-0 flex-col">
      @if ($showNewColumnInput)
        <div
          class="rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/50 p-3 dark:border-indigo-500/40 dark:bg-indigo-900/10">
          <form wire:submit="addColumn" class="space-y-3">
            <flux:input wire:model="newColumnName" placeholder="Nama kolom..." size="sm" autofocus />
            <div class="flex items-center gap-2">
              <span class="text-xs text-zinc-500 dark:text-zinc-400">Warna:</span>
              <div class="flex gap-1">
                @foreach (['#6b7280', '#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'] as $color)
                  <button type="button" wire:click="$set('newColumnColor', '{{ $color }}')"
                    class="h-5 w-5 rounded-full border-2 transition-transform hover:scale-110 {{ $newColumnColor === $color ? 'border-zinc-900 dark:border-white scale-110' : 'border-transparent' }}"
                    style="background-color: {{ $color }}"></button>
                @endforeach
              </div>
            </div>
            <div class="flex justify-end gap-1">
              <flux:button size="xs" variant="ghost" wire:click="$set('showNewColumnInput', false)">Batal
              </flux:button>
              <flux:button size="xs" variant="primary" type="submit">Tambah Kolom</flux:button>
            </div>
          </form>
        </div>
      @else
        <button wire:click="$set('showNewColumnInput', true)"
          class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-zinc-300 text-sm font-medium text-zinc-400 transition-all hover:border-indigo-400 hover:bg-indigo-50/50 hover:text-indigo-500 dark:border-zinc-600 dark:hover:border-indigo-500/50 dark:hover:bg-indigo-900/10 dark:hover:text-indigo-400">
          <flux:icon name="plus" class="size-5 transition-transform group-hover:rotate-90" />
          <span>Tambah Kolom Baru</span>
        </button>
      @endif
    </div>
  </div>

  @livewire('project.task-form-modal', ['space' => $space, 'taskList' => $taskList])
  @livewire('project.task-delete-modal')

  @if ($showTaskDetail && $selectedTaskId)
    <flux:modal wire:model="showTaskDetail" variant="flyout" class="w-full max-w-2xl">
      <livewire:project.task-detail :taskId="$selectedTaskId" :key="'board-detail-' . $selectedTaskId" />
    </flux:modal>
  @endif

  <flux:modal wire:model="showDeleteColumnConfirm" class="w-full max-w-sm">
    <div class="space-y-4 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
        <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
      </div>
      <flux:heading size="lg">Hapus Kolom?</flux:heading>
      <p class="text-sm text-zinc-500 dark:text-zinc-400">
        Semua tugas dalam kolom ini akan dipindahkan ke kolom pertama yang tersedia.
      </p>
      <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-center">
        <flux:button variant="ghost" class="w-full sm:w-auto" wire:click="$set('showDeleteColumnConfirm', false)">
          Batal</flux:button>
        <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteColumn">Hapus</flux:button>
      </div>
    </div>
  </flux:modal>
</div>

@script
  <script>
    Alpine.data('kanbanBoard', () => ({
      _sortableInstances: [],
      _columnSortable: null,
      _hookCleanup: null,
      _dragFrame: null,
      _initDebounce: null,

      isDown: false,
      startX: 0,
      scrollLeft: 0,

      init() {
        this.$nextTick(() => this._initAll());

        this._hookCleanup = Livewire.hook('morph.updated', ({
          el
        }) => {
          if (el?.classList?.contains('kanban-column') || el?.classList?.contains('kanban-col-wrapper')) {
            clearTimeout(this._initDebounce);
            this._initDebounce = setTimeout(() => this.$nextTick(() => this._initAll()), 50);
          }
        });

        Livewire.on('task-created-on-board', () => {
          this.$nextTick(() => this._initAll());
        });
      },

      destroy() {
        this._destroyAll();
        clearTimeout(this._initDebounce);
        if (typeof this._hookCleanup === 'function') this._hookCleanup();
      },

      _destroyAll() {
        this._sortableInstances.forEach(s => s?.destroy());
        this._sortableInstances = [];
        this._columnSortable?.destroy();
        this._columnSortable = null;
        document.querySelectorAll('.kanban-column').forEach(col => {
          col._sortableInstance = null;
        });
      },

      _initAll() {
        this._destroyAll();
        this._initTaskSortables();
        this._initColumnSortable();
      },

      _initColumnSortable() {
        const board = this.$el;
        if (!board) return;

        this._columnSortable = new Sortable(board, {
          animation: 200,
          easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
          draggable: '.kanban-col-wrapper',
          handle: '.kanban-col-wrapper',
          ghostClass: 'kanban-col-ghost',
          chosenClass: 'kanban-col-chosen',
          dragClass: 'kanban-col-drag',
          direction: 'horizontal',
          delay: 50,
          delayOnTouchOnly: true,
          touchStartThreshold: 8,
          filter: 'input, select, textarea, button, a, .task-card',
          preventOnFilter: false,
          swapThreshold: 0.5,
          forceFallback: false,
          fallbackOnBody: true,
          onStart: () => document.body.classList.add('is-dragging-column'),
          onEnd: (evt) => {
            document.body.classList.remove('is-dragging-column');
            const ids = Array.from(board.querySelectorAll('.kanban-col-wrapper'))
              .map(el => parseInt(el.dataset.columnId))
              .filter(Number.isFinite);
            if (ids.length) this.$wire.updateColumnOrder(ids);
          },
        });
      },

      _initTaskSortables() {
        document.querySelectorAll('.kanban-column').forEach(column => {
          const instance = new Sortable(column, {
            group: 'kanban-tasks',
            animation: 200,
            easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
            ghostClass: 'kanban-ghost',
            chosenClass: 'kanban-chosen',
            dragClass: 'kanban-drag',
            draggable: '.task-card',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            delay: 80, // <-- was 120, slightly faster
            delayOnTouchOnly: true,
            touchStartThreshold: 5,
            filter: 'button, a, input, select, textarea',
            preventOnFilter: false,
            forceFallback: false, // <-- was true, caused ghost card glitch
            onStart: (evt) => {
              document.body.classList.add('is-dragging');
              evt.item.style.transform = 'rotate(2deg)';
            },
            onEnd: (evt) => {
              document.body.classList.remove('is-dragging');
              evt.item.style.transform = '';
              const taskId = parseInt(evt.item.dataset.taskId);
              const newStatusId = parseInt(evt.to.dataset.statusId);
              const orderedIds = Array.from(evt.to.querySelectorAll('.task-card'))
                .map(el => parseInt(el.dataset.taskId));
              this.$wire.moveTask(taskId, newStatusId, orderedIds);
            },
          });

          column._sortableInstance = instance;
          this._sortableInstances.push(instance);
        });
      },

      startDrag(e) {
        if (e.button !== 0) return;
        if (e.target.closest('.task-card') || e.target.closest('button') || e.target.closest('input')) return;
        this.isDown = true;
        this.startX = e.pageX - this.$el.offsetLeft;
        this.scrollLeft = this.$el.scrollLeft;
        this.$el.classList.add('cursor-grabbing');
      },

      stopDrag() {
        if (!this.isDown) return;
        this.isDown = false;
        if (this._dragFrame) {
          cancelAnimationFrame(this._dragFrame);
          this._dragFrame = null;
        }
        this.$el.classList.remove('cursor-grabbing');
      },

      doDrag(e) {
        if (!this.isDown) return;

        // Stop custom panning if Sortable is active or no mouse buttons are pressed (stuck drag check)
        if (e.buttons === 0 || document.body.classList.contains('is-dragging') || document.body.classList.contains(
            'is-dragging-column')) {
          this.stopDrag();
          return;
        }

        e.preventDefault();
        if (this._dragFrame) return;
        this._dragFrame = requestAnimationFrame(() => {
          const x = e.pageX - this.$el.offsetLeft;
          this.$el.scrollLeft = this.scrollLeft - (x - this.startX) * 1.5;
          this._dragFrame = null;
        });
      },

      handleWheel(e) {
        if (Math.abs(e.deltaX) > 0 || e.shiftKey) return;
        if (document.body.classList.contains('is-dragging') || document.body.classList.contains(
            'is-dragging-column')) return;

        const el = this.$el;
        const atLeft = el.scrollLeft === 0;
        const atRight = Math.ceil(el.scrollLeft + el.clientWidth) >= el.scrollWidth;

        if ((e.deltaY < 0 && atLeft) || (e.deltaY > 0 && atRight)) return;

        el.scrollLeft += e.deltaY;
      },
    }));
  </script>
@endscript
