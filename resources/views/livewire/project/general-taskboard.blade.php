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

  <flux:modal wire:model="showEditList" class="w-full max-w-md">
    <div class="space-y-6">
      <flux:heading size="lg">Edit List</flux:heading>
      <form wire:submit="updateList" class="space-y-4">
        <flux:field>
          <flux:label>Nama List</flux:label>
          <flux:input wire:model="editListName" placeholder="Masukkan nama list" autofocus />
          <flux:error name="editListName" />
        </flux:field>
        <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-end">
          <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showEditList', false)">Batal
          </flux:button>
          <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Simpan</flux:button>
        </div>
      </form>
    </div>
  </flux:modal>

  <flux:modal wire:model="showManageMembers" class="w-full max-w-md">
    <div class="space-y-6">
      <div>
        <flux:heading size="lg">Kelola Anggota List</flux:heading>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Anggota yang terdaftar di sini dapat di-assign ke task
          dalam list ini.</p>
      </div>

      <div class="max-h-64 overflow-y-auto rounded-lg border border-zinc-200 p-1.5 dark:border-zinc-700">
        @foreach ($this->allUsers as $user)
          <label wire:key="gtb-user-{{ $user->id }}"
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
