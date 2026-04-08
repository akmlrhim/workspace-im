<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Roles & Permissions') }}</flux:heading>

    <x-settings.layout :heading="__('Roles & Permissions')" :subheading="__('Kelola role dan hak akses pengguna sistem')" :wide="true">
        <div class="space-y-6">
          <div class="flex items-center justify-between">
            <div></div>
            <flux:button wire:click="openForm" variant="primary" icon="plus">
              Tambah Role
            </flux:button>
          </div>

          <!-- Search -->
          <flux:input type="search" wire:model.live="search" placeholder="Cari role..." icon="magnifying-glass" />

          <!-- Form Modal -->
          <flux:modal wire:model="showForm">
            <flux:heading>{{ $editingRole ? 'Edit Role' : 'Tambah Role Baru' }}</flux:heading>

            <div class="space-y-4 py-4">
              <flux:field>
                <flux:label>Nama Role</flux:label>
                <flux:input type="text" wire:model="name" placeholder="Contoh: manager" />
                <flux:description>Gunakan huruf kecil, angka dan tanda hubung saja</flux:description>
                <flux:error name="name" />
              </flux:field>

              <flux:field>
                <flux:label>Nama Tampilan</flux:label>
                <flux:input type="text" wire:model="displayName" placeholder="Contoh: Manager" />
                <flux:error name="displayName" />
              </flux:field>

              <flux:field>
                <flux:label>Deskripsi</flux:label>
                <flux:textarea wire:model="description" placeholder="Deskripsi role..." rows="3" />
                <flux:error name="description" />
              </flux:field>

              <flux:field>
                <flux:checkbox wire:model="isDefault" label="Set sebagai role default" />
              </flux:field>
            </div>

            <div class="flex gap-2 justify-end">
              <flux:button wire:click="closeForm" variant="ghost">
                Batal
              </flux:button>
              <flux:button wire:click="save" variant="primary">
                {{ $editingRole ? 'Update' : 'Buat' }}
              </flux:button>
            </div>
          </flux:modal>

          <!-- Roles Table -->
          <div class="bg-white dark:bg-zinc-900 rounded-lg shadow overflow-hidden">
            <table class="w-full divide-y divide-gray-200 dark:divide-zinc-700">
              <thead class="bg-gray-50 dark:bg-zinc-800">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase">
                    Nama Role
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase">
                    Deskripsi
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase">
                    Permission
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase">
                    Status
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-zinc-400 uppercase">
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-zinc-700">
                @forelse ($roles as $role)
                  <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ $role->display_name }}
                      </div>
                      <div class="text-xs text-gray-500 dark:text-zinc-400">
                        {{ $role->name }}
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <p class="text-sm text-gray-600 dark:text-zinc-400 line-clamp-2">
                        {{ $role->description ?? '-' }}
                      </p>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <a href="{{ route('settings.roles.permissions', $role) }}"
                        class="inline-flex items-center gap-1 text-sm text-blue-600 dark:text-blue-400 hover:underline" wire:navigate>
                        <flux:icon icon="shield-check" variant="micro" />
                        {{ $role->permissions->count() }} permission
                      </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex gap-1">
                        @if ($role->is_default)
                          <flux:badge color="blue">Default</flux:badge>
                        @endif
                        @if ($role->name === 'super-admin')
                          <flux:badge color="red">System</flux:badge>
                        @endif
                      </div>
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                      <flux:dropdown placement="bottom-end">
                        <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="right" />

                        <flux:menu>
                          <flux:menu.item :href="route('settings.roles.permissions', $role)" wire:navigate>
                            <flux:icon icon="shield-check" variant="micro" />
                            Kelola Permission
                          </flux:menu.item>

                          <flux:menu.item wire:click="openForm(@js($role))">
                            <flux:icon icon="pencil" variant="micro" />
                            Edit
                          </flux:menu.item>

                          @if ($role->name !== 'super-admin' && !$role->is_default)
                            <flux:menu.item wire:click="delete(@js($role->id))"
                              wire:confirm="Yakin ingin menghapus role ini?" class="text-red-600">
                              <flux:icon icon="trash" variant="micro" />
                              Hapus
                            </flux:menu.item>
                          @endif
                        </flux:menu>
                      </flux:dropdown>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-zinc-400">
                      Belum ada role
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="flex justify-center">
            {{ $roles->links() }}
          </div>
        </div>
    </x-settings.layout>
</section>
