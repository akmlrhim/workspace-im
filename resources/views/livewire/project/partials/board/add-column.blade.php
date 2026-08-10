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
