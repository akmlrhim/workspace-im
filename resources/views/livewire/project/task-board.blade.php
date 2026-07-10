<div>
  <div class="mb-4">
    @include('livewire.project.partials.breadcrumb')
    <div class="flex items-center justify-between gap-3 mb-4">
      <h1 class="hidden lg:block text-2xl font-bold text-zinc-900 dark:text-white">
        {{ $taskList->name }}
      </h1>
    </div>
    @include('livewire.project.partials.view-toggle', ['active' => 'board'])

    <div class="mt-4">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">

        <div class="flex items-center justify-between sm:w-auto">
          @if ($this->hasActiveFilter)
            <button wire:click="clearFilters"
              class="flex items-center gap-1 rounded-md px-2 py-1 text-[11px] font-medium text-red-600 transition-colors hover:bg-red-50 sm:hidden dark:text-red-400 dark:hover:bg-red-500/10">
              <flux:icon name="x-mark" class="size-3" />
              Hapus
            </button>
          @endif
        </div>

        <div class="relative w-full sm:w-64" x-data="{ showSuggestions: false }" @click.outside="showSuggestions = false"
          @keydown.escape="showSuggestions = false">
          <flux:input icon="magnifying-glass" size="sm" placeholder="Cari tugas..."
            wire:model.live.debounce.300ms="search" @focus="showSuggestions = true" @input="showSuggestions = true" />

          @if (mb_strlen(trim($search)) >= 2)
            <div x-show="showSuggestions" x-cloak x-transition:enter="transition ease-out duration-100"
              x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
              class="absolute inset-x-0 top-full z-40 mt-1 max-h-64 overflow-y-auto rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
              @forelse ($this->searchSuggestions as $suggestion)
                <button type="button" wire:key="search-suggestion-{{ $suggestion->id }}"
                  @click="showSuggestions = false; $flux.modal('task-detail-board').show(); if ($wire.selectedTaskId !== {{ $suggestion->id }}) { $wire.openTaskDetail({{ $suggestion->id }}) }"
                  class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700">
                  <span class="flex min-w-0 items-center gap-1.5">
                    <flux:icon name="magnifying-glass" class="size-3.5 shrink-0 text-zinc-400" />
                    <span class="truncate">{{ $suggestion->title }}</span>
                  </span>
                  @if ($suggestion->status)
                    <span
                      class="flex shrink-0 items-center gap-1 rounded-md bg-zinc-100 px-1.5 py-0.5 text-[10px] font-medium text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
                      <span class="h-1.5 w-1.5 rounded-full"
                        style="background-color: {{ $suggestion->status->color }}"></span>
                      {{ $suggestion->status->name }}
                    </span>
                  @endif
                </button>
              @empty
                <div class="px-3 py-2 text-sm text-zinc-400 dark:text-zinc-500">Tidak ada tugas yang cocok.</div>
              @endforelse
            </div>
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
      <div wire:key="status-{{ $status->id }}"
        class="kanban-col-wrapper group/col flex w-72 shrink-0 flex-col rounded-xl bg-zinc-50 dark:bg-zinc-800/50 border-t-4"
        style="border-top-color: {{ $status->color }}" data-column-id="{{ $status->id }}">
        <div class="flex items-center justify-between px-3 py-3">
          <div class="flex items-center gap-1.5 min-w-0 flex-1">
            {{-- Grip handle for column drag (visible on hover when canManage) --}}
            @if ($canManage)
              <div
                class="kanban-col-handle mr-0.5 flex shrink-0 touch-none cursor-grab items-center opacity-0 transition-opacity group-hover/col:opacity-60 hover:!opacity-100 active:cursor-grabbing">
                <svg class="size-3.5 text-zinc-400" viewBox="0 0 16 16" fill="currentColor">
                  <circle cx="5.5" cy="3" r="1.3" />
                  <circle cx="5.5" cy="8" r="1.3" />
                  <circle cx="5.5" cy="13" r="1.3" />
                  <circle cx="10.5" cy="3" r="1.3" />
                  <circle cx="10.5" cy="8" r="1.3" />
                  <circle cx="10.5" cy="13" r="1.3" />
                </svg>
              </div>
            @endif
            <div class="h-2.5 w-2.5 shrink-0 rounded-full" style="background-color: {{ $status->color }}"></div>

            <span class="truncate text-sm font-semibold text-zinc-700 dark:text-zinc-300">{{ $status->name }}</span>
            <span data-task-count
              class="rounded-md bg-zinc-200 px-1.5 py-0.5 text-xs font-semibold text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">
              {{ $status->tasks->count() }}
            </span>
          </div>

          @if ($canManage)
            <div class="relative flex items-center gap-0.5" x-data="{ open: false }">
              <button wire:click="$dispatch('open-create-task-form', { statusId: {{ $status->id }} })"
                title="Tambah tugas"
                class="rounded-md p-1 text-zinc-400 hover:bg-zinc-200 hover:text-zinc-700 dark:hover:bg-zinc-700 dark:hover:text-zinc-200 transition-colors">
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
                <button
                  @click="open = false; $dispatch('open-column-rename', { id: {{ $status->id }}, name: @js($status->name), color: @js($status->color) }); $flux.modal('rename-column-modal').show()"
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
          data-status-type="{{ $status->type }}" wire:key="col-status-{{ $status->id }}">
          @foreach ($status->tasks as $task)
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
              class="task-card group/card relative cursor-pointer overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm transition-all hover:shadow-md hover:border-zinc-300 {{ $task->can_drag ? 'active:cursor-grabbing active:shadow-lg active:ring-2 active:ring-indigo-400/30' : 'task-locked' }} dark:border-zinc-700 dark:bg-zinc-800 dark:hover:border-zinc-600"
              data-task-id="{{ $task->id }}" data-can-drag="{{ $task->can_drag ? '1' : '0' }}"
              @click="if (!_isDraggingTask && !$event.target.closest('[data-no-drag]')) { $flux.modal('task-detail-board').show(); if ($wire.selectedTaskId !== {{ $task->id }}) { $wire.openTaskDetail({{ $task->id }}); } }">

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
                      <span data-due-badge data-open-class="{{ $ddClass }}"
                        data-open-label="{{ $ddLabel }}" data-closed-label="{{ $dd->isoFormat('D MMM') }}"
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

      </div>
    @endforeach

    @if ($canManage)
      <div class="flex w-72 shrink-0 flex-col">
        @if ($showNewColumnInput)
          <div
            class="rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/50 p-3 dark:border-indigo-500/40 dark:bg-indigo-900/10">
            <form x-data="{ newColor: '{{ $newColumn->color }}' }" x-init="$wire.$watch('newColumn.color', v => newColor = v)"
              @submit.prevent="$wire.newColumn.color = newColor; $wire.addColumn()" class="space-y-3">
              <flux:input wire:model="newColumn.name" placeholder="Nama kolom..." size="sm" autofocus />
              <flux:error name="newColumn.name" class="text-xs" />
              <div class="flex items-center gap-2">
                <span class="text-xs text-zinc-500 dark:text-zinc-400">Warna:</span>
                <div class="flex gap-1">
                  @foreach (['#6b7280', '#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'] as $color)
                    <button type="button" @click="newColor = '{{ $color }}'"
                      class="h-5 w-5 rounded-full border-2 transition-transform hover:scale-110"
                      :class="newColor === '{{ $color }}' ? 'border-zinc-900 dark:border-white scale-110' :
                          'border-transparent'"
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

  <x-task-detail-modal name="task-detail-board" keyPrefix="board-detail" :selectedTaskId="$selectedTaskId" />

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

  <flux:modal name="rename-column-modal" class="w-full max-w-sm">
    <div x-data="{ id: null, name: '', color: '#6b7280', nameError: '', saving: false }"
      @open-column-rename.window="
        id = $event.detail.id;
        name = $event.detail.name;
        color = $event.detail.color;
        nameError = '';
        saving = false;
        $nextTick(() => $refs.renameInput?.focus());
      ">
      <form
        @submit.prevent="
          nameError = name.trim().length === 0 ? 'Nama kolom wajib diisi.' : (name.trim().length > 100 ? 'Maksimal 100 karakter.' : '');
          if (nameError || saving) return;
          saving = true;
          $wire.saveColumnRename(id, name.trim(), color)
            .then(() => $flux.modal('rename-column-modal').close())
            .finally(() => saving = false)
        "
        class="space-y-5">
        <flux:heading size="lg">Ubah Kolom</flux:heading>

        <div>
          <flux:label>Nama Kolom</flux:label>
          <flux:input x-model="name" x-ref="renameInput" placeholder="Nama kolom..."
            @keydown.escape="$flux.modal('rename-column-modal').close()" />
          <p x-show="nameError" x-text="nameError" x-cloak class="mt-1 text-sm text-red-500 dark:text-red-400"></p>
        </div>

        <div>
          <flux:label>Warna</flux:label>
          <div class="mt-2 flex flex-wrap gap-2">
            @foreach (['#6b7280', '#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'] as $col)
              <button type="button" @click="color = '{{ $col }}'" title="{{ $col }}"
                class="h-7 w-7 rounded-full border-[3px] transition-transform hover:scale-110"
                :class="color === '{{ $col }}' ? 'border-zinc-900 scale-110 dark:border-white' : 'border-transparent'"
                style="background-color: {{ $col }}"></button>
            @endforeach
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-1">
          <flux:button variant="ghost" type="button" @click="$flux.modal('rename-column-modal').close()">Batal
          </flux:button>
          <flux:button variant="primary" type="submit" x-bind:disabled="saving">
            <span x-show="!saving">Simpan</span>
            <span x-show="saving" x-cloak class="flex items-center gap-1.5">
              <svg class="size-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                  stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              Menyimpan...
            </span>
          </flux:button>
        </div>
      </form>
    </div>
  </flux:modal>
