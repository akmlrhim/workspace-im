@if ($spaces->isNotEmpty())
  <div class="space-y-8">
    @foreach ($spaces as $space)
      <div x-show="!deletedSpaceIds.includes({{ $space->id }})"
        x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" wire:key="space-{{ $space->id }}">
        <div class="mb-3 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <button @click="toggleSpace({{ $space->id }})"
              class="cursor-pointer flex items-center gap-2 rounded-md px-1 py-0.5 transition hover:bg-zinc-100 dark:hover:bg-zinc-800">
              <flux:icon x-show="!collapsedSpaces.includes({{ $space->id }})" name="chevron-down" class="size-3.5 text-zinc-400" />
              <flux:icon x-show="collapsedSpaces.includes({{ $space->id }})" name="chevron-right" class="size-3.5 text-zinc-400" />
              <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md"
                style="background-color: {{ $space->color }}20">
                <flux:icon name="{{ $space->icon }}" class="size-4" style="color: {{ $space->color }}" />
              </div>
              <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                {{ $space->name }}
              </h2>
            </button>
            <span
              class="rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
              {{ $space->lists->count() }}
            </span>
          </div>

          @if (auth()->user()->canManageLists())
            <div class="flex items-center gap-0.5">
              <button title="Edit Space"
                @click="$wire.editingSpaceId = {{ $space->id }}; $wire.editSpaceName = @js($space->name); $wire.editSpaceColor = @js($space->color); $wire.editSpaceIcon = @js($space->icon); $wire.showEditSpace = true"
                class="cursor-pointer flex h-7 w-7 items-center justify-center rounded-md text-zinc-400 transition hover:bg-zinc-100 hover:text-indigo-600 dark:hover:bg-zinc-800 dark:hover:text-indigo-400">
                <flux:icon name="pencil-square" class="size-3.5" />
              </button>
              <button title="Hapus Space"
                @click="deletingSpaceId = {{ $space->id }}; deletingSpaceName = @js($space->name); $flux.modal('delete-space-modal').show()"
                class="cursor-pointer flex h-7 w-7 items-center justify-center rounded-md text-zinc-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                <flux:icon name="trash" class="size-3.5" />
              </button>
            </div>
          @endif
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
          x-show="!collapsedSpaces.includes({{ $space->id }})"
          x-transition:enter="transition duration-200 ease-out"
          x-transition:enter-start="opacity-0 -translate-y-1"
          x-transition:enter-end="opacity-100 translate-y-0"
          x-transition:leave="transition duration-150 ease-in"
          x-transition:leave-start="opacity-100 translate-y-0"
          x-transition:leave-end="opacity-0 -translate-y-1">
          @foreach ($space->lists as $list)
            <div x-show="!deletedListIds.includes({{ $list->id }})"
              x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100"
              x-transition:leave-end="opacity-0">
              @include('livewire.partials.taskboard-list-card', ['list' => $list])
            </div>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>
@else
  <x-empty-state
    icon="clipboard-document-list"
    heading="Belum ada list"
    subheading="Mulai dengan membuat Space dan List pertama Anda.">
    <flux:button size="sm" variant="ghost"
      @click="$flux.modal('create-list-modal').show()">
      Tambah List
    </flux:button>
    <flux:button size="sm" variant="primary"
      @click="$wire.showCreateSpace = true; $flux.modal('create-space-modal').show()">
      Tambah Space
    </flux:button>
  </x-empty-state>
@endif
