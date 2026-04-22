<div>
  {{-- Header --}}
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">User Management</h1>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Kelola semua pengguna aplikasi</p>
    </div>
    @can('manage-users')
      <flux:button icon="plus" variant="primary" wire:click="openCreate">
        Tambah Pengguna
      </flux:button>
    @endcan
  </div>

  {{-- Filters --}}
  <div class="mb-4 flex flex-col gap-3 sm:flex-row">
    <div class="flex-1">
      <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Cari nama atau email..." />
    </div>
    <flux:select wire:model.live="filterRole" class="sm:w-48">
      <option value="">Semua Role</option>
      @foreach ($roles as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
      @endforeach
    </flux:select>
  </div>

  {{-- Table --}}
  <flux:table :paginate="$users">
    <flux:table.columns>
      <flux:table.column>Pengguna</flux:table.column>
      <flux:table.column class="hidden sm:table-cell">Jabatan</flux:table.column>
      <flux:table.column class="hidden sm:table-cell">Role</flux:table.column>
      <flux:table.column class="hidden md:table-cell">Status</flux:table.column>
      <flux:table.column class="hidden lg:table-cell">Bergabung</flux:table.column>
      <flux:table.column align="end">Aksi</flux:table.column>
    </flux:table.columns>

    <flux:table.rows>
      @forelse ($users as $user)
        <flux:table.row :key="$user->id">
          {{-- Name + Email --}}
          <flux:table.cell>
            <div class="flex items-center gap-3">
              <flux:avatar circle :name="$user->name" :initials="$user->initials()" :src="$user->avatar"
                size="sm" />
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                  <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
                  @if ($user->id === auth()->id())
                    <flux:badge size="sm" color="indigo" inset="top bottom">Anda</flux:badge>
                  @endif
                </div>
                <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                <div class="mt-1.5 flex flex-wrap items-center gap-1.5 sm:hidden">
                  @php $role = strtolower(trim($user->role)); @endphp
                  @if ($role === 'super_user')
                    <flux:badge color="rose" size="sm" icon="star">Super User</flux:badge>
                  @elseif ($role === 'administrator')
                    <flux:badge color="amber" size="sm" icon="shield-check">Administrator</flux:badge>
                  @elseif ($role === 'manager')
                    <flux:badge color="blue" size="sm" icon="briefcase">Manager</flux:badge>
                  @elseif ($role === 'member')
                    <flux:badge color="yellow" size="sm" icon="clock">Member</flux:badge>
                  @else
                    <flux:badge color="zinc" size="sm" icon="user">Guest/Pending</flux:badge>
                  @endif

                  @if ($user->position)
                    <flux:text variant="subtle" class="text-xs">· {{ $user->position }}</flux:text>
                  @endif
                </div>
              </div>
            </div>
          </flux:table.cell>

          <flux:table.cell class="hidden sm:table-cell">
            <flux:text variant="subtle">{{ $user->position ?? '—' }}</flux:text>
          </flux:table.cell>

          <flux:table.cell class="hidden sm:table-cell">
            @php $role = strtolower(trim($user->role)); @endphp
            @if ($role === 'super_user')
              <flux:badge color="rose" size="sm" icon="star">Super User</flux:badge>
            @elseif ($role === 'administrator')
              <flux:badge color="amber" size="sm" icon="shield-check">Administrator</flux:badge>
            @elseif ($role === 'manager')
              <flux:badge color="blue" size="sm" icon="briefcase">Manager</flux:badge>
            @elseif ($role === 'member')
              <flux:badge color="yellow" size="sm" icon="clock">Member</flux:badge>
            @else
              <flux:badge color="zinc" size="sm" icon="user">Guest/Pending</flux:badge>
            @endif
          </flux:table.cell>

          <flux:table.cell class="hidden md:table-cell">
            <div class="flex flex-wrap items-center gap-1.5">
              @if ($user->email_verified_at)
                <flux:badge color="green" size="sm" icon="check-circle">Terverifikasi</flux:badge>
              @else
                <flux:badge color="red" size="sm" icon="x-circle">Belum verifikasi</flux:badge>
              @endif
              @if ($user->google_id)
                <flux:badge color="blue" size="sm" icon="globe-alt">Google</flux:badge>
              @endif
            </div>
          </flux:table.cell>

          <flux:table.cell class="hidden lg:table-cell">
            <flux:text variant="subtle" class="whitespace-nowrap text-xs"
              title="{{ $user->created_at->format('d M Y H:i') }}">
              {{ $user->created_at->format('d M Y') }}
            </flux:text>
          </flux:table.cell>

          <flux:table.cell align="end">
            @can('manage-users')
              <flux:dropdown position="bottom" align="end">
                <flux:button icon="ellipsis-horizontal" size="sm" variant="ghost" inset="top bottom" />
                <flux:menu>
                  <flux:menu.item icon="pencil-square" wire:click="editUser({{ $user->id }})">Edit</flux:menu.item>
                  @if ($user->id !== auth()->id() && $user->id !== \App\Models\User::min('id'))
                    <flux:menu.separator />
                    <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $user->id }})">Hapus
                    </flux:menu.item>
                  @endif
                </flux:menu>
              </flux:dropdown>
            @endcan
          </flux:table.cell>
        </flux:table.row>
      @empty
        <flux:table.row>
          <flux:table.cell colspan="6" class="py-12 text-center">
            <div class="flex flex-col items-center gap-2">
              <flux:icon name="users" class="size-10 text-zinc-300 dark:text-zinc-600" />
              <flux:text variant="subtle">
                @if ($search || $filterRole)
                  Tidak ada pengguna yang cocok dengan filter.
                @else
                  Belum ada pengguna.
                @endif
              </flux:text>
            </div>
          </flux:table.cell>
        </flux:table.row>
      @endforelse
    </flux:table.rows>
  </flux:table>

  {{-- Create Modal --}}
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

  {{-- Edit Modal --}}
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
                <flux:button size="sm" variant="ghost" icon="arrow-uturn-left"
                  wire:click="clearEditPositionCustom" title="Pilih dari daftar" />
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

  {{-- Delete Confirm Modal --}}
  <flux:modal wire:model="showDeleteConfirm" class="w-full max-w-sm">
    <div class="space-y-4 text-center">
      <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
        <flux:icon name="exclamation-triangle" class="size-6 text-red-600 dark:text-red-400" />
      </div>
      <flux:heading size="lg">Hapus Pengguna?</flux:heading>
      <flux:text variant="subtle">
        Tindakan ini tidak dapat dibatalkan. Semua data terkait pengguna ini akan terpengaruh.
      </flux:text>
      <div class="flex justify-center gap-2 pt-2">
        <flux:button variant="ghost" wire:click="$set('showDeleteConfirm', false)">Batal</flux:button>
        <flux:button variant="danger" wire:click="deleteUser">Hapus Pengguna</flux:button>
      </div>
    </div>
  </flux:modal>
</div>
