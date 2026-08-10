<flux:modal wire:model="showEditModal"
  class="w-full max-w-lg max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
  <div class="space-y-6 max-sm:overflow-y-auto max-sm:pb-8">
    <flux:heading size="lg">Edit Pengguna</flux:heading>

    <form wire:submit="updateUser" class="space-y-4">
      <flux:field>
        <flux:label>Nama Lengkap</flux:label>
        <flux:input wire:model="editName" />
        <flux:error name="editName" />
      </flux:field>

      <flux:field>
        <flux:label>Email</flux:label>
        <flux:input wire:model="editEmail" type="email" />
        <flux:error name="editEmail" />
      </flux:field>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <flux:field>
          <flux:label>Role</flux:label>
          <flux:select wire:model="editRole">
            @foreach ($roles as $value => $label)
              @if ($value !== 'super_user' || auth()->user()->isSuperUser())
                <option value="{{ $value }}">{{ $label }}</option>
              @endif
            @endforeach
          </flux:select>
          <flux:error name="editRole" />
        </flux:field>

        <flux:field>
          <flux:label>Jabatan
          </flux:label>
          @if (!$editPositionCustom)
            <flux:select wire:change="selectEditPosition($event.target.value)">
              <option value="">— Pilih Jabatan —</option>
              @foreach ($positions as $pos)
                <option value="{{ $pos }}" @selected($editPosition === $pos)>{{ $pos }}</option>
              @endforeach
              <option value="__other__">Lainnya (ketik sendiri)...</option>
            </flux:select>
          @else
            <div class="flex gap-2">
              <flux:input wire:model="editPosition" placeholder="Masukkan jabatan..." autofocus />
              <flux:button size="sm" variant="ghost" icon="arrow-uturn-left" wire:click="clearEditPositionCustom"
                title="Pilih dari daftar" />
            </div>
          @endif
          <flux:error name="editPosition" />
        </flux:field>
      </div>

      <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
        <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
          Reset Password
        </p>
        <div class="space-y-3">
          <flux:field>
            <flux:label>Password Baru</flux:label>
            <flux:input wire:model="editPassword" type="password" viewable />
            <flux:error name="editPassword" />
          </flux:field>
          <flux:field>
            <flux:label>Konfirmasi Password Baru</flux:label>
            <flux:input wire:model="editPassword_confirmation" type="password" viewable />
            <flux:error name="editPassword_confirmation" />
          </flux:field>
        </div>
      </div>

      <div class="flex justify-end gap-2 pt-2">
        <flux:button variant="ghost" wire:click="$set('showEditModal', false)">Batal</flux:button>
        <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
      </div>
    </form>
  </div>
</flux:modal>
