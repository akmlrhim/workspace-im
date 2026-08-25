<div>
  @if ($sidebarSpaces->isNotEmpty())
    <flux:separator class="my-1" />

    @foreach ($sidebarSpaces as $sidebarSpace)
      @php
        $spacePath = 'spaces/' . $sidebarSpace->uuid;
      @endphp

      <flux:sidebar.group expandable :expanded="true" wire:key="sidebar-space-{{ $sidebarSpace->id }}">
        <x-slot:heading>
          <span class="uppercase font-bold text-base">{{ $sidebarSpace->name }}</span>
        </x-slot:heading>
        @foreach ($sidebarSpace->lists as $sidebarList)
          <flux:sidebar.item :href="route('lists.board', [$sidebarSpace, $sidebarList])"
            :current="request()->is($spacePath . '/lists/' . $sidebarList->uuid . '*')" wire:navigate
            wire:key="sidebar-list-{{ $sidebarList->id }}" :title="$sidebarList->name">
            {{ $sidebarList->name }}
          </flux:sidebar.item>
        @endforeach
      </flux:sidebar.group>
    @endforeach
  @endif
</div>
