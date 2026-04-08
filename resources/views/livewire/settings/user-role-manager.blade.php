<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Manajemen Role User') }}</flux:heading>

    <x-settings.layout :heading="__('User Roles')" :subheading="__('Assign role kepada user untuk mengatur hak akses mereka')" :wide="true">
        <div class="space-y-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Users List -->
            <div class="lg:col-span-1">
              <div class="bg-white dark:bg-zinc-900 rounded-lg shadow">
                <div class="p-4 border-b border-gray-200 dark:border-zinc-700">
                  <flux:input type="search" wire:model.live="search" placeholder="Cari user..." icon="magnifying-glass" />
                </div>

                <div class="divide-y divide-gray-200 dark:divide-zinc-700 max-h-[28rem] overflow-y-auto">
                  @forelse ($users as $user)
                    <button wire:click="selectUserForRoles(@js($user->id))"
                      class="w-full text-left p-4 hover:bg-blue-50 dark:hover:bg-zinc-800 transition {{ $selectedUser?->id === $user->id ? 'bg-blue-100 dark:bg-zinc-700' : '' }}">
                      <div class="font-medium text-gray-900 dark:text-white">
                        {{ $user->name }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-zinc-400">
                        {{ $user->email }}
                      </div>
                      <div class="flex flex-wrap gap-1 mt-2">
                        @forelse ($user->roles as $role)
                          <flux:badge size="sm" color="blue">{{ $role->display_name ?? $role->name }}</flux:badge>
                        @empty
                          <span class="text-xs text-gray-400 dark:text-zinc-500 italic">Belum ada role</span>
                        @endforelse
                      </div>
                    </button>
                  @empty
                    <div class="p-4 text-center text-gray-500 dark:text-zinc-400">
                      Belum ada user
                    </div>
                  @endforelse
                </div>

                <div class="p-4 border-t border-gray-200 dark:border-zinc-700">
                  {{ $users->links() }}
                </div>
              </div>
            </div>

            <!-- Role Assignment -->
            <div class="lg:col-span-2">
              @if ($selectedUser)
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6">
                  <div class="mb-6">
                    <flux:heading class="text-lg">Assign Role</flux:heading>
                    <p class="text-sm text-gray-600 dark:text-zinc-400">
                      {{ $selectedUser->name }} ({{ $selectedUser->email }})
                    </p>
                  </div>

                  <div class="space-y-3 mb-6">
                    @foreach ($roles as $role)
                      <label
                        class="flex items-start p-4 border rounded-lg cursor-pointer transition
                          {{ in_array($role->id, $selectedRoles)
                            ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700'
                            : 'border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800' }}">
                        <input type="checkbox" wire:model.live="selectedRoles" value="{{ $role->id }}"
                          class="mt-1 rounded border-gray-300 dark:border-zinc-600 text-blue-600 dark:text-blue-500 focus:ring-2 focus:ring-blue-500" />

                        <div class="ml-3 flex-1">
                          <div class="font-medium text-gray-900 dark:text-white">
                            {{ $role->display_name }}
                          </div>
                          <div class="text-sm text-gray-500 dark:text-zinc-400">
                            {{ $role->description }}
                          </div>
                          <div class="flex gap-2 mt-2">
                            @if ($role->is_default)
                              <flux:badge color="blue" size="sm">Default</flux:badge>
                            @endif
                            <flux:badge color="zinc" size="sm">{{ $role->permissions->count() }} permission</flux:badge>
                          </div>
                        </div>
                      </label>
                    @endforeach
                  </div>

                  <div class="flex gap-3 justify-end">
                    <flux:button wire:click="deselectUser" variant="ghost">
                      Batal
                    </flux:button>
                    <flux:button wire:click="saveRoles" variant="primary">
                      Simpan Role
                    </flux:button>
                  </div>
                </div>
              @else
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-12 text-center">
                  <flux:icon icon="users" class="w-12 h-12 text-gray-400 dark:text-zinc-600 mx-auto mb-4" variant="outline" />
                  <p class="text-gray-600 dark:text-zinc-400">
                    Pilih user dari daftar di sebelah untuk manage role mereka
                  </p>
                </div>
              @endif
            </div>
          </div>
        </div>
    </x-settings.layout>
</section>
