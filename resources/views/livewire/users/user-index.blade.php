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
  @include('livewire.users.partials.user-table')

  {{-- Create Modal --}}
  @include('livewire.users.partials.create-modal')

  {{-- Edit Modal --}}
  @include('livewire.users.partials.edit-modal')

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
