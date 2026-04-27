<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
  @include('partials.head')
</head>

<body class="h-dvh bg-white dark:bg-zinc-800 antialiased overflow-hidden">
  @php
    $modules = config('erp.modules', []);
    $path = request()->path();
    $isGuestUser = auth()->check() && auth()->user()->isGuest();

    $currentModuleKey = request()->query('module');

    if (!$currentModuleKey) {
        foreach ($modules as $key => $module) {
            $modulePath = ltrim($module['url'], '/');
            if ($path === $modulePath || str_starts_with($path, $modulePath . '/')) {
                $currentModuleKey = $key;
                break;
            }
        }
    }

    $currentModuleKey ??= $isGuestUser ? null : 'project';
    $currentModule = $currentModuleKey ? $modules[$currentModuleKey] ?? null : null;
  @endphp

  <flux:sidebar collapsible
    class="flex flex-col border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 sticky top-0 h-dvh data-flux-sidebar-on-mobile:z-50! data-flux-sidebar-on-mobile:bg-zinc-50! dark:data-flux-sidebar-on-mobile:bg-zinc-900!">

    <flux:sidebar.header class="flex items-center justify-between pb-4">
      <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
      <flux:sidebar.collapse />
    </flux:sidebar.header>

    <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden custom-scrollbar bg-zinc-50 dark:bg-zinc-900">
      <flux:sidebar.nav>

        @if ($isGuestUser)
          <flux:sidebar.item icon="squares-2x2" :href="route('dashboard')" :current="request()->is('dashboard')"
            wire:navigate>
            {{ __('Dashboard') }}
          </flux:sidebar.item>
        @elseif ($currentModule)
          <div class="mb-4">
            @foreach ($currentModule['sidebar'] as $item)
              <flux:sidebar.item :icon="$item['icon']" :href="url($item['url'])" :badge="$item['badge']"
                :current="request()->is($item['active'])" wire:navigate>
                {{ $item['name'] }}
              </flux:sidebar.item>
            @endforeach
          </div>

          @if ($currentModuleKey === 'project' && auth()->check())
            <div class="mt-2 px-3 text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">
              Workspace
            </div>
            <livewire:project.sidebar-spaces />
          @endif

        @endif

      </flux:sidebar.nav>
    </div>

    <div class="shrink-0 mt-auto border-t border-zinc-200 bg-zinc-50 pt-4 dark:border-zinc-700 dark:bg-zinc-900">
      <x-desktop-user-menu class="w-full" :name="auth()->check() ? auth()->user()->name : 'Guest'" />
    </div>

  </flux:sidebar>


  <flux:header
    class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 sticky top-0 z-40">
    <flux:navbar scrollable class="w-full flex items-center">

      <flux:sidebar.toggle class="lg:hidden mr-4" icon="bars-2" inset="left" />

      <div class="flex items-center gap-1">
        @foreach ($modules as $key => $module)
          @php
            $allowedPositions = $module['allowed_positions'] ?? [];
            // Logika disederhanakan agar lebih mudah dibaca
            $hasModuleAccess =
                auth()->check() &&
                (empty($allowedPositions) ||
                    auth()->user()->isAdmin() ||
                    in_array(auth()->user()->position, $allowedPositions));
          @endphp

          @if ($hasModuleAccess)
            <flux:navbar.item :icon="$module['icon']" :href="url($module['url'])" :current="$currentModuleKey === $key"
              wire:navigate class="transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-md">
              <span class="hidden sm:inline-block ml-1">{{ $module['name'] }}</span>
            </flux:navbar.item>
          @endif
        @endforeach
      </div>
    </flux:navbar>
  </flux:header>

  {{ $slot }}

  <flux:toast />

  @fluxScripts

  <script>
    (function() {
      let lastScrollY = 0;
      window.addEventListener('scroll', function() {
        lastScrollY = window.scrollY;
      }, {
        passive: true
      });

      const observer = new MutationObserver(function() {
        const isLocked = document.documentElement.style.overflow === 'hidden';
        if (!isLocked && lastScrollY > 0) {
          window.scrollTo({
            top: lastScrollY,
            behavior: 'instant'
          });
        }
      });
      observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['style']
      });
    })();
  </script>
</body>

</html>
