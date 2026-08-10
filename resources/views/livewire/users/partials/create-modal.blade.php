<flux:modal wire:model="showCreateModal"
  class="w-full max-w-lg max-sm:max-w-none max-sm:rounded-none max-sm:h-dvh max-sm:!m-0">
  <div class="space-y-6 max-sm:overflow-y-auto max-sm:pb-8">
    <flux:heading size="lg">Tambah Pengguna Baru</flux:heading>

    <form wire:submit="createUser" class="space-y-4">
      <flux:field>
        <flux:label>Nama Lengkap</flux:label>
        <flux:input wire:model="createName" placeholder="Masukkan nama lengkap" autofocus />
        <flux:error name="createName" />
      </flux:field>

      <flux:field>
        <flux:label>Email</flux:label>
        <flux:input wire:model="createEmail" type="email" placeholder="Masukkan email" />
        <flux:error name="createEmail" />
      </flux:field>

      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <flux:field>
          <flux:label>Role</flux:label>
          <flux:select wire:model="createRole">
            @foreach ($roles as $value => $label)
              @if ($value !== 'super_user' || auth()->user()->isSuperUser())
                <option value="{{ $value }}">{{ $label }}</option>
              @endif
            @endforeach
          </flux:select>
          <flux:error name="createRole" />
        </flux:field>

        <flux:field>
          <flux:label>Jabatan
          </flux:label>
          @if (!$createPositionCustom)
            <flux:select wire:change="selectCreatePosition($event.target.value)">
              <option value="">— Pilih Jabatan —</option>
              @foreach ($positions as $pos)
                <option value="{{ $pos }}" @selected($createPosition === $pos)>{{ $pos }}</option>
              @endforeach
              <option value="__other__">Lainnya (ketik sendiri)...</option>
            </flux:select>
          @else
            <div class="flex gap-2">
              <flux:input wire:model="createPosition" placeholder="Masukkan jabatan..." autofocus />
              <flux:button size="sm" variant="ghost" icon="arrow-uturn-left"
                wire:click="clearCreatePositionCustom" title="Pilih dari daftar" />
            </div>
          @endif
          <flux:error name="createPosition" />
        </flux:field>
      </div>

      <flux:field>
        <flux:label>Password</flux:label>
        <flux:input wire:model="createPassword" type="password" viewable />
        <flux:error name="createPassword" />
      </flux:field>

      <flux:field>
        <flux:label>Konfirmasi Password</flux:label>
        <flux:input wire:model="createPassword_confirmation" type="password" viewable />
        <flux:error name="createPassword_confirmation" />
      </flux:field>

      <div class="flex justify-end gap-2 pt-2">
        <flux:button variant="ghost" wire:click="$set('showCreateModal', false)">Batal</flux:button>
        <flux:button type="submit" variant="primary">Buat Pengguna</flux:button>
      </div>
    </form>
  </div>
</flux:modal>
