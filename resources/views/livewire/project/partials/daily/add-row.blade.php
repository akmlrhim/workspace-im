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
