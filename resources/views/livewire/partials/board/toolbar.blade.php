<div class="mb-4">
  @include('livewire.partials.breadcrumb')
  <div class="flex items-center justify-between gap-3 mb-4">
    <h1 class="hidden lg:block text-2xl font-bold text-zinc-900 dark:text-white">
      {{ $taskList->name }}
    </h1>
  </div>
  @include('livewire.partials.view-toggle', ['active' => 'board'])

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
