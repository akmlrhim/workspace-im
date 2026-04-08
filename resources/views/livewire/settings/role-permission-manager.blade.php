<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Kelola Permission Role') }}</flux:heading>

    <x-settings.layout :heading="__('Kelola Permission')" :subheading="__('Tentukan permission yang dimiliki oleh role ' . $role->display_name)" :wide="true">
        <div class="space-y-6">
          <div class="flex items-center justify-between">
            <div class="flex gap-2">
              <flux:button wire:click="selectAll" variant="ghost" size="sm" icon="check-circle">
                Pilih Semua
              </flux:button>
              <flux:button wire:click="deselectAll" variant="ghost" size="sm" icon="x-circle">
                Hapus Semua
              </flux:button>
            </div>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
              <span class="font-semibold text-zinc-900 dark:text-white">{{ count($selectedPermissions) }}</span> permission dipilih
            </p>
          </div>

          <!-- Filters -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:select wire:model.live="filterModule" placeholder="Filter by Module">
              <option value="">Semua Module</option>
              @foreach ($modules as $module)
                <option value="{{ $module }}">{{ ucfirst($module) }}</option>
              @endforeach
            </flux:select>

            <flux:select wire:model.live="filterGroup" placeholder="Filter by Group">
              <option value="">Semua Group</option>
              @foreach ($groups as $group)
                <option value="{{ $group }}">{{ $group }}</option>
              @endforeach
            </flux:select>
          </div>

          <!-- Permissions -->
          <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6">
            @if ($permissions->isEmpty())
              <p class="text-gray-500 dark:text-zinc-400 text-center py-8">
                Tidak ada permission yang sesuai dengan filter
              </p>
            @else
              <div class="space-y-6">
                @php
                  $groupedPermissions = $permissions->groupBy('group');
                @endphp

                @foreach ($groupedPermissions as $group => $perms)
                  <div class="border-t border-gray-200 dark:border-zinc-700 pt-4 first:border-t-0 first:pt-0">
                    <div class="flex items-center justify-between mb-3">
                      <h3 class="font-semibold text-gray-900 dark:text-white">{{ $group }}</h3>
                      <flux:badge color="zinc" size="sm">{{ $perms->count() }} permission</flux:badge>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      @foreach ($perms as $permission)
                        <label
                          class="flex items-start p-3 border rounded-lg cursor-pointer transition
                            {{ in_array($permission->id, $selectedPermissions)
                              ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700'
                              : 'border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
                          <input type="checkbox" wire:model.live="selectedPermissions" value="{{ $permission->id }}"
                            class="mt-1 rounded border-gray-300 dark:border-zinc-600 text-blue-600 dark:text-blue-500 focus:ring-2 focus:ring-blue-500" />

                          <div class="ml-3 flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">
                              {{ $permission->display_name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-zinc-400">
                              {{ $permission->name }}
                            </div>
                            @if ($permission->description)
                              <p class="text-xs text-gray-600 dark:text-zinc-500 mt-1">
                                {{ $permission->description }}
                              </p>
                            @endif
                          </div>
                        </label>
                      @endforeach
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>

          <!-- Actions -->
          <div class="flex gap-3 justify-end">
            <flux:button variant="ghost" :href="route('settings.roles')" wire:navigate>
              Kembali
            </flux:button>
            <flux:button variant="primary" wire:click="save">
              Simpan Permission
            </flux:button>
          </div>
        </div>
    </x-settings.layout>
</section>
