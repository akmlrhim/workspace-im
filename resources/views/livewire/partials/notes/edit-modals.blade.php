<flux:modal wire:model="showEditNoteForm" class="w-full max-w-lg">
  <div class="space-y-4">
    <flux:heading size="lg">Edit Catatan</flux:heading>

    <flux:field>
      <flux:label>Judul</flux:label>
      <flux:input wire:model="editNoteTitle" placeholder="Judul catatan..." maxlength="255" />
    </flux:field>

    <flux:field>
      <flux:label>Isi <span class="font-normal text-zinc-400">(opsional)</span></flux:label>
      <flux:textarea wire:model="editNoteContent" placeholder="Tulis catatan di sini..." rows="4" />
    </flux:field>

    <div class="flex justify-end gap-2 pt-2">
      <flux:button variant="ghost" wire:click="$set('showEditNoteForm', false)">Batal</flux:button>
      <flux:button variant="primary" wire:click="updateNote" wire:loading.attr="disabled" wire:target="updateNote">
        Simpan
      </flux:button>
    </div>
  </div>
</flux:modal>

<flux:modal wire:model="showLinkForm" class="w-full max-w-md">
  <div class="space-y-4">
    <flux:heading size="lg">Tambah Link</flux:heading>

    <flux:field>
      <flux:label>URL</flux:label>
      <flux:input wire:model="newLinkUrl" type="url" placeholder="https://..." wire:keydown.enter="addLink" />
    </flux:field>

    <flux:field>
      <flux:label>Label <span class="font-normal text-zinc-400">(opsional)</span></flux:label>
      <flux:input wire:model="newLinkLabel" placeholder="Nama link..." maxlength="255"
        wire:keydown.enter="addLink" />
    </flux:field>

    <div class="flex justify-end gap-2 pt-2">
      <flux:button variant="ghost" wire:click="$set('showLinkForm', false)">Batal</flux:button>
      <flux:button variant="primary" wire:click="addLink" wire:loading.attr="disabled" wire:target="addLink">
        Simpan
      </flux:button>
    </div>
  </div>
</flux:modal>

<flux:modal wire:model="showDeleteNoteConfirm" class="w-full max-w-sm">
  <div class="space-y-4 text-center">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
      <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
    </div>
    <flux:heading size="lg">Hapus Catatan?</flux:heading>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">
      Catatan beserta semua lampirannya akan dihapus. Tindakan ini tidak bisa dibatalkan.
    </p>
    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-center">
      <flux:button variant="ghost" class="w-full sm:w-auto" wire:click="$set('showDeleteNoteConfirm', false)">
        Batal</flux:button>
      <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteNote" wire:loading.attr="disabled"
        wire:target="deleteNote">Hapus</flux:button>
    </div>
  </div>
</flux:modal>

<flux:modal wire:model="showDeleteAttachmentConfirm" class="w-full max-w-sm">
  <div class="space-y-4 text-center">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
      <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
    </div>
    <flux:heading size="lg">Hapus Lampiran?</flux:heading>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">
      Lampiran ini akan dihapus dari catatan. Tindakan ini tidak bisa dibatalkan.
    </p>
    <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-center">
      <flux:button variant="ghost" class="w-full sm:w-auto" wire:click="$set('showDeleteAttachmentConfirm', false)">
        Batal</flux:button>
      <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteAttachment"
        wire:loading.attr="disabled" wire:target="deleteAttachment">Hapus</flux:button>
    </div>
  </div>
</flux:modal>
