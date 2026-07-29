<div x-data="{
    deleteModal: false,
    pendingDeleteId: null,
    deletingIds: [],
    addForm: false,
    addTitle: '',
    addDesc: '',
    addType: 'on_demand',
    optimisticTasks: [],
    openAdd() {
        this.addForm = true;
        this.$nextTick(() => this.$refs.addInput?.focus())
    },
    closeAdd() {
        this.addForm = false;
        this.addTitle = '';
        this.addDesc = '';
        this.addType = 'on_demand';
    },
    submitAdd() {
        if (!this.addTitle.trim()) return;
        const title = this.addTitle.trim();
        const desc = this.addDesc.trim();
        const type = this.addType;
        this.optimisticTasks.push({ id: Date.now(), title, desc, type });
        this.closeAdd();
        this.$wire.addDailyTask(title, desc, type).then(() => { this.optimisticTasks = [] });
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
    $myId = auth()->id();
    $currentDayName = $this->selectedCarbon->locale('id')->isoFormat('dddd');

    $allTasks = $this->dailyTasks;
    $totalTasks = $allTasks->count();
    $doneTasks = $this->completedCount;
    $progressPercent = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;

    $routineTasks = $allTasks->filter(fn(\App\Models\Project\DailyTask $dt) => $dt->isRoutine());
    $onDemandTasks = $allTasks->reject(fn(\App\Models\Project\DailyTask $dt) => $dt->isRoutine());

    $sections = [
        ['label' => 'Rutin harian', 'icon' => 'arrow-path', 'tasks' => $routineTasks],
        ['label' => 'Khusus hari ini', 'icon' => 'sparkles', 'tasks' => $onDemandTasks],
    ];

    // Judul bagian hanya berguna kalau kedua tipe sama-sama ada.
    $showSectionHeaders = $routineTasks->isNotEmpty() && $onDemandTasks->isNotEmpty();
  @endphp


  {{-- Task list --}}
  <div wire:loading.class="opacity-60" wire:target="previousDay,nextDay,goToToday">

    @if ($totalTasks === 0 && !$canManage)
      <div
        class="flex flex-col items-center justify-center rounded-xl border border-dashed border-zinc-200 py-16 dark:border-zinc-800">
        <flux:icon name="clipboard-document-list" class="size-10 text-zinc-300 dark:text-zinc-600" />
        <p class="mt-3 text-sm font-medium text-zinc-500 dark:text-zinc-400">Belum ada task untuk hari
          {{ $currentDayName }}
        </p>
      </div>
    @else
      <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800/60">

          @if ($totalTasks === 0)
            <div x-show="optimisticTasks.length === 0 && !addForm"
              class="flex flex-col items-center justify-center px-4 py-12">
              <flux:icon name="clipboard-document-list" class="size-10 text-zinc-300 dark:text-zinc-600" />
              <p class="mt-3 text-sm font-medium text-zinc-500 dark:text-zinc-400">Belum ada task untuk hari
                {{ $currentDayName }}
              </p>
              <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Tambah task baru di bawah</p>
            </div>
          @endif

          @foreach ($sections as $section)
            @continue($section['tasks']->isEmpty())

            @if ($showSectionHeaders)
              <div class="flex items-center gap-2 bg-zinc-50/80 px-4 py-2 dark:bg-zinc-800/40">
                <flux:icon :name="$section['icon']" class="size-3.5 text-zinc-400 dark:text-zinc-500" />
                <span class="text-[11px] font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                  {{ $section['label'] }}
                </span>
                <span class="text-[11px] tabular-nums text-zinc-400 dark:text-zinc-600">
                  {{ $section['tasks']->count() }}
                </span>
              </div>
            @endif

            @foreach ($section['tasks'] as $dt)
              @php
                $taskLog = $dt->logs->firstWhere('user_id', $myId);
                $isDone = (bool) $taskLog?->is_completed;
                $completedLogs = $dt->logs->where('is_completed', true)->values();
                $otherCompletedLogs = $completedLogs->where('user_id', '!=', $myId)->take(4);
              @endphp

              {{--
                Semua tampilan (centang, coret, warna, spinner) digerakkan CSS lewat atribut data-*
                di baris ini, bukan lewat :class dari Alpine. Setiap data-* punya nilai awal dari
                server, jadi saat Livewire mem-morph baris ini nilainya ditimpa dengan yang benar
                — bukan dihapus. Ini yang bikin checkbox & teks tidak bisa lagi beda state.
              --}}
              <div wire:key="task-{{ $dt->id }}-{{ $selectedDate }}-{{ $isDone ? 1 : 0 }}"
                x-show="!deletingIds.includes({{ $dt->id }})" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1"
                x-data="{
                    editing: false,
                    done: @js($isDone),
                    doneAt: @js($taskLog?->completed_at?->format('H:i')),
                    pending: 0,
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
                    },
                    applyDone(isDone, at) {
                        if (isDone === this.done) return;
                        this.done = isDone;
                        this.doneAt = at;
                        this.$dispatch('daily-task-toggled', { delta: isDone ? 1 : -1 });
                    },
                    nowLabel() {
                        return new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    },
                    async toggleDone() {
                        // Sengaja tanpa guard 'sedang jalan': klik cepat berturut-turut
                        // harus tetap tercatat. Livewire menjalankan requestnya berurutan
                        // dan tiap balasan membawa status sebenarnya, jadi yang terakhir menang.
                        const completing = !this.done;
                        const previousDoneAt = this.doneAt;
                        this.applyDone(completing, completing ? this.nowLabel() : null);
                        this.pending++;
                        try {
                            // Server membalas status sebenarnya; UI selalu ikut nilai itu,
                            // jadi tebakan optimistis yang meleset terkoreksi sendiri.
                            const serverDone = await this.$wire.toggleComplete({{ $dt->id }});
                            if (serverDone === null || serverDone === undefined) {
                                this.applyDone(!completing, previousDoneAt);
                            } else {
                                this.applyDone(serverDone, serverDone ? (this.doneAt ?? this.nowLabel()) : null);
                            }
                        } catch (e) {
                            this.applyDone(!completing, previousDoneAt);
                        } finally {
                            this.pending--;
                        }
                    }
                }"
                {{-- Selalu string: x-bind menghapus atribut kalau nilainya boolean false. --}}
                data-done="{{ $isDone ? 'true' : 'false' }}" data-busy="false" data-editing="false"
                :data-done="done ? 'true' : 'false'" :data-busy="pending > 0 ? 'true' : 'false'"
                :data-editing="editing ? 'true' : 'false'"
                class="group/task flex items-start gap-3 px-4 py-3.5 transition-colors data-[done=true]:bg-emerald-50/50 dark:data-[done=true]:bg-emerald-400/5">

                {{-- Checkbox --}}
                @if ($canManage)
                  <button type="button" role="checkbox" :aria-checked="done" @click="toggleDone()"
                    :title="done ? 'Tandai belum selesai' : 'Tandai selesai'"
                    class="relative mt-0.5 grid h-[22px] w-[22px] shrink-0 cursor-pointer place-items-center rounded-md border-2 border-zinc-300 transition-all duration-150 before:absolute before:-inset-2 before:content-[''] hover:border-emerald-400 active:scale-90 group-data-[done=true]/task:border-emerald-500 group-data-[done=true]/task:bg-emerald-500 dark:border-zinc-600 dark:hover:border-emerald-500 dark:group-data-[done=true]/task:border-emerald-500">
                    <svg
                      class="h-3.5 w-3.5 scale-50 text-white opacity-0 transition-all duration-200 group-data-[done=true]/task:scale-100 group-data-[done=true]/task:opacity-100 group-data-[busy=true]/task:opacity-0"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M5 13l4 4L19 7" />
                    </svg>
                    <svg
                      class="absolute h-3.5 w-3.5 animate-spin text-zinc-400 opacity-0 transition-opacity group-data-[busy=true]/task:opacity-100 group-data-[done=true]/task:text-white dark:text-zinc-500"
                      fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                    </svg>
                  </button>
                @else
                  <div
                    class="mt-0.5 grid h-[22px] w-[22px] shrink-0 place-items-center rounded-md border-2 border-zinc-300 group-data-[done=true]/task:border-emerald-500 group-data-[done=true]/task:bg-emerald-500 dark:border-zinc-600 dark:group-data-[done=true]/task:border-emerald-500">
                    <svg
                      class="h-3.5 w-3.5 scale-50 text-white opacity-0 transition-all duration-200 group-data-[done=true]/task:scale-100 group-data-[done=true]/task:opacity-100"
                      fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5"
                      stroke-linecap="round" stroke-linejoin="round">
                      <path d="M5 13l4 4L19 7" />
                    </svg>
                  </div>
                @endif

                {{-- Task content --}}
                <div class="min-w-0 flex-1">
                  <div class="group-data-[editing=true]/task:hidden">
                    <p
                      class="text-sm font-medium leading-snug text-zinc-800 transition-colors group-data-[done=true]/task:text-zinc-400 group-data-[done=true]/task:line-through dark:text-zinc-100 dark:group-data-[done=true]/task:text-zinc-500">
                      {{ $dt->title }}
                    </p>

                    @if ($dt->description)
                      <p class="mt-0.5 text-xs text-zinc-400 dark:text-zinc-500">{{ $dt->description }}</p>
                    @endif

                    {{-- Avatar sendiri di-toggle CSS agar terasa instan, avatar orang lain dari server --}}
                    <div
                      class="mt-1.5 items-center gap-1.5 {{ $otherCompletedLogs->isNotEmpty() ? 'flex' : 'hidden group-data-[done=true]/task:flex' }}">
                      <div class="flex -space-x-1.5">
                        <span class="hidden group-data-[done=true]/task:inline-flex">
                          <flux:avatar circle :name="auth()->user()->name" :src="auth()->user()->avatar" size="xs"
                            class="ring-1 ring-white dark:ring-zinc-900" />
                        </span>
                        @foreach ($otherCompletedLogs as $log)
                          <flux:avatar circle :name="$log->user?->name ?? '?'" :src="$log->user?->avatar ?? null"
                            size="xs" class="ring-1 ring-white dark:ring-zinc-900" />
                        @endforeach
                      </div>
                    </div>

                    @if ($taskLog?->reason)
                      <div
                        class="mt-1.5 inline-flex items-start gap-1 rounded-md bg-amber-50 px-2 py-1 group-data-[done=true]/task:hidden dark:bg-amber-900/20">
                        <flux:icon.chat-bubble-left-ellipsis class="mt-0.5 h-3 w-3 shrink-0 text-amber-500" />
                        <span
                          class="text-xs leading-relaxed text-amber-700 dark:text-amber-400">{{ $taskLog->reason }}</span>
                      </div>
                    @endif

                    <div class="mt-1 hidden items-center gap-1 group-data-[done=true]/task:flex">
                      <flux:icon name="clock" class="size-3 text-emerald-500" />
                      <span class="text-xs text-emerald-600 dark:text-emerald-400"
                        x-text="doneAt ? `Selesai ${doneAt}` : ''">{{ $taskLog?->completed_at ? 'Selesai ' . $taskLog->completed_at->format('H:i') : '' }}</span>
                    </div>
                  </div>

                  @if ($canManage)
                    <div class="hidden space-y-2 group-data-[editing=true]/task:block">
                      <input x-ref="editInput" x-model="t" @keydown.enter.prevent="submitEdit()"
                        @keydown.escape="cancelEdit()"
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                      <input x-model="d" @keydown.escape="cancelEdit()" placeholder="Deskripsi (opsional)..."
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                      <div class="flex gap-2">
                        <flux:button @click="submitEdit()" variant="primary" size="sm" wire:loading.attr="disabled"
                          wire:loading.class="opacity-75" wire:target="saveEdit">
                          Simpan
                        </flux:button>
                        <flux:button @click="cancelEdit()" variant="ghost" size="sm">Batal</flux:button>
                      </div>
                    </div>
                  @endif
                </div>

                @if ($canManage)
                  <div
                    class="flex shrink-0 items-center gap-0.5 opacity-0 transition-opacity focus-within:opacity-100 group-hover/task:opacity-100 group-data-[editing=true]/task:hidden max-sm:opacity-100">
                    <div class="group-data-[done=true]/task:hidden">
                      <flux:tooltip content="Catat alasan" position="top">
                        <flux:button variant="ghost" size="sm" icon="chat-bubble-left-ellipsis"
                          class="h-7 w-7 p-0 text-zinc-400 hover:text-amber-500"
                          wire:click="openReasonModal({{ $dt->id }})" wire:loading.attr="disabled"
                          wire:target="openReasonModal({{ $dt->id }})" />
                      </flux:tooltip>
                    </div>
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
          @endforeach

          {{-- Optimistic rows --}}
          <template x-for="ot in optimisticTasks" :key="ot.id">
            <div class="flex items-start gap-3 px-4 py-3">
              <div class="mt-0.5 h-[22px] w-[22px] shrink-0 rounded-md border-2 border-zinc-200 dark:border-zinc-700">
              </div>
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

          {{-- Add row --}}
          @if ($canManage)
            <div x-show="addForm" x-cloak class="flex items-start gap-3 px-4 py-3">
              <div class="mt-0.5 h-[22px] w-[22px] shrink-0 rounded-md border-2 border-zinc-200 dark:border-zinc-700">
              </div>
              <div class="min-w-0 flex-1 space-y-2">
                <input x-ref="addInput" x-model="addTitle" @keydown.enter.prevent="submitAdd()"
                  @keydown.escape="closeAdd()" placeholder="Nama task..."
                  class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                <input x-model="addDesc" @keydown.escape="closeAdd()" placeholder="Deskripsi (opsional)..."
                  class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:outline-none focus:inset-ring-1 focus:inset-ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
                <div class="flex items-center gap-1.5">
                  <span class="text-xs text-zinc-500 dark:text-zinc-400">Tipe:</span>
                  <div
                    class="flex divide-x divide-zinc-200 overflow-hidden rounded-lg border border-zinc-200 dark:divide-zinc-700 dark:border-zinc-700">
                    <button type="button" @click="addType = 'on_demand'"
                      :class="addType === 'on_demand' ? 'bg-indigo-600 text-white' :
                          'bg-white text-zinc-600 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'"
                      class="px-3 py-1 text-xs font-medium transition-colors">
                      Hari ini
                    </button>
                    <button type="button" @click="addType = 'routine'"
                      :class="addType === 'routine' ? 'bg-violet-600 text-white' :
                          'bg-white text-zinc-600 hover:bg-zinc-50 dark:bg-zinc-900 dark:text-zinc-300 dark:hover:bg-zinc-800'"
                      class="px-3 py-1 text-xs font-medium transition-colors">
                      Setiap hari
                    </button>
                  </div>
                </div>
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
              class="group/add flex w-full items-center gap-2 px-4 py-3 text-xs font-medium text-zinc-400 transition-colors hover:bg-zinc-50 hover:text-indigo-500 dark:hover:bg-zinc-800/60 dark:hover:text-indigo-400">
              <span
                class="grid h-[22px] w-[22px] shrink-0 place-items-center rounded-md border-2 border-dashed border-zinc-300 transition-colors group-hover/add:border-indigo-400 dark:border-zinc-700">
                <flux:icon name="plus" class="size-3" />
              </span>
              Tambah task
            </button>
          @endif

        </div>
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
