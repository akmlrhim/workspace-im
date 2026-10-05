<flux:table :paginate="$users">
  <flux:table.columns>
    <flux:table.column>Pengguna</flux:table.column>
    <flux:table.column>Jabatan</flux:table.column>
    <flux:table.column>Role</flux:table.column>
    <flux:table.column>Status</flux:table.column>
    <flux:table.column>Bergabung</flux:table.column>
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
            </div>
          </div>
        </flux:table.cell>

        <flux:table.cell>
          <flux:text variant="subtle">{{ $user->position ?? '—' }}</flux:text>
        </flux:table.cell>

        <flux:table.cell>
          @php $role = strtolower(trim($user->role)); @endphp
          @if ($role === 'super_user')
            <flux:badge color="rose" size="sm" icon="star">Super User</flux:badge>
          @elseif ($role === 'administrator')
            <flux:badge color="amber" size="sm" icon="shield-check">Administrator</flux:badge>
          @elseif ($role === 'manager')
            <flux:badge color="blue" size="sm" icon="briefcase">Manager</flux:badge>
          @elseif ($role === 'member')
            <flux:badge color="yellow" size="sm" icon="users">Member</flux:badge>
          @else
            <flux:badge color="zinc" size="sm" icon="clock">Guest/Pending</flux:badge>
          @endif
        </flux:table.cell>

        <flux:table.cell>
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

        <flux:table.cell>
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
                @if ($user->id !== auth()->id())
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
