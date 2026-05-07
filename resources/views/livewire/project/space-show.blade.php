<div>
  <div class="mb-6">
    <div class="mb-3 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
      <a href="{{ route('project-management.index') }}" wire:navigate
        class="hover:text-zinc-700 dark:hover:text-zinc-200">Spaces</a>
      <flux:icon name="chevron-right" class="size-3.5" />
      <span class="font-medium text-zinc-900 dark:text-white">{{ $space->name }}</span>
    </div>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg"
          style="background-color: {{ $space->color }}20">
          <flux:icon name="{{ $space->icon }}" class="size-5" style="color: {{ $space->color }}" />
        </div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $space->name }}</h1>
      </div>

      <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
        <flux:button icon="plus" size="sm" variant="primary" class="w-full justify-center sm:w-auto"
          wire:click="openCreateList">
          New List
        </flux:button>
      </div>
    </div>
  </div>

  @if ($this->lists->isNotEmpty())
    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
      @foreach ($this->lists as $list)
        @include('livewire.project.partials.list-card', ['list' => $list])
      @endforeach
    </div>
  @else
    <div
      class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-8 py-16 text-center dark:border-zinc-700 dark:bg-zinc-800/50">
      <flux:icon name="queue-list" class="mb-3 size-12 text-zinc-400" />
      <h3 class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">This space is empty</h3>
      <p class="mt-1 text-sm text-zinc-500">Create a list to start tracking tasks</p>

      <div class="mt-6 flex w-full flex-col gap-2 sm:w-auto sm:flex-row">
        <flux:button icon="plus" variant="primary" class="w-full justify-center sm:w-auto"
          wire:click="openCreateList">
          Buat List
        </flux:button>
      </div>
    </div>
  @endif

  <flux:modal wire:model="showCreateList" class="w-full max-w-md">
    <div class="space-y-6">
      <flux:heading size="lg">Buat List Baru</flux:heading>
      <form wire:submit="createList" class="space-y-4">
        <flux:field>
          <flux:label>Nama List</flux:label>
          <flux:input wire:model="listName" placeholder="Masukkan nama list" autofocus />
          <flux:error name="listName" />
        </flux:field>
        <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-end">
          <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showCreateList', false)">Cancel
          </flux:button>
          <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Buat List</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  <flux:modal wire:model="showEditList" class="w-full max-w-md">
    <div class="space-y-6">
      <flux:heading size="lg">Edit List</flux:heading>
      <form wire:submit="updateList" class="space-y-4">
        <flux:field>
          <flux:label>Nama List</flux:label>
          <flux:input wire:model="editListName" placeholder="Masukkan nama list" autofocus />
          <flux:error name="editListName" />
        </flux:field>
        <flux:field>
          <flux:label>Space</flux:label>
          <flux:select wire:model="editListSpaceId" placeholder="Pilih space tujuan...">
            @foreach ($this->spaces as $sp)
              <flux:select.option value="{{ $sp->id }}">{{ $sp->name }}</flux:select.option>
            @endforeach
          </flux:select>
          <flux:error name="editListSpaceId" />
        </flux:field>
        <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-end">
          <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showEditList', false)">Cancel
          </flux:button>
          <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Simpan</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  <flux:modal wire:model="showDeleteListConfirm" class="w-full max-w-sm">
    <div class="space-y-4 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
        <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
      </div>
      <flux:heading size="lg">Hapus List?</flux:heading>
      <p class="text-sm text-zinc-500 dark:text-zinc-400">Semua task dalam list ini akan dihapus secara permanen.</p>
      <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-center">
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showDeleteListConfirm', false)">
          Cancel</flux:button>
        <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteList">Delete</flux:button>
      </div>
    </div>
  </flux:modal>

  <flux:modal wire:model="showManageMembers" class="w-full max-w-md">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Kelola Anggota List</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Anggota yang terdaftar di sini dapat di-assign ke task
          dalam list ini dan space akan muncul di sidebar mereka.</p>
      </div>

      <div class="max-h-64 overflow-y-auto rounded-lg border border-zinc-200 p-1.5 dark:border-zinc-700">
        @foreach ($this->allUsers as $user)
          <label wire:key="user-{{ $user->id }}"
            class="flex cursor-pointer items-center gap-3 rounded-md px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800">
            <flux:checkbox wire:model="listMemberIds" :value="$user->id" />
            <flux:avatar circle :name="$user->name" :initials="$user->initials()" :src="$user->avatar"
              size="sm" />
            <div class="min-w-0 flex-1">
              <span class="block font-medium text-zinc-800 dark:text-zinc-200">{{ $user->name }}</span>
              <span class="block truncate text-xs text-zinc-400">{{ $user->email }}</span>
            </div>
          </label>
        @endforeach
      </div>

      <div class="flex items-center justify-between">
        <span class="text-xs text-zinc-400">{{ count($listMemberIds) }} anggota dipilih</span>
        <div class="flex gap-2">
          <flux:button variant="ghost" @click="$wire.set('showManageMembers', false)">Batal</flux:button>
          <flux:button variant="primary" wire:click="saveMembers">Simpan Anggota</flux:button>
        </div>
      </div>
    </div>
  </flux:modal>

</div>
