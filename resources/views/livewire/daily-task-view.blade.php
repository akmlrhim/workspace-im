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
    <div class="mb-6">

        @include('livewire.partials.breadcrumb')

        <div class="mt-3 flex items-center justify-between gap-3 mb-4">
            <h1 class="hidden lg:block lg:text-2xl font-bold text-zinc-900 dark:text-white">{{ $taskList->name }}</h1>
        </div>

        @include('livewire.partials.view-toggle', ['active' => 'daily'])

        @include('livewire.partials.daily.date-navigator')
    </div>

    @php
    $myId = auth()->id();
    $currentDayName = $this->selectedCarbon->locale('id')->isoFormat('dddd');

    $allTasks = $this->dailyTasks;
    $totalTasks = $allTasks->count();
    $doneTasks = $this->completedCount;
    $progressPercent = $totalTasks > 0 ? (int) round(($doneTasks / $totalTasks) * 100) : 0;

    $routineTasks = $allTasks->filter(fn(\App\Models\DailyTask $dt) => $dt->isRoutine());
    $onDemandTasks = $allTasks->reject(fn(\App\Models\DailyTask $dt) => $dt->isRoutine());

    $sections = [
    ['label' => 'Rutin harian', 'icon' => 'arrow-path', 'tasks' => $routineTasks],
    ['label' => 'Khusus hari ini', 'icon' => 'sparkles', 'tasks' => $onDemandTasks],
    ];

    $showSectionHeaders = $routineTasks->isNotEmpty() && $onDemandTasks->isNotEmpty();
    @endphp

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
                @include('livewire.partials.daily.task-row')
                @endforeach
                @endforeach

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

                @if ($canManage)
                @include('livewire.partials.daily.add-row')
                @endif

            </div>
        </div>
        @endif

    </div>

    @include('livewire.partials.daily.modals')
</div>
