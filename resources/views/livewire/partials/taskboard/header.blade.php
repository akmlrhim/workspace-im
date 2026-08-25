<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <flux:heading size="xl">General Taskboard</flux:heading>
    <flux:subheading>{{ $totalLists }} list tersedia di seluruh space</flux:subheading>
  </div>

  <div class="flex flex-wrap items-center gap-2">
    <div class="relative w-full sm:w-64" x-data="{ showGlobalSuggestions: false }"
      @click.outside="showGlobalSuggestions = false" @keydown.escape="showGlobalSuggestions = false">
      <flux:input icon="magnifying-glass" size="sm" placeholder="Cari tugas di semua space..."
        wire:model.live.debounce.300ms="globalSearch" @focus="showGlobalSuggestions = true"
        @input="showGlobalSuggestions = true" />

      @if (mb_strlen(trim($globalSearch)) >= 2)
        <div x-show="showGlobalSuggestions" x-cloak x-transition:enter="transition ease-out duration-100"
          x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
          class="absolute inset-x-0 top-full z-40 mt-1 max-h-72 overflow-y-auto rounded-lg border border-zinc-200 bg-white py-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
          @forelse ($this->globalSearchSuggestions as $suggestion)
            <button type="button" wire:key="global-suggestion-{{ $suggestion->id }}"
              @click="showGlobalSuggestions = false; $flux.modal('gen-task-detail').show(); if ($wire.selectedTaskId !== {{ $suggestion->id }}) { $wire.openTaskDetail({{ $suggestion->id }}) }"
              class="flex w-full items-center justify-between gap-2 px-3 py-2 text-left text-sm text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-700">
              <span class="min-w-0">
                <span class="block truncate font-medium text-zinc-800 dark:text-zinc-200">
                  {{ $suggestion->title }}
                </span>
                <span class="mt-0.5 flex items-center gap-1 text-[10px] text-zinc-400 dark:text-zinc-500">
                  <span class="h-1.5 w-1.5 shrink-0 rounded-full"
                    style="background-color: {{ $suggestion->taskList?->space?->color ?? '#6366f1' }}"></span>
                  <span class="truncate">
                    {{ $suggestion->taskList?->space?->name ?? '-' }} / {{ $suggestion->taskList?->name ?? '-' }}
                  </span>
                </span>
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

    @if ($activeTab === 'lists')
      <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari list..." size="sm"
        icon="magnifying-glass" class="w-full sm:w-48" />
    @endif

    @if (auth()->user()->canManageLists())
      <flux:button size="sm" variant="ghost" icon="plus" class="cursor-pointer"
        @click="$flux.modal('create-list-modal').show()">
        Tambah List
      </flux:button>

      <flux:button size="sm" variant="primary" icon="plus" class="cursor-pointer"
        @click="$wire.showCreateSpace = true; $flux.modal('create-space-modal').show()">
        Tambah Space
      </flux:button>
    @endif
  </div>
</div>

<div class="mb-6 flex items-center gap-1 border-b border-zinc-200 dark:border-zinc-700">
  <button wire:click="$set('activeTab', 'lists')"
    class="cursor-pointer flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors
      {{ $activeTab === 'lists'
          ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
          : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
    <flux:icon name="squares-2x2" class="size-4" />
    Lists
    <span
      class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold {{ $activeTab === 'lists' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'bg-zinc-100 text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400' }}">
      {{ $totalLists }}
    </span>
  </button>

  <button wire:click="$set('activeTab', 'calendar')"
    class="cursor-pointer flex items-center gap-2 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors
      {{ $activeTab === 'calendar'
          ? 'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
          : 'border-transparent text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
    <flux:icon name="calendar-days" class="size-4" />
    Kalender Deadline
  </button>
</div>
