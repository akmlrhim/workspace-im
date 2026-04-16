<div>
  <div class="mb-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-md lg:text-2xl  font-bold text-zinc-900 dark:text-white">Spaces</h1>
        <p class="mt-1 text-xs lg:text-sm text-zinc-500 dark:text-zinc-400">Kelola tugas dan proyek Anda dalam
          ruang-ruang yang terorganisir</p>
      </div>
      <div class="flex items-center gap-2">
        <flux:button icon="plus" variant="primary" size="sm" class="max-sm:!px-2.5" @click="$wire.set('showCreateSpace', true)">
          <span class="hidden sm:inline">Buat space baru</span>
          <span class="sm:hidden">Baru</span>
        </flux:button>
      </div>
    </div>
  </div>
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($this->spaces as $space)
      <div wire:key="space-{{ $space->id }}"
        class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white transition-all hover:shadow-lg hover:shadow-zinc-200/50 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:shadow-zinc-900/50">
        <div class="h-1.5" style="background-color: {{ $space->color }}"></div>

        <div class="p-5">
          <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-lg"
                style="background-color: {{ $space->color }}20">
                <flux:icon name="{{ $space->icon }}" class="size-5" style="color: {{ $space->color }}" />
              </div>
              <div>
                <a href="{{ route('project-management.spaces.show', $space) }}" wire:navigate
                  class="font-semibold text-zinc-900 hover:underline dark:text-white">
                  {{ $space->name }}
                </a>
                <div class="mt-0.5 flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                  <span>{{ $space->lists_count }} {{ Str::plural('list', $space->lists_count) }}</span>
                  <span>{{ $space->folders_count }} {{ Str::plural('folder', $space->folders_count) }}</span>
                </div>
              </div>
            </div>

            <flux:dropdown position="bottom" align="end">
              <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost"
                class="sm:opacity-0 transition-opacity group-hover:opacity-100" />
              <flux:menu>
                <flux:menu.item icon="pencil-square" wire:click="editSpace({{ $space->id }})">Edit</flux:menu.item>
                <flux:menu.separator />
                <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $space->id }})">
                  Delete
                </flux:menu.item>
              </flux:menu>
            </flux:dropdown>
          </div>
        </div>
      </div>
    @empty
      <div
        class="col-span-full flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 py-16 dark:border-zinc-700 dark:bg-zinc-800/50">
        <flux:icon name="folder-plus" class="mb-3 size-12 text-zinc-400" />
        <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">No spaces yet</h3>
        <p class="mt-1 text-sm text-zinc-500 text-center">Create your first space to start organizing projects</p>
        <flux:button icon="plus" variant="primary" class="mt-4" @click="$wire.set('showCreateSpace', true)">
          Create Space
        </flux:button>
      </div>
    @endforelse
  </div>
  <flux:modal wire:model="showCreateSpace" class="w-full max-w-lg max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
    <div class="space-y-6">
      <flux:heading size="lg">Create New Space</flux:heading>

      <form wire:submit="createSpace" class="space-y-4">
        <flux:field>
          <flux:label>Space Name</flux:label>
          <flux:input wire:model="spaceName" placeholder="e.g. Engineering, Marketing..." autofocus />
          <flux:error name="spaceName" />
        </flux:field>

        <flux:field>
          <flux:label>Color</flux:label>
          <div class="flex flex-wrap gap-2">
            @foreach ($this->availableColors as $color)
              <button type="button" @click="$wire.set('spaceColor', '{{ $color }}')"
                class="h-8 w-8 rounded-full transition-transform hover:scale-110 {{ $spaceColor === $color ? 'ring-2 ring-offset-2 ring-offset-white dark:ring-offset-zinc-800' : '' }}"
                style="background-color: {{ $color }}; {{ $spaceColor === $color ? 'ring-color: ' . $color : '' }}">
              </button>
            @endforeach
          </div>
        </flux:field>

        <div class="flex justify-end gap-2 pt-2">
          <flux:button variant="ghost" @click="$wire.set('showCreateSpace', false)">Cancel</flux:button>
          <flux:button type="submit" variant="primary">Create Space</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>
  <flux:modal wire:model="showEditSpace" class="w-full max-w-lg max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
    <div class="space-y-6">
      <flux:heading size="lg">Edit Space</flux:heading>

      <form wire:submit="updateSpace" class="space-y-4">
        <flux:field>
          <flux:label>Space Name</flux:label>
          <flux:input wire:model="editSpaceName" />
          <flux:error name="editSpaceName" />
        </flux:field>

        <flux:field>
          <flux:label>Color</flux:label>
          <div class="flex flex-wrap gap-2">
            @foreach ($this->availableColors as $color)
              <button type="button" @click="$wire.set('editSpaceColor', '{{ $color }}')"
                class="h-8 w-8 rounded-full transition-transform hover:scale-110 {{ $editSpaceColor === $color ? 'ring-2 ring-offset-2 ring-offset-white dark:ring-offset-zinc-800' : '' }}"
                style="background-color: {{ $color }}; {{ $editSpaceColor === $color ? 'ring-color: ' . $color : '' }}">
              </button>
            @endforeach
          </div>
        </flux:field>

        <div class="flex justify-end gap-2 pt-2">
          <flux:button variant="ghost" @click="$wire.set('showEditSpace', false)">Cancel</flux:button>
          <flux:button type="submit" variant="primary">Save Changes</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  <flux:modal wire:model="showDeleteConfirm" class="w-full max-w-sm">
    <div class="space-y-4 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
        <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
      </div>
      <flux:heading size="lg">Hapus space?</flux:heading>
      <p class="text-sm text-zinc-500 dark:text-zinc-400">
        Apakah Anda yakin ingin menghapus space ini? Semua proyek, tugas, dan data terkait dalam space ini akan
        dihapus secara permanen. Tindakan ini tidak dapat dibatalkan.
      </p>
      <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-center">
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showDeleteConfirm', false)">Cancel</flux:button>
        <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteSpace">Delete Space</flux:button>
      </div>
    </div>
  </flux:modal>
</div>
