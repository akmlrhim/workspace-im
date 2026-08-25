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
