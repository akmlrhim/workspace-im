<div>
  <div class="mb-4">
    @include('livewire.project.partials.breadcrumb')
    <div class="flex items-center justify-between gap-3">
      <h1 class="hidden lg:block text-2xl font-bold text-zinc-900 dark:text-white">
        {{ $taskList->name }}
      </h1>
      <div class="flex items-center gap-2">
        @include('livewire.project.partials.view-toggle', ['active' => 'board'])
      </div>
    </div>

    <div class="mt-3 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-700/60 dark:bg-zinc-800/40">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

        <div class="flex items-center justify-between sm:w-auto">
          <span
            class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
            <flux:icon name="funnel" class="size-3.5" />
            Filter
          </span>

          @if ($this->hasActiveFilter)
            <button wire:click="clearFilters"
              class="flex items-center gap-1 rounded-md px-2 py-1 text-[11px] font-medium text-red-600 transition-colors hover:bg-red-50 sm:hidden dark:text-red-400 dark:hover:bg-red-500/10">
              <flux:icon name="x-mark" class="size-3" />
              Hapus
            </button>
          @endif
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-1 sm:flex-wrap sm:items-center">

          <flux:select wire:model.live="filterAssigneeId" size="sm" class="w-full sm:!w-auto sm:min-w-[140px]">
            <flux:select.option value="">Semua Anggota</flux:select.option>
            @foreach ($this->availableAssignees as $member)
              <flux:select.option value="{{ $member->id }}">{{ $member->name }}</flux:select.option>
            @endforeach
          </flux:select>

          <flux:select wire:model.live="filterPriority" size="sm" class="w-full sm:!w-auto sm:min-w-[140px]">
            <flux:select.option value="">Semua Prioritas</flux:select.option>
            <flux:select.option value="urgent">🔴 Urgent</flux:select.option>
            <flux:select.option value="high">🟠 High</flux:select.option>
            <flux:select.option value="normal">🔵 Normal</flux:select.option>
            <flux:select.option value="low">⚪ Low</flux:select.option>
          </flux:select>

          @if ($this->availableLabels->isNotEmpty())
            <flux:select wire:model.live="filterLabelId" size="sm"
              class="col-span-2 w-full sm:col-span-1 sm:!w-auto sm:min-w-[140px]">
              <flux:select.option value="">Semua Label</flux:select.option>
              @foreach ($this->availableLabels as $label)
                <flux:select.option value="{{ $label->id }}">{{ $label->name }}</flux:select.option>
              @endforeach
            </flux:select>
          @endif
        </div>

        @if ($this->hasActiveFilter)
          <button wire:click="clearFilters"
            class="hidden sm:flex ml-auto items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
            <flux:icon name="x-mark" class="size-3" />
            Hapus Filter
          </button>
        @endif

      </div>
    </div>
  </div>

  <div class="kanban-board flex gap-4 overflow-x-auto overflow-y-hidden pb-4 -mx-3 px-3 sm:mx-0 sm:px-0"
    x-data="kanbanBoard({{ $canManage ? 'true' : 'false' }})" x-init="init()" @pointerdown="startDrag" @pointerleave="stopDrag"
    @pointerup="stopDrag" @pointercancel="stopDrag" @pointermove="doDrag">
    @foreach ($this->statuses as $status)
      @php
        $overdueCount = $status->tasks
            ->filter(fn($t) => $t->due_date && $t->due_date->isPast() && !$t->due_date->isToday())
            ->count();
      @endphp
      <div wire:key="status-{{ $status->id }}"
        class="kanban-col-wrapper flex w-72 shrink-0 flex-col rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border-t-4 {{ $canManage ? 'cursor-grab' : '' }}"
        style="border-top-color: {{ $status->color }}" data-column-id="{{ $status->id }}">
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-2 min-w-0 flex-1">
            <div class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $status->color }}"></div>

            @if ($renamingColumnId === $status->id)
              <form wire:submit="saveColumnRename" class="flex items-center gap-1 min-w-0 flex-1">
                <flux:input wire:model="renamingColumnName"
                  class="w-full !px-1.5 !py-0.5 text-sm font-semibold !rounded" autofocus
                  @keydown.escape="$wire.cancelColumnRename()" />
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
                class="rounded-md bg-zinc-200 px-1.5 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
                {{ $status->tasks->count() }}
              </span>
              @if ($overdueCount > 0)
                <span
                  class="inline-flex items-center gap-0.5 rounded-md bg-red-100 px-1.5 py-0.5 text-[10px] font-bold text-red-700 dark:bg-red-500/15 dark:text-red-400"
                  title="{{ $overdueCount }} tugas lewat tenggat">
                  <flux:icon name="exclamation-triangle" class="size-3" />
                  {{ $overdueCount }}
                </span>
              @endif
            @endif
          </div>

          @if ($canManage)
            <div class="relative flex items-center gap-0.5" x-data="{ open: false }">
              <button @click="$wire.set('createInStatusId', {{ $status->id }})"
                class="rounded-md p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 transition-colors"
                title="Tambah Tugas">
                <flux:icon name="plus" class="size-4" />
              </button>

              <button @click="open = !open"
                class="rounded-md p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 transition-colors">
                <flux:icon name="ellipsis-horizontal" class="size-4" />
              </button>

              <div x-show="open" x-cloak @click.away="open = false"
                x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
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
          @endif
        </div>

        <div class="kanban-column flex min-h-[100px] flex-col gap-2 px-2 pb-2" data-status-id="{{ $status->id }}"
          wire:key="col-status-{{ $status->id }}">
          @foreach ($status->tasks as $task)
            @php
              $dd = $task->due_date;
              $isToday = $dd?->isToday();
              $isPast = $dd && $dd->isPast() && !$isToday;
              $isTomorrow = $dd?->isTomorrow();
              $ddClass = $isPast
                  ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400 font-semibold'
                  : ($isToday
                      ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400 font-semibold'
                      : ($isTomorrow
                          ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400'
                          : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700/60 dark:text-zinc-300'));
              $ddLabel = $dd
                  ? ($isToday
                      ? 'Hari ini'
                      : ($isTomorrow
                          ? 'Besok'
                          : ($isPast
                              ? (int) ceil($dd->diffInDays(now(), true)) . 'h lewat'
                              : $dd->isoFormat('D MMM'))))
                  : null;
            @endphp
            <div wire:key="task-{{ $task->id }}"
              class="task-card group/card relative cursor-pointer overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-zinc-300 {{ $task->can_drag ? 'active:cursor-grabbing active:shadow-lg active:ring-2 active:ring-indigo-400/30' : 'task-locked' }} dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
              data-task-id="{{ $task->id }}" data-can-drag="{{ $task->can_drag ? '1' : '0' }}"
              @click="if (!_isDraggingTask) $wire.openTaskDetail({{ $task->id }})">

              <div class="absolute inset-y-0 left-0 w-1" style="background-color: {{ $task->priority_color }}"
                title="Prioritas: {{ ucfirst($task->priority) }}"></div>

              <div class="p-3 pl-3.5">
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

                <div class="mb-2 pr-8 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $task->title }}</div>

                @if ($task->can_drag)
                  <div class="absolute right-2 top-2 opacity-0 group-hover/card:opacity-60 transition-opacity">
                    <flux:icon name="bars-2" class="size-4 text-zinc-400" />
                  </div>
                @endif

                <div class="flex items-center justify-between gap-2">
                  <div class="flex flex-wrap items-center gap-1.5 text-zinc-400 dark:text-zinc-500">
                    @if (filled($task->description))
                      <span title="Ada Deskripsi">
                        <flux:icon name="document-text" class="size-3.5" />
                      </span>
                    @endif

                    @if ($task->subtasks->isNotEmpty())
                      <span class="flex items-center gap-0.5 text-[10px]" title="Subtask">
                        <flux:icon name="bars-3-bottom-left" class="size-3.5" />
                        {{ $task->subtasks->where('is_completed', true)->count() }}/{{ $task->subtasks->count() }}
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
                      <span
                        class="inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 text-[10px] {{ $ddClass }}"
                        title="Tenggat {{ $dd->format('d M Y') }}">
                        <flux:icon name="clock" class="size-3" />
                        {{ $ddLabel }}
                      </span>
                    @endif
                  </div>

                  @if ($task->assignees->isNotEmpty())
                    <div class="flex shrink-0 -space-x-1.5">
                      @foreach ($task->assignees->take(3) as $assignee)
                        <flux:avatar circle :name="$assignee->name" :initials="$assignee->initials()"
                          :src="$assignee->avatar" size="xs" class="ring-2 ring-white dark:ring-zinc-800" />
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
          @endforeach
        </div>

        @if ($canManage)
          <div class="px-2 pb-2">
            @if ($createInStatusId === $status->id)
              <form wire:submit="createTaskInStatus({{ $status->id }})" class="space-y-2"
                wire:key="create-in-{{ $status->id }}">
                <flux:input wire:model="newTaskTitle" placeholder="Nama tugas..." size="sm" autofocus />
                <div class="flex justify-end gap-1">
                  <flux:button size="xs" variant="ghost" @click="$wire.set('createInStatusId', null)">Batal
                  </flux:button>
                  <flux:button size="xs" variant="primary" type="submit">Tambah</flux:button>
                </div>
              </form>
            @else
              <button @click="$wire.set('createInStatusId', {{ $status->id }})"
                class="flex w-full items-center gap-1.5 rounded-lg px-2 py-1.5 text-sm text-zinc-400 transition-colors hover:bg-zinc-200/60 hover:text-zinc-600 dark:hover:bg-zinc-700/60 dark:hover:text-zinc-300">
                <flux:icon name="plus" class="size-4" />
                <span>Tambah Tugas</span>
              </button>
            @endif
          </div>
        @endif
      </div>
    @endforeach

    @if ($canManage)
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
                    <button type="button" @click="$wire.set('newColumnColor', '{{ $color }}')"
                      class="h-5 w-5 rounded-full border-2 transition-transform hover:scale-110 {{ $newColumnColor === $color ? 'border-zinc-900 dark:border-white scale-110' : 'border-transparent' }}"
                      style="background-color: {{ $color }}"></button>
                  @endforeach
                </div>
              </div>
              <div class="flex justify-end gap-1">
                <flux:button size="xs" variant="ghost" @click="$wire.set('showNewColumnInput', false)">Batal
                </flux:button>
                <flux:button size="xs" variant="primary" type="submit">Tambah Kolom</flux:button>
              </div>
            </form>
          </div>
        @else
          <button @click="$wire.set('showNewColumnInput', true)"
            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-zinc-300 text-sm font-medium text-zinc-400 transition-all hover:border-indigo-400 hover:bg-indigo-50/50 hover:text-indigo-500 dark:border-zinc-600 dark:hover:border-indigo-500/50 dark:hover:bg-indigo-900/10 dark:hover:text-indigo-400">
            <flux:icon name="plus" class="size-5 transition-transform group-hover:rotate-90" />
            <span>Tambah Kolom Baru</span>
          </button>
        @endif
      </div>
    @endif
  </div>

  @livewire('project.task-form-modal', ['space' => $space, 'taskList' => $taskList])
  @livewire('project.task-delete-modal')

  @if ($showTaskDetail && $selectedTaskId)
    <flux:modal wire:model="showTaskDetail"
      class="w-full max-w-5xl max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
      <div class="max-h-[85vh] overflow-y-auto px-1 -mx-1 max-sm:max-h-none max-sm:h-[calc(100dvh-4rem)]">
        <livewire:project.task-detail :taskId="$selectedTaskId" :key="'board-detail-' . $selectedTaskId" />
      </div>
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
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showDeleteColumnConfirm', false)">
          Batal</flux:button>
        <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteColumn">Hapus</flux:button>
      </div>
    </div>
  </flux:modal>
</div>

@script
  <script>
    Alpine.data('kanbanBoard', (canManage = true) => ({
      _sortableInstances: [],
      _columnSortable: null,
      _hookCleanup: null,
      _dragFrame: null,
      _initDebounce: null,
      _isDraggingTask: false,
      _canManage: canManage,

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
        if (typeof window.Sortable === 'undefined') {
          setTimeout(() => this._initAll(), 50);
          return;
        }
        this._destroyAll();
        this._initTaskSortables();
        this._initColumnSortable();
      },

      _initColumnSortable() {
        if (!this._canManage) return;
        const board = this.$el;
        if (!board) return;

        this._columnSortable = new window.Sortable(board, {
          animation: 200,
          easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
          draggable: '.kanban-col-wrapper',
          handle: '.kanban-col-wrapper',
          ghostClass: 'kanban-col-ghost',
          chosenClass: 'kanban-col-chosen',
          dragClass: 'kanban-col-drag',
          direction: 'horizontal',
          delay: 250,
          delayOnTouchOnly: true,
          touchStartThreshold: 10,
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
          const instance = new window.Sortable(column, {
            group: 'kanban-tasks',
            animation: 200,
            easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
            ghostClass: 'kanban-ghost',
            chosenClass: 'kanban-chosen',
            dragClass: 'kanban-drag',
            draggable: '.task-card',
            fallbackOnBody: true,
            swapThreshold: 0.65,
            delay: 250,
            delayOnTouchOnly: true,
            touchStartThreshold: 10,
            filter: 'button, a, input, select, textarea, .task-locked',
            preventOnFilter: false,
            forceFallback: false, // <-- was true, caused ghost card glitch
            onStart: (evt) => {
              document.body.classList.add('is-dragging');
              this._isDraggingTask = true;
              evt.item.style.transform = 'rotate(2deg)';
            },
            onEnd: (evt) => {
              document.body.classList.remove('is-dragging');
              evt.item.style.transform = '';
              const taskId = parseInt(evt.item.dataset.taskId);
              const newStatusId = parseInt(evt.to.dataset.statusId);
              const orderedIds = Array.from(evt.to.querySelectorAll('.task-card'))
                .map(el => parseInt(el.dataset.taskId));

              // Update column count badges immediately (optimistic UI)
              this._updateColumnCounts(evt.from, evt.to);

              this.$wire.moveTask(taskId, newStatusId, orderedIds);
              // Reset after click event has fired to prevent opening detail after drag
              setTimeout(() => {
                this._isDraggingTask = false;
              }, 100);
            },
          });

          column._sortableInstance = instance;
          this._sortableInstances.push(instance);
        });
      },

      _updateColumnCounts(fromCol, toCol) {
        // Update the task count badge in column headers after drag
        [fromCol, toCol].forEach(col => {
          if (!col) return;
          const wrapper = col.closest('.kanban-col-wrapper');
          if (!wrapper) return;
          const badge = wrapper.querySelector('.rounded-full.bg-zinc-200, .rounded-full.dark\\:bg-zinc-700');
          if (badge) {
            badge.textContent = col.querySelectorAll('.task-card').length;
          }
        });
      },

      startDrag(e) {
        // Only mouse drag-to-pan — touch uses native momentum scroll.
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
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
        if (e.pointerType && e.pointerType !== 'mouse') return;

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

    }));
  </script>
@endscript
