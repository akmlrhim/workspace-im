<flux:dropdown position="top" align="start" x-on:scroll.window="$popover.close()">
  <flux:sidebar.profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
    :avatar="auth()->user()->avatar" icon:trailing="chevrons-up-down" data-test="sidebar-menu-button" />

  <flux:menu>
    <div class="flex items-center gap-2 px-2 py-1.5 text-start text-sm">
      <flux:avatar size="sm" :name="auth()->user()->name" :initials="auth()->user()->initials()"
        :src="auth()->user()->avatar" />
      <div class="grid flex-1 text-start text-sm leading-tight">
        <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
        <flux:text class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</flux:text>
      </div>
    </div>

    <flux:menu.separator />

    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
      {{ __('Settings') }}
    </flux:menu.item>

    <form method="POST" action="{{ route('logout') }}" class="block w-full m-0 p-0">
      @csrf
      <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full cursor-pointer"
        data-test="logout-button">
        {{ __('Log out') }}
      </flux:menu.item>
    </form>

  </flux:menu>
</flux:dropdown>
