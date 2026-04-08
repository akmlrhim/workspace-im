<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
  @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800 antialiased overflow-x-hidden">
  @php
    $modules = config('erp.modules', []);

    $currentModuleKey = 'project';
    $path = request()->path();

    foreach ($modules as $key => $module) {
        $modulePath = ltrim($module['url'], '/');
        if ($path === $modulePath || str_starts_with($path, $modulePath . '/')) {
            $currentModuleKey = $key;
            break;
        }
    }

    $currentModule = $modules[$currentModuleKey] ?? null;
  @endphp

  <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.header>
      <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
      <flux:sidebar.collapse />
    </flux:sidebar.header>

    <flux:sidebar.nav>
      @if ($currentModule)
        @foreach ($currentModule['sidebar'] as $item)
          <flux:sidebar.item :icon="$item['icon']" :href="url($item['url'])" :badge="$item['badge']"
            :current="request()->is($item['active'])" wire:navigate>
            {{ $item['name'] }}
          </flux:sidebar.item>
        @endforeach

        @if (!empty($currentModule['favorites']))
          <flux:sidebar.group expandable heading="Favorites" class="grid">
            @foreach ($currentModule['favorites'] as $fav)
              <flux:sidebar.item :href="url($fav['url'])" wire:navigate>{{ $fav['name'] }}</flux:sidebar.item>
            @endforeach
          </flux:sidebar.group>
        @endif

        @if ($currentModuleKey === 'project' && auth()->check())
          @php
            $userId = auth()->id();
            $sidebarSpaces = \App\Models\Project\Space::whereHas('workspace', function ($query) use ($userId) {
                $query->where('owner_id', $userId);
            })
                ->orWhereHas('lists.tasks.assignees', function ($query) use ($userId) {
                    $query->where('users.id', $userId);
                })
                ->orWhereHas('lists.tasks', function ($query) use ($userId) {
                    $query->where('assigned_to', $userId);
                })
                ->with('lists')
                ->get();
          @endphp

          @if ($sidebarSpaces->isNotEmpty())
            <flux:separator class="my-2" />

            @foreach ($sidebarSpaces as $sidebarSpace)
              @php
                $isSpaceActive = request()->is('project-management/spaces/' . $sidebarSpace->id . '*');
              @endphp

              <flux:sidebar.group expandable :expanded="$isSpaceActive" class="grid">

                <x-slot:heading>
                    <a href="{{ route('project-management.spaces.show', $sidebarSpace->id) }}" wire:navigate
                      class="block w-full font-semibold text-zinc-900 dark:text-white">
                      {{ $sidebarSpace->name }}
                    </a>
                  </div>
                </x-slot:heading>

                @foreach ($sidebarSpace->lists as $sidebarList)
                  <flux:sidebar.item :href="route('project-management.lists.show', [$sidebarSpace, $sidebarList])"
                    :current="request()->is('project-management/spaces/' . $sidebarSpace->id . '/lists/' . $sidebarList->id . '*')"
                    wire:navigate>
                    {{ $sidebarList->name }}
                  </flux:sidebar.item>
                @endforeach

              </flux:sidebar.group>
            @endforeach
          @endif
        @endif
      @endif
    </flux:sidebar.nav>

    <flux:spacer />

    <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
  </flux:sidebar>

  <flux:header class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    <flux:navbar scrollable class="w-full">
      <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

      @foreach ($modules as $key => $module)
        <flux:navbar.item :icon="$module['icon']" :href="url($module['url'])" :current="$currentModuleKey === $key" wire:navigate>
          {{ $module['name'] }}
        </flux:navbar.item>
      @endforeach
    </flux:navbar>
  </flux:header>

  {{ $slot }}

  <flux:toast />

  @fluxScripts
</body>

</html>