</div>

@script
  <script>
    // SortableJS has an internal bug where it accesses lastElementChild on a null
    // element during dragover on empty/morphed columns. Our _pendingFullReinit guard
    // prevents the functional bug; this suppressor silences the console noise.
    window.addEventListener('error', (e) => {
      if (
        e.message?.includes('lastElementChild') &&
        e.filename?.includes('sortablejs')
      ) {
        e.preventDefault();
      }
    }, true);

    Alpine.data('kanbanBoard', (canManage = true) => ({
      // Keyed by column DOM element → SortableInstance
      _taskSortables: new Map(),
      _columnSortable: null,
      _hookCleanup: null,
      _taskDebounce: null,
      _colDebounce: null,
      _dragFrame: null,
      _isDraggingTask: false,
      _canManage: canManage,
      // Columns that need reinit after current drag ends (queued from broadcasts)
      _pendingReinit: [],
      // Set when a kanban-col-wrapper morphed during drag — needs full reinit after drag
      _pendingFullReinit: false,

      // Board pan-scroll state
      isDown: false,
      startX: 0,
      scrollLeft: 0,

      // ─── Lifecycle ─────────────────────────────────────────
      init() {
        this.$nextTick(() => this._initAll());

        // Smart reinit: only affect the column(s) that Livewire morphed.
        // Skip reinit if a drag is active — would destroy Sortable mid-drag
        // and cause the card to snap back. Queue it for after drag ends instead.
        this._hookCleanup = Livewire.hook('morph.updated', ({
          el
        }) => {
          if (el?.classList?.contains('kanban-column')) {
            clearTimeout(this._taskDebounce);
            this._taskDebounce = setTimeout(() => {
              if (this._isDraggingTask) {
                this._pendingReinit.push(el);
                return;
              }
              this.$nextTick(() => this._reinitTaskSortable(el));
            }, 30);
          } else if (el?.classList?.contains('kanban-col-wrapper')) {
            clearTimeout(this._colDebounce);
            this._colDebounce = setTimeout(() => {
              if (this._isDraggingTask) {
                // Never destroy Sortable while a drag is active — this.el would
                // become null and the in-flight dragover event would crash.
                // Queue a full reinit to run after onEnd fires.
                this._pendingFullReinit = true;
                return;
              }
              this.$nextTick(() => {
                this._columnSortable?.destroy();
                this._columnSortable = null;
                this._initColumnSortable();
              });
            }, 30);
          }
        });

        // A task created/updated via the form modal re-renders the board; make
        // sure Sortable is (re)attached to any freshly-morphed columns.
        Livewire.on('task-updated', () => {
          this.$nextTick(() => this._initAll());
        });
      },

      destroy() {
        this._taskSortables.forEach(s => s?.destroy());
        this._taskSortables.clear();
        this._columnSortable?.destroy();
        this._columnSortable = null;
        clearTimeout(this._taskDebounce);
        clearTimeout(this._colDebounce);
        if (this._dragFrame) cancelAnimationFrame(this._dragFrame);
        if (typeof this._hookCleanup === 'function') this._hookCleanup();
      },

      // ─── Init ──────────────────────────────────────────────
      _initAll() {
        if (typeof window.Sortable === 'undefined') {
          setTimeout(() => this._initAll(), 50);
          return;
        }
        this._taskSortables.forEach(s => s?.destroy());
        this._taskSortables.clear();
        this._columnSortable?.destroy();
        this._columnSortable = null;

        this.$el.querySelectorAll('.kanban-column').forEach(col => {
          this._createTaskSortable(col);
        });
        this._initColumnSortable();
      },

      // ─── Task sortable ─────────────────────────────────────
      _createTaskSortable(columnEl) {
        // Destroy stale instance for this column if any
        const stale = this._taskSortables.get(columnEl);
        if (stale) {
          stale.destroy();
          this._taskSortables.delete(columnEl);
        }

        const instance = new window.Sortable(columnEl, {
          group: 'kanban-tasks',
          animation: 150,
          easing: 'cubic-bezier(0.2, 0, 0, 1)',
          ghostClass: 'kanban-ghost',
          chosenClass: 'kanban-chosen',
          dragClass: 'kanban-drag',
          draggable: '.task-card',
          filter: '.task-locked',
          preventOnFilter: true,
          emptyInsertThreshold: 8,
          scroll: true,
          scrollSensitivity: 60,
          scrollSpeed: 10,
          bubbleScroll: true,
          swapThreshold: 0.65,
          delay: 150,
          delayOnTouchOnly: true,
          touchStartThreshold: 3,
          forceFallback: false,

          onChoose: (evt) => {
            evt.item.style.willChange = 'transform, box-shadow';
          },
          onStart: (evt) => {
            document.body.classList.add('is-dragging');
            this._isDraggingTask = true;
          },
          onEnd: (evt) => {
            document.body.classList.remove('is-dragging');
            evt.item.style.willChange = '';

            const taskId = parseInt(evt.item.dataset.taskId);
            const newStatusId = parseInt(evt.to.dataset.statusId);
            const destType = evt.to.dataset.statusType ?? '';
            const orderedIds = Array.from(evt.to.querySelectorAll('.task-card'))
              .map(el => parseInt(el.dataset.taskId));

            this._updateColumnCounts(evt.from, evt.to);
            this._updateDueBadge(evt.item, destType);
            this.$wire.moveTask(taskId, newStatusId, orderedIds);

            setTimeout(() => {
              this._isDraggingTask = false;
              if (this._pendingFullReinit) {
                // Column structure changed during drag — do a full board reinit.
                this._pendingFullReinit = false;
                this._pendingReinit = [];
                this.$nextTick(() => this._initAll());
              } else if (this._pendingReinit.length) {
                const pending = this._pendingReinit.splice(0);
                this.$nextTick(() => pending.forEach(el => this._reinitTaskSortable(el)));
              }
            }, 150);
          },
        });

        this._taskSortables.set(columnEl, instance);
        return instance;
      },

      _reinitTaskSortable(columnEl) {
        this._createTaskSortable(columnEl);
      },

      // column sortable 
      _initColumnSortable() {
        if (!this._canManage) return;
        const board = this.$el;
        if (!board) return;

        this._columnSortable = new window.Sortable(board, {
          animation: 220,
          easing: 'cubic-bezier(0.2, 0, 0, 1)',
          draggable: '.kanban-col-wrapper',
          handle: '.kanban-col-handle',
          ghostClass: 'kanban-col-ghost',
          chosenClass: 'kanban-col-chosen',
          dragClass: 'kanban-col-drag',
          direction: 'horizontal',
          delay: 80,
          delayOnTouchOnly: false,
          touchStartThreshold: 8,
          swapThreshold: 0.5,
          scroll: true,
          scrollSensitivity: 120,
          scrollSpeed: 14,
          fallbackOnBody: true,
          forceFallback: false,

          onStart: () => {
            document.body.classList.add('is-dragging-column');
          },
          onEnd: () => {
            document.body.classList.remove('is-dragging-column');
            const ids = Array.from(board.querySelectorAll('.kanban-col-wrapper'))
              .map(el => parseInt(el.dataset.columnId))
              .filter(Number.isFinite);
            if (ids.length) this.$wire.updateColumnOrder(ids);
          },
        });
      },


      _updateDueBadge(cardEl, destStatusType) {
        const badge = cardEl.querySelector('[data-due-badge]');
        if (!badge) return;

        const isClosed = destStatusType === 'closed';
        const closedClass = 'bg-green-100 text-green-700 dark:bg-green-500/15 dark:text-green-400';
        const openClass = badge.dataset.openClass || '';
        const base = 'inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 text-[10px] ';

        badge.setAttribute('class', base + (isClosed ? closedClass : openClass));

        // Also update the label text so "Hari ini"/"Besok" becomes the actual date when closed
        const labelEl = badge.querySelector('[data-due-label]');
        if (labelEl) {
          labelEl.textContent = isClosed ?
            (badge.dataset.closedLabel || labelEl.textContent) :
            (badge.dataset.openLabel || labelEl.textContent);
        }
      },

      _updateColumnCounts(fromCol, toCol) {
        [fromCol, toCol].forEach(col => {
          if (!col) return;
          const badge = col.closest('.kanban-col-wrapper')?.querySelector('[data-task-count]');
          if (badge) badge.textContent = col.querySelectorAll('.task-card').length;
        });
      },

      startDrag(e) {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
        if (document.body.classList.contains('is-dragging-column')) return;
        if (e.target.closest('.task-card') || e.target.closest('button') ||
          e.target.closest('input') || e.target.closest('.kanban-col-handle')) return;
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
        if (e.buttons === 0 ||
          document.body.classList.contains('is-dragging') ||
          document.body.classList.contains('is-dragging-column')) {
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
