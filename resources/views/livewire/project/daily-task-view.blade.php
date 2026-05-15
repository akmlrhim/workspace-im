<div x-data="{
    deleteModal: false,
    pendingDeleteId: null,
    deletingIds: [],
    addForm: false,
    addTitle: '',
    addDesc: '',
    optimisticTasks: [],
    openAdd() {
        this.addForm = true;
        this.$nextTick(() => this.$refs.addInput?.focus())
    },
    closeAdd() {
        this.addForm = false;
        this.addTitle = '';
        this.addDesc = ''
    },
    submitAdd() {
        if (!this.addTitle.trim()) return;
        const title = this.addTitle.trim();
        const desc = this.addDesc.trim();
        this.optimisticTasks.push({ id: Date.now(), title, desc });
        this.closeAdd();
        this.$wire.addDailyTask(title, desc).then(() => { this.optimisticTasks = [] });
    }
}">
  {{-- Header --}}
  <div class="mb-6">

    @include('livewire.project.partials.breadcrumb')

    <div class="mt-3 flex items-center justify-between gap-3 mb-4">
      <h1 class="hidden lg:block lg:text-2xl font-bold text-zinc-900 dark:text-white">{{ $taskList->name }}</h1>
    </div>

    @include('livewire.project.partials.view-toggle', ['active' => 'daily'])

    @php
      $carbon = $this->selectedCarbon;
      $isToday = $this->isToday;
      $isYesterday = $carbon->isYesterday();
      $dateLabel = $isToday ? 'Hari ini' : ($isYesterday ? 'Kemarin' : $carbon->locale('id')->isoFormat('D MMM YYYY'));
    @endphp

    <div class="relative mt-3 flex items-center gap-2" x-data="{
        calOpen: false,
        viewMonth: 0,
        viewYear: 0,
        todayStr: '{{ today()->toDateString() }}',
        months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        days: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
    
        openCal() {
            const d = new Date(this.$wire.selectedDate + 'T00:00:00');
            this.viewMonth = d.getMonth();
            this.viewYear = d.getFullYear();
            this.calOpen = true;
        },
    
        prevViewMonth() {
            if (this.viewMonth === 0) {
                this.viewMonth = 11;
                this.viewYear--;
            } else this.viewMonth--;
        },
    
        nextViewMonth() {
            const now = new Date();
            if (this.viewYear === now.getFullYear() && this.viewMonth === now.getMonth()) return;
            if (this.viewMonth === 11) {
                this.viewMonth = 0;
                this.viewYear++;
            } else this.viewMonth++;
        },
    
        isNextMonthDisabled() {
            const now = new Date();
            return this.viewYear === now.getFullYear() && this.viewMonth === now.getMonth();
        },
    
        get gridDays() {
            const firstDay = new Date(this.viewYear, this.viewMonth, 1);
            const lastDay = new Date(this.viewYear, this.viewMonth + 1, 0);
            const grid = [];
            let startDow = firstDay.getDay();
            startDow = startDow === 0 ? 6 : startDow - 1;
            for (let i = 0; i < startDow; i++) grid.push('');
            for (let d = 1; d <= lastDay.getDate(); d++) {
                grid.push(
                    this.viewYear + '-' +
                    String(this.viewMonth + 1).padStart(2, '0') + '-' +
                    String(d).padStart(2, '0')
                );
            }
            return grid;
        },
    
        get monthLabel() { return this.months[this.viewMonth] + ' ' + this.viewYear; },
    
        selectDate(ds) {
            if (!ds || ds > this.todayStr) return;
            this.$wire.set('selectedDate', ds);
            this.calOpen = false;
        },
    
        isSelected(ds) { return ds === this.$wire.selectedDate; },
        isFuture(ds) { return ds > this.todayStr; },
        isToday(ds) { return ds === this.todayStr; }
    }" @keydown.escape.window="calOpen = false">
      <div
        class="flex items-center divide-x divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-sm dark:divide-zinc-700 dark:border-zinc-700 dark:bg-zinc-900">
        <button wire:click="previousDay" wire:loading.attr="disabled" wire:target="previousDay,nextDay,goToToday"
          class="flex h-8 w-8 items-center justify-center text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-800 disabled:opacity-40 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
          <flux:icon name="chevron-left" class="size-4" />
        </button>

        <button @click="calOpen ? calOpen = false : openCal()"
          class="flex h-8 items-center gap-1.5 px-3 text-sm font-medium text-zinc-700 transition-colors hover:bg-zinc-50 dark:text-zinc-200 dark:hover:bg-zinc-800">
          <flux:icon name="calendar-days" class="size-3.5 shrink-0 text-zinc-400" />
          <span class="whitespace-nowrap">{{ $dateLabel }}</span>
          @unless ($isToday)
            <span class="text-xs text-zinc-400 dark:text-zinc-500">· {{ $carbon->locale('id')->isoFormat('dddd') }}</span>
          @endunless
          <span class="inline-flex transition-transform duration-150" :class="calOpen ? 'rotate-180' : ''">
            <flux:icon name="chevron-down" class="size-3 text-zinc-400" />
          </span>
        </button>

        <button wire:click="nextDay" wire:loading.attr="disabled" wire:target="previousDay,nextDay,goToToday"
          @disabled($isToday)
          class="flex h-8 w-8 items-center justify-center text-zinc-500 transition-colors hover:bg-zinc-50 hover:text-zinc-800 disabled:cursor-not-allowed disabled:opacity-30 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
          <flux:icon name="chevron-right" class="size-4" />
        </button>
      </div>

      @unless ($isToday)
        <button wire:click="goToToday" wire:loading.attr="disabled" wire:target="previousDay,nextDay,goToToday"
          class="flex h-8 items-center gap-1.5 rounded-lg bg-zinc-600 px-3 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700 active:bg-indigo-800 dark:bg-indigo-500 dark:hover:bg-indigo-600">
          Hari ini
        </button>
      @endunless

      <div x-show="calOpen" x-cloak @click.outside="calOpen = false"
        x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute left-0 top-10 z-50 w-72 rounded-xl border border-zinc-200 bg-white p-4 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">

        <div class="mb-3 flex items-center justify-between">
          <button @click="prevViewMonth()"
            class="flex h-7 w-7 items-center justify-center rounded-lg text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
            <flux:icon name="chevron-left" class="size-4" />
          </button>
          <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-100" x-text="monthLabel"></span>
          <button @click="nextViewMonth()" :disabled="isNextMonthDisabled()"
            class="flex h-7 w-7 items-center justify-center rounded-lg text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 disabled:cursor-not-allowed disabled:opacity-30 dark:hover:bg-zinc-800 dark:hover:text-zinc-200">
            <flux:icon name="chevron-right" class="size-4" />
          </button>
        </div>

        <div class="mb-1 grid grid-cols-7">
          <template x-for="day in days" :key="day">
            <div class="flex justify-center py-1 text-xs font-medium text-zinc-400 dark:text-zinc-500" x-text="day">
            </div>
          </template>
        </div>

        <div class="grid grid-cols-7 gap-y-0.5">
          <template x-for="(ds, i) in gridDays" :key="i">
            <div class="flex justify-center">
              <button x-show="ds !== ''" @click="selectDate(ds)" :disabled="isFuture(ds)"
                :class="{
                    'bg-indigo-600 text-white hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600': isSelected(
                        ds),
                    'bg-indigo-50 text-indigo-700 font-semibold ring-1 ring-inset ring-indigo-300 dark:bg-indigo-900/40 dark:text-indigo-300 dark:ring-indigo-600': isToday(
                        ds) && !isSelected(ds),
                    'text-zinc-300 cursor-not-allowed dark:text-zinc-600': isFuture(ds),
                    'text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800': !isSelected(ds) && !
                        isFuture(ds) && !isToday(ds)
                }"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-sm transition-colors">
                <span x-text="ds ? parseInt(ds.split('-')[2]) : ''"></span>
              </button>
              <div x-show="ds === ''" class="h-8 w-8"></div>
            </div>
          </template>
        </div>

        <div class="mt-3 border-t border-zinc-100 pt-3 dark:border-zinc-800">
          <button @click="selectDate(todayStr)"
            class="w-full rounded-lg py-1.5 text-center text-xs font-semibold text-indigo-600 transition-colors hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-900/20">
            Lompat ke Hari Ini
          </button>
        </div>
      </div>
    </div>
  </div>

  @php
    $total = $this->dailyTasks->count();
    $completedCount = $this->completedCount;
    $percentage = $total > 0 ? round(($completedCount / $total) * 100) : 0;
    $myId = auth()->id();
    $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $currentDayName = $dayNames[$this->selectedDayOfWeek] ?? '';
  @endphp

  {{-- Day context header --}}
  <div class="mb-4 flex items-center gap-2">
    <span
      class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-300 dark:ring-indigo-700">
      <flux:icon name="calendar-days" class="size-3.5" />
      Task hari {{ $currentDayName }}
    </span>
    @if ($total === 0)
      <span class="text-xs text-zinc-400 dark:text-zinc-500">Belum ada task untuk hari ini</span>
    @endif
  </div>

  {{-- Progress bar (overall task completion by each creator) --}}
  @if ($total > 0)
    <div class="mb-5">
      <div class="mb-1.5 flex items-center justify-between">
        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $completedCount }} / {{ $total }} task
          selesai</span>
        <span
          class="text-xs font-semibold {{ $percentage === 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500 dark:text-zinc-400' }}">
          {{ $percentage }}%
        </span>
      </div>
      <div class="h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
        <div
          class="h-full rounded-full transition-all duration-500 {{ $percentage === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}"
          style="width: {{ $percentage }}%"></div>
      </div>
    </div>
  @endif

  {{-- Task list grouped by creator --}}
  @php $myGroupRendered = false; @endphp

  <div class="space-y-5" wire:loading.class="opacity-60"
    wire:target="previousDay,nextDay,goToToday,toggleComplete,submitReason">

    @forelse ($tasksByCreator as $creatorId => $tasks)
      @php
        $creator = $tasks->first()->creator;
        $isMyGroup = $creatorId === $myId;
        if ($isMyGroup) {
            $myGroupRendered = true;
        }
        $groupDone = $tasks->filter(fn($dt) => $dt->logs->firstWhere('user_id', $creatorId)?->is_completed)->count();
      @endphp

      <div>
        {{-- Group header --}}
        <div class="mb-2 flex items-center gap-2">
          <flux:avatar circle :name="$creator?->name ?? 'Dihapus'" :src="$creator?->avatar ?? null" size="xs" />
          <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">
            {{ $isMyGroup ? 'Task saya' : $creator?->name ?? 'Pengguna dihapus' }}
          </span>
          <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $tasks->count() }} task</span>
          @if ($groupDone > 0)
            <span class="inline-flex items-center gap-0.5 text-xs font-medium text-emerald-600 dark:text-emerald-400">
              <flux:icon name="check-circle" class="size-3" />
              {{ $groupDone }} selesai
            </span>
          @endif
        </div>

        {{-- Task rows --}}
        <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white dark:border-zinc-800 dark:bg-zinc-900">
          <div class="divide-y divide-zinc-50 dark:divide-zinc-800/50">

            @foreach ($tasks as $dt)
              @php
                $taskLog = $dt->logs->firstWhere('user_id', $creatorId);
                $isDone = (bool) $taskLog?->is_completed;
              @endphp

              <div wire:key="task-{{ $dt->id }}-{{ $selectedDate }}"
                x-show="!deletingIds.includes({{ $dt->id }})"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0 -translate-y-1" x-data="{
                    editing: false,
                    t: @js($dt->title),
                    d: @js($dt->description ?? ''),
                    openEdit() {
                        this.editing = true;
                        this.$nextTick(() => this.$refs.editInput?.focus())
                    },
                    cancelEdit() {
                        this.editing = false;
                        this.t = @js($dt->title);
                        this.d = @js($dt->description ?? '')
                    },
                    submitEdit() {
                        if (!this.t.trim()) return;
                        this.$wire.saveEdit({{ $dt->id }}, this.t.trim(), this.d.trim());
                        this.editing = false;
                    }
                }"
                class="flex items-start gap-3 px-4 py-3 transition-colors {{ $isDone ? 'bg-zinc-50/80 dark:bg-zinc-900/60' : '' }}">

                {{-- Status indicator / interactive checkbox --}}
                @if ($isMyGroup && $canManage)
                  {{-- Interactive checkbox — only for the creator --}}
                  <button type="button" @click="$wire.toggleComplete({{ $dt->id }})"
                    wire:loading.attr="disabled" wire:target="toggleComplete({{ $dt->id }})"
                    title="{{ $isDone ? 'Tandai belum selesai' : 'Tandai selesai' }}"
                    class="mt-0.5 shrink-0 flex h-5 w-5 items-center justify-center rounded transition-all duration-150
                      {{ $isDone
                          ? 'bg-emerald-500 text-white hover:bg-emerald-600'
                          : 'border-2 border-zinc-300 hover:border-emerald-400 dark:border-zinc-600 dark:hover:border-emerald-500' }}">
                    @if ($isDone)
                      <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                    @endif
                  </button>
                @else
                  {{-- Read-only status dot — for viewers of others' tasks --}}
                  <div
                    class="mt-0.5 shrink-0 flex h-5 w-5 items-center justify-center rounded
                    {{ $isDone ? 'bg-emerald-500' : 'border-2 border-zinc-200 dark:border-zinc-700' }}"
                    title="{{ $isDone ? 'Selesai' : 'Belum selesai' }}">
                    @if ($isDone)
                      <svg class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                      </svg>
                    @endif
                  </div>
                @endif

                {{-- Task content --}}
                <div class="min-w-0 flex-1">
                  <div x-show="!editing">
                    <p
                      class="text-sm font-medium leading-snug
                      {{ $isDone ? 'text-zinc-400 line-through dark:text-zinc-500' : 'text-zinc-800 dark:text-zinc-100' }}">
                      {{ $dt->title }}
                    </p>

                    @if ($dt->description)
                      <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">{{ $dt->description }}</p>
                    @endif

                    @if ($taskLog?->reason)
                      <div
                        class="mt-1.5 inline-flex items-start gap-1 rounded-md bg-amber-50 px-2 py-1 dark:bg-amber-900/20">
                        <flux:icon.chat-bubble-left-ellipsis class="mt-0.5 h-3 w-3 shrink-0 text-amber-500" />
                        <span
                          class="text-xs leading-relaxed text-amber-700 dark:text-amber-400">{{ $taskLog->reason }}</span>
                      </div>
                    @endif

                    @if ($isDone && $taskLog?->completed_at)
                      <div class="mt-1 flex items-center gap-1">
                        <flux:icon name="clock" class="size-3 text-emerald-400" />
                        <span class="text-xs text-emerald-500 dark:text-emerald-400">
                          Selesai {{ $taskLog->completed_at->locale('id')->isoFormat('HH:mm') }}
                        </span>
                      </div>
                    @endif
                  </div>

                  {{-- Edit form (creator only) --}}
                  @if ($isMyGroup)
                    <div x-show="editing" x-cloak class="space-y-2">
                      <input x-ref="editInput" x-model="t" @keydown.enter.prevent="submitEdit()"
                        @keydown.escape="cancelEdit()"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                      <input x-model="d" @keydown.escape="cancelEdit()" placeholder="Deskripsi (opsional)..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                      <div class="flex gap-2">
                        <flux:button @click="submitEdit()" variant="primary" size="sm"
                          wire:loading.attr="disabled" wire:loading.class="opacity-75" wire:target="saveEdit">
                          Simpan
                        </flux:button>
                        <flux:button @click="cancelEdit()" variant="ghost" size="sm">Batal</flux:button>
                      </div>
                    </div>
                  @endif
                </div>

                {{-- Actions — only for the task creator --}}
                @if ($isMyGroup && $canManage)
                  <div x-show="!editing" class="shrink-0 flex items-center gap-0.5">
                    @if (!$isDone)
                      <flux:tooltip content="Catat alasan" position="top">
                        <flux:button variant="ghost" size="sm" icon="chat-bubble-left-ellipsis"
                          class="h-7 w-7 p-0 text-zinc-400 hover:text-amber-500"
                          wire:click="openReasonModal({{ $dt->id }})" wire:loading.attr="disabled"
                          wire:target="openReasonModal({{ $dt->id }})" />
                      </flux:tooltip>
                    @endif
                    <flux:dropdown position="bottom" align="end">
                      <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                        class="h-7 w-7 p-0 text-zinc-400" />
                      <flux:menu>
                        <flux:menu.item icon="pencil-square" @click="openEdit()">Edit</flux:menu.item>
                        <flux:menu.separator />
                        <flux:menu.item variant="danger" icon="trash"
                          @click="pendingDeleteId = {{ $dt->id }}; deleteModal = true">
                          Hapus
                        </flux:menu.item>
                      </flux:menu>
                    </flux:dropdown>
                  </div>
                @endif

              </div>
            @endforeach

            {{-- Optimistic rows (inside my group) --}}
            @if ($isMyGroup)
              <template x-for="ot in optimisticTasks" :key="ot.id">
                <div class="flex items-start gap-3 px-4 py-3">
                  <div class="mt-0.5 shrink-0 h-5 w-5 rounded border-2 border-zinc-200 dark:border-zinc-700"></div>
                  <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100" x-text="ot.title"></p>
                    <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500" x-show="ot.desc" x-text="ot.desc"></p>
                    <div class="mt-1.5 flex items-center gap-1">
                      <svg class="h-3 w-3 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                          stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                      </svg>
                      <span class="text-xs text-zinc-400">Menyimpan...</span>
                    </div>
                  </div>
                </div>
              </template>
            @endif

            {{-- Add row (inside my group) --}}
            @if ($isMyGroup && $canManage)
              <div>
                <div x-show="addForm" x-cloak class="flex items-start gap-3 px-4 py-3">
                  <div class="mt-0.5 shrink-0 h-5 w-5 rounded border-2 border-zinc-200 dark:border-zinc-700"></div>
                  <div class="min-w-0 flex-1 space-y-2">
                    <input x-ref="addInput" x-model="addTitle" @keydown.enter.prevent="submitAdd()"
                      @keydown.escape="closeAdd()" placeholder="Nama daily task..."
                      class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                    <input x-model="addDesc" @keydown.escape="closeAdd()" placeholder="Deskripsi (opsional)..."
                      class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                    <div class="flex gap-2">
                      <flux:button @click="submitAdd()" variant="primary" size="sm"
                        wire:loading.attr="disabled" wire:loading.class="opacity-75" wire:target="addDailyTask">
                        Simpan
                      </flux:button>
                      <flux:button @click="closeAdd()" variant="ghost" size="sm">Batal</flux:button>
                    </div>
                  </div>
                </div>
                <button x-show="!addForm" @click="openAdd()"
                  class="flex w-full items-center gap-2 px-4 py-2.5 text-xs font-medium text-zinc-400 transition-colors hover:bg-zinc-50 hover:text-indigo-500 dark:hover:bg-zinc-800/60 dark:hover:text-indigo-400">
                  <flux:icon name="plus" class="size-3.5" />
                  Tambah task
                </button>
              </div>
            @endif

          </div>
        </div>
      </div>
    @empty
      <div
        class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-200 py-16 dark:border-zinc-700">
        <flux:icon name="clipboard-document-list" class="size-10 text-zinc-300 dark:text-zinc-600" />
        <p class="mt-3 text-sm font-medium text-zinc-500 dark:text-zinc-400">Belum ada task untuk hari
          {{ $currentDayName }}</p>
        @if ($canManage)
          <button @click="openAdd()"
            class="mt-4 flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600">
            <flux:icon name="plus" class="size-4" />
            Tambah task pertama
          </button>
        @endif
      </div>
    @endforelse

    {{-- Add section for users who haven't created any task yet --}}
    @if ($canManage && !$myGroupRendered)
      <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white dark:border-zinc-800 dark:bg-zinc-900">

        {{-- Optimistic rows --}}
        <template x-for="ot in optimisticTasks" :key="ot.id">
          <div class="flex items-start gap-3 border-b border-zinc-50 px-4 py-3 dark:border-zinc-800/50">
            <div class="mt-0.5 shrink-0 h-5 w-5 rounded border-2 border-zinc-200 dark:border-zinc-700"></div>
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-zinc-800 dark:text-zinc-100" x-text="ot.title"></p>
              <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500" x-show="ot.desc" x-text="ot.desc"></p>
              <div class="mt-1.5 flex items-center gap-1">
                <svg class="h-3 w-3 animate-spin text-indigo-400" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                    stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-xs text-zinc-400">Menyimpan...</span>
              </div>
            </div>
          </div>
        </template>

        <div x-show="addForm" x-cloak class="flex items-start gap-3 px-4 py-3">
          <div class="mt-0.5 shrink-0 h-5 w-5 rounded border-2 border-zinc-200 dark:border-zinc-700"></div>
          <div class="min-w-0 flex-1 space-y-2">
            <input x-ref="addInput" x-model="addTitle" @keydown.enter.prevent="submitAdd()"
              @keydown.escape="closeAdd()" placeholder="Nama daily task..."
              class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
            <input x-model="addDesc" @keydown.escape="closeAdd()" placeholder="Deskripsi (opsional)..."
              class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
            <div class="flex gap-2">
              <flux:button @click="submitAdd()" variant="primary" size="sm" wire:loading.attr="disabled"
                wire:loading.class="opacity-75" wire:target="addDailyTask">
                Simpan
              </flux:button>
              <flux:button @click="closeAdd()" variant="ghost" size="sm">Batal</flux:button>
            </div>
          </div>
        </div>

        <button x-show="!addForm" @click="openAdd()"
          class="flex w-full items-center gap-2 px-4 py-3 text-sm font-medium text-zinc-400 transition-colors hover:bg-zinc-50 hover:text-indigo-500 dark:hover:bg-zinc-800/60 dark:hover:text-indigo-400">
          <flux:icon name="plus-circle" class="size-4" />
          Tambah task saya
        </button>
      </div>
    @endif

  </div>

  {{-- Delete modal --}}
  <div x-show="deleteModal" x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" @click.self="deleteModal = false" @keydown.escape.window="deleteModal = false"
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none">
    <div x-show="deleteModal" x-transition:enter="transition ease-out duration-150"
      x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
      x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 scale-100"
      x-transition:leave-end="opacity-0 scale-95"
      class="w-full max-w-sm rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
      <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Hapus Daily Task?</h3>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Tindakan ini tidak bisa dibatalkan.</p>
      <div class="mt-5 flex justify-end gap-2">
        <flux:button @click="deleteModal = false" variant="ghost" size="sm">Batal</flux:button>
        <flux:button
          @click="deletingIds.push(pendingDeleteId); deleteModal = false; $wire.deleteDailyTask(pendingDeleteId)"
          variant="danger" size="sm" wire:loading.attr="disabled" wire:loading.class="opacity-75"
          wire:target="deleteDailyTask">
          Hapus
        </flux:button>
      </div>
    </div>
  </div>

  @if ($reasonModalFor !== null)
    <div class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-0 sm:items-center sm:p-4">
      <div
        class="w-full rounded-t-2xl border border-zinc-200 bg-white p-6 shadow-xl sm:max-w-md sm:rounded-xl dark:border-zinc-700 dark:bg-zinc-900">
        <h3 class="mb-1 text-base font-semibold text-zinc-900 dark:text-zinc-100">Kenapa belum selesai?</h3>
        <p class="mb-4 text-sm text-zinc-500 dark:text-zinc-400">
          Jelaskan alasan task ini belum bisa diselesaikan hari ini.
        </p>

        <flux:textarea wire:model="reasonInputs.{{ $reasonModalFor }}" placeholder="Tulis alasanmu di sini..."
          rows="3" wire:keydown.ctrl.enter="submitReason" />

        @error("reasonInputs.{$reasonModalFor}")
          <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror

        <div class="mt-4 flex gap-2">
          <flux:button wire:click="$set('reasonModalFor', null)" variant="ghost" size="sm" class="flex-1"
            wire:loading.attr="disabled" wire:target="submitReason">
            Batal
          </flux:button>
          <flux:button wire:click="submitReason" variant="primary" size="sm" class="flex-1"
            wire:loading.attr="disabled" wire:loading.class="opacity-75" wire:target="submitReason">
            Simpan Alasan
          </flux:button>
        </div>
      </div>
    </div>
  @endif
</div>
