<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
  @include('partials.head')
</head>

<body class="h-dvh bg-white dark:bg-zinc-800 antialiased overflow-hidden">
  @php
    $modules = config('erp.modules', []);
    $path = request()->path();

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

    $currentModuleKey ??= 'project';
    $currentModule = $modules[$currentModuleKey] ?? null;
  @endphp

  <flux:sidebar collapsible
    class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 sticky top-0 h-dvh flex flex-col">

    <flux:sidebar.header>
      <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
      <flux:sidebar.collapse />
    </flux:sidebar.header>

    <div class="flex-1 min-h-0 overflow-y-auto">
      <flux:sidebar.nav>

        @if ($currentModule)
          @foreach ($currentModule['sidebar'] as $item)
            <flux:sidebar.item :icon="$item['icon']" :href="url($item['url'])" :badge="$item['badge']"
              :current="request()->is($item['active'])" wire:navigate>
              {{ $item['name'] }}
            </flux:sidebar.item>
          @endforeach

          @if ($currentModuleKey === 'project' && auth()->check())
            <livewire:project.sidebar-spaces />
          @endif
        @endif
      </flux:sidebar.nav>
    </div>

    <div class="shrink-0 mt-auto">
      <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
    </div>

  </flux:sidebar>

  <flux:header class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
    <flux:navbar scrollable class="w-full">
      <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

      @foreach ($modules as $key => $module)
        @php
          $allowedPositions = $module['allowed_positions'] ?? [];
          $hasModuleAccess =
              !auth()->user()->isGuest() &&
              (empty($allowedPositions) ||
                  auth()->user()->isAdmin() ||
                  in_array(auth()->user()->position, $allowedPositions));
        @endphp
        @if ($hasModuleAccess)
          <flux:navbar.item :icon="$module['icon']" :href="url($module['url'])" :current="$currentModuleKey === $key"
            wire:navigate>
            <span class="hidden sm:inline">{{ $module['name'] }}</span>
          </flux:navbar.item>
        @endif
      @endforeach
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
