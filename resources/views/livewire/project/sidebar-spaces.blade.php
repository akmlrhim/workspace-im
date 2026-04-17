<div>
  @if ($sidebarSpaces->isNotEmpty())
    <flux:separator class="my-2" />

    @foreach ($sidebarSpaces as $sidebarSpace)
      @php
        $spacePath = 'project-management/spaces/' . $sidebarSpace->uuid;
      @endphp

      <flux:sidebar.group expandable :expanded="request()->is($spacePath . '*')" class="grid"
        wire:key="sidebar-space-{{ $sidebarSpace->id }}">
        <x-slot:heading>
          <a href="{{ route('project-management.spaces.show', $sidebarSpace) }}" wire:navigate
            class="block w-full font-semibold text-zinc-900 dark:text-white hover:underline">
            {{ $sidebarSpace->name }}
          </a>
        </x-slot:heading>

        @foreach ($sidebarSpace->lists as $sidebarList)
          <flux:sidebar.item :href="route('project-management.lists.board', [$sidebarSpace, $sidebarList])"
            :current="request()->is($spacePath . '/lists/' . $sidebarList->uuid . '*')" wire:navigate
            wire:key="sidebar-list-{{ $sidebarList->id }}">
            {{ $sidebarList->name }}
          </flux:sidebar.item>
        @endforeach
      </flux:sidebar.group>
    @endforeach
  @endif
</div>
