<div>
  {{-- Header --}}
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">General Taskboard</flux:heading>
    </div>

    <div class="flex items-center gap-2">
      <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari list..." size="sm" icon="magnifying-glass"
        class="w-full sm:w-56" />
    </div>
  </div>

  {{-- Lists grouped by Space --}}
  @if ($grouped->isNotEmpty())
    <div class="space-y-8">
      @foreach ($grouped as $spaceName => $lists)
        @php
          $space = $lists->first()->space;
        @endphp

        <div>
          {{-- Space Header --}}
          <div class="mb-3 flex items-center gap-2">
            @if ($space)
              <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md"
                style="background-color: {{ $space->color }}20">
                <flux:icon name="{{ $space->icon }}" class="size-4" style="color: {{ $space->color }}" />
              </div>
            @endif
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
              {{ $spaceName }}
            </h2>
            <span
              class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
              {{ $lists->count() }}
            </span>
          </div>

          {{-- Folder groups --}}
          @php
            $folders = $lists->filter(fn($l) => $l->folder_id !== null)->groupBy(fn($l) => $l->folder->name);
            $listsWithoutFolder = $lists->filter(fn($l) => $l->folder_id === null);
          @endphp

          @foreach ($folders as $folderName => $folderLists)
            <div class="mb-4 ml-2">
              <div class="mb-2 flex items-center gap-1.5">
                <flux:icon name="folder" class="size-3.5 text-zinc-400" />
                <span class="text-xs font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
                  {{ $folderName }}
                </span>
              </div>
              <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($folderLists as $list)
                  @include('livewire.project.partials.taskboard-list-card', ['list' => $list])
                @endforeach
              </div>
            </div>
          @endforeach

          @if ($listsWithoutFolder->isNotEmpty())
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
              @foreach ($listsWithoutFolder as $list)
                @include('livewire.project.partials.taskboard-list-card', ['list' => $list])
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    </div>
  @else
    <div
      class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 py-20 dark:border-zinc-700 dark:bg-zinc-800/20">
      <div class="mb-4 rounded-full bg-zinc-100 p-4 dark:bg-zinc-500/20">
        <flux:icon name="clipboard-document-list" class="size-9 text-zinc-400 dark:text-zinc-500" />
      </div>
      <flux:heading size="lg">Belum ada list</flux:heading>
      <flux:subheading class="mt-1">Tidak ada task list yang dapat diakses saat ini.</flux:subheading>
    </div>
  @endif
</div>
