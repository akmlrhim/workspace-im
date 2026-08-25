<flux:modal name="create-space-modal" wire:model="showCreateSpace" class="w-full max-w-md">
  <div class="space-y-6">
    <div>
      <flux:heading size="lg">Buat Space Baru</flux:heading>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Space adalah wadah untuk mengelompokkan task list.</p>
    </div>

    <form wire:submit="createSpace" class="space-y-4">
      <flux:field>
        <flux:label>Nama Space</flux:label>
        <flux:input wire:model="createSpaceForm.name" placeholder="Contoh: Divisi, Proyek, HQ, Arsip....."
          autofocus />
        <flux:error name="createSpaceForm.name" />
      </flux:field>

      <x-space-color-picker model="$wire.createSpaceForm.color" />

      <x-space-icon-picker model="$wire.createSpaceForm.icon" />

      <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('create-space-modal').close()">
          Batal
        </flux:button>
        <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Buat Space</flux:button>
      </div>
    </form>
  </div>
</flux:modal>

<flux:modal name="create-list-modal" class="w-full max-w-md" @close="$wire.resetCreateListForm()">
  <div class="space-y-6">
    <div>
      <flux:heading size="lg">Buat List Baru</flux:heading>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">List berisi task-task yang dikelola dalam sebuah
        space.</p>
    </div>

    <form wire:submit="createList" class="space-y-4">
      <flux:field>
        <flux:label>Nama List</flux:label>
        <flux:input wire:model="createListForm.name" placeholder="Finance, HR, Creative......" autofocus />
        <flux:error name="createListForm.name" />
      </flux:field>

      <flux:field>
        <flux:label>Space</flux:label>
        <flux:select wire:model="createListForm.spaceId">
          <option value="">— Pilih space —</option>
          @foreach ($this->spaces as $sp)
            <option value="{{ $sp->id }}">{{ $sp->name }}</option>
          @endforeach
        </flux:select>
        <flux:error name="createListForm.spaceId" />
      </flux:field>

      <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('create-list-modal').close()">
          Batal
        </flux:button>
        <flux:button type="submit" variant="primary" class="w-full sm:w-auto"
          wire:loading.attr="disabled" wire:target="createList">
          <span wire:loading.remove wire:target="createList">Buat List</span>
          <span wire:loading wire:target="createList">Menyimpan...</span>
        </flux:button>
      </div>
    </form>
  </div>
</flux:modal>

<flux:modal wire:model="showEditSpace" class="w-full max-w-md">
  <div class="space-y-6">
    <div>
      <flux:heading size="lg">Edit Space</flux:heading>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Ubah nama, warna, atau ikon space.</p>
    </div>

    <form wire:submit="updateSpace" class="space-y-4">
      <flux:field>
        <flux:label>Nama Space</flux:label>
        <flux:input wire:model="editSpaceName" placeholder="Masukkan nama space" autofocus />
        <flux:error name="editSpaceName" />
      </flux:field>

      <x-space-color-picker model="$wire.editSpaceColor" />

      <x-space-icon-picker model="$wire.editSpaceIcon"
        selectedClass="border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400" />

      <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showEditSpace', false)">
          Batal
        </flux:button>
        <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Simpan</flux:button>
      </div>
    </form>
  </div>
</flux:modal>

<flux:modal name="edit-list-modal" wire:model="showEditList" class="w-full max-w-md">
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
        <flux:select wire:model="editListSpaceId">
          <option value="">— Pilih space —</option>
          @foreach ($this->spaces as $sp)
            <option value="{{ $sp->id }}">{{ $sp->name }}</option>
          @endforeach
        </flux:select>
        <flux:error name="editListSpaceId" />
      </flux:field>
      <div class="flex flex-col gap-2 pt-2 sm:flex-row sm:justify-end">
        <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showEditList', false)">Batal
        </flux:button>
        <flux:button type="submit" variant="primary" class="w-full sm:w-auto">Simpan</flux:button>
      </div>
    </form>
  </div>
</flux:modal>

<flux:modal name="manage-members-modal" wire:model="showManageMembers" class="w-full max-w-md">
  <div class="space-y-6">
    <div>
      <flux:heading size="lg">Kelola Anggota List</flux:heading>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Anggota yang terdaftar dapat di-assign ke task dalam
        list ini.</p>
    </div>

    <div class="max-h-64 overflow-y-auto rounded-lg border border-zinc-200 p-1.5 dark:border-zinc-700">
      <div x-show="membersLoading" class="flex min-h-[120px] items-center justify-center">
        <x-spinner />
      </div>
      <div x-show="!membersLoading">
        @foreach ($this->allUsers as $user)
          <label wire:key="gtb-user-{{ $user->id }}"
            class="flex cursor-pointer items-center gap-3 rounded-md px-3 py-2 text-sm hover:bg-zinc-50 dark:hover:bg-zinc-800">
            <input type="checkbox" wire:model.live="listMemberIds" value="{{ $user->id }}"
              class="size-4 cursor-pointer rounded border-zinc-300 text-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-700 dark:checked:bg-indigo-500" />
            <flux:avatar circle :name="$user->name" :initials="$user->initials()" :src="$user->avatar"
              size="sm" />
            <div class="min-w-0 flex-1">
              <span class="block font-medium text-zinc-800 dark:text-zinc-200">{{ $user->name }}</span>
              <span class="block truncate text-xs text-zinc-400">{{ $user->email }}</span>
            </div>
          </label>
        @endforeach
      </div>
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

<flux:modal name="delete-list-modal" class="w-full max-w-sm">
  <div class="space-y-6">
    <div>
      <flux:heading size="lg">Hapus List</flux:heading>
      <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
        List <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="deletingListName"></span>
        beserta seluruh task di dalamnya akan dihapus permanen.
      </p>
    </div>
    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
      <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('delete-list-modal').close()">
        Batal
      </flux:button>
      <flux:button variant="danger" class="w-full sm:w-auto"
        @click="deletedListIds.push(deletingListId); $wire.deleteList(deletingListId); $flux.modal('delete-list-modal').close()">
        Hapus
      </flux:button>
    </div>
  </div>
</flux:modal>

<flux:modal name="delete-space-modal" class="w-full max-w-sm">
  <div class="space-y-6">
    <div>
      <flux:heading size="lg">Hapus Space</flux:heading>
      <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
        Space <span class="font-semibold text-zinc-800 dark:text-zinc-200" x-text="deletingSpaceName"></span>
        beserta seluruh list dan task di dalamnya akan dihapus permanen.
      </p>
    </div>
    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
      <flux:button variant="ghost" class="w-full sm:w-auto" @click="$flux.modal('delete-space-modal').close()">
        Batal
      </flux:button>
      <flux:button variant="danger" class="w-full sm:w-auto"
        @click="deletedSpaceIds.push(deletingSpaceId); $wire.deleteSpace(deletingSpaceId); $flux.modal('delete-space-modal').close()">
        Hapus
      </flux:button>
    </div>
  </div>
</flux:modal>
