@props(['wide' => false])

<div class="flex items-start max-md:flex-col">
    <div class="me-10 w-full pb-4 md:w-[220px]">
        <flux:navlist aria-label="{{ __('Settings') }}">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>

            @can('settings.roles.view')
                <flux:navlist.group heading="{{ __('Access Control') }}" expandable>
                    <flux:navlist.item :href="route('settings.roles')" wire:navigate>{{ __('Roles & Permissions') }}</flux:navlist.item>
                    <flux:navlist.item :href="route('settings.users.roles')" wire:navigate>{{ __('User Roles') }}</flux:navlist.item>
                </flux:navlist.group>
            @endcan
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full {{ $wide ? '' : 'max-w-lg' }}">
            {{ $slot }}
        </div>
    </div>
</div>


