<flux:modal wire:model="showDeleteColumnConfirm" class="w-full max-w-sm">
  <div class="space-y-4 text-center">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
      <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
    </div>
    <flux:heading size="lg">Hapus Kolom?</flux:heading>
    <p class="text-sm text-zinc-500 dark:text-zinc-400">
      Semua tugas dalam kolom ini akan dipindahkan ke kolom pertama yang tersedia.
    </p>
    <div class="flex flex-col-reverse gap-2 pt-4 sm:flex-row sm:justify-center">
      <flux:button variant="ghost" class="w-full sm:w-auto" @click="$wire.set('showDeleteColumnConfirm', false)">
        Batal</flux:button>
      <flux:button variant="danger" class="w-full sm:w-auto" wire:click="deleteColumn">Hapus</flux:button>
    </div>
  </div>
</flux:modal>

<flux:modal name="rename-column-modal" class="w-full max-w-sm">
  <div x-data="{ id: null, name: '', color: '#6b7280', nameError: '', saving: false }"
    @open-column-rename.window="
      id = $event.detail.id;
      name = $event.detail.name;
      color = $event.detail.color;
      nameError = '';
      saving = false;
      $nextTick(() => $refs.renameInput?.focus());
    ">
    <form
      @submit.prevent="
        nameError = name.trim().length === 0 ? 'Nama kolom wajib diisi.' : (name.trim().length > 100 ? 'Maksimal 100 karakter.' : '');
        if (nameError || saving) return;
        saving = true;
        $wire.saveColumnRename(id, name.trim(), color)
          .then(() => $flux.modal('rename-column-modal').close())
          .finally(() => saving = false)
      "
      class="space-y-5">
      <flux:heading size="lg">Ubah Kolom</flux:heading>

      <div>
        <flux:label>Nama Kolom</flux:label>
        <flux:input x-model="name" x-ref="renameInput" placeholder="Nama kolom..."
          @keydown.escape="$flux.modal('rename-column-modal').close()" />
        <p x-show="nameError" x-text="nameError" x-cloak class="mt-1 text-sm text-red-500 dark:text-red-400"></p>
      </div>

      <div>
        <flux:label>Warna</flux:label>
        <div class="mt-2 flex flex-wrap gap-2">
          @foreach (['#6b7280', '#3b82f6', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'] as $col)
            <button type="button" @click="color = '{{ $col }}'" title="{{ $col }}"
              class="h-7 w-7 rounded-full border-[3px] transition-transform hover:scale-110"
              :class="color === '{{ $col }}' ? 'border-zinc-900 scale-110 dark:border-white' : 'border-transparent'"
              style="background-color: {{ $col }}"></button>
          @endforeach
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-1">
        <flux:button variant="ghost" type="button" @click="$flux.modal('rename-column-modal').close()">Batal
        </flux:button>
        <flux:button variant="primary" type="submit" x-bind:disabled="saving">
          <span x-show="!saving">Simpan</span>
          <span x-show="saving" x-cloak class="flex items-center gap-1.5">
            <svg class="size-3.5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            Menyimpan...
          </span>
        </flux:button>
      </div>
    </form>
  </div>
</flux:modal>
