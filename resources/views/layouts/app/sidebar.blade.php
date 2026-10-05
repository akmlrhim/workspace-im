<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
  @include('partials.head')
</head>

<body class="h-dvh bg-white dark:bg-zinc-800 antialiased overflow-hidden">
  @php
    $modules = config('project.modules', []);
    $isGuestUser = auth()->check() && auth()->user()->isGuest();

    $currentModuleKey = $isGuestUser ? null : 'project';
    $currentModule = $currentModuleKey ? $modules[$currentModuleKey] ?? null : null;
  @endphp

  <flux:sidebar collapsible
    class="flex flex-col border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 sticky top-0 h-dvh data-flux-sidebar-on-mobile:z-50! data-flux-sidebar-on-mobile:bg-zinc-50! dark:data-flux-sidebar-on-mobile:bg-zinc-900!">

    <flux:sidebar.header class="flex items-center justify-between pb-1">
      <x-app-logo :sidebar="true" href="{{ route('general-taskboard') }}" wire:navigate />
      <flux:sidebar.collapse />
    </flux:sidebar.header>

    <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden custom-scrollbar bg-zinc-50 dark:bg-zinc-900">
      <flux:sidebar.nav>

        @if ($isGuestUser)
          <flux:sidebar.item icon="squares-2x2" :href="route('profile.edit')"
            :current="request()->routeIs('profile.edit')" wire:navigate>
            {{ __('Profil Saya') }}
          </flux:sidebar.item>
        @elseif ($currentModule)
          <div class="mb-2">
            @foreach ($currentModule['sidebar'] as $item)
              <flux:sidebar.item :icon="$item['icon']" :href="url($item['url'])" :badge="$item['badge']"
                :current="request()->is($item['active'])" wire:navigate>
                {{ $item['name'] }}
              </flux:sidebar.item>
            @endforeach

            @if (auth()->check() && !auth()->user()->isGuest() && auth()->user()->role === 'super_user')
              <flux:sidebar.item icon="users" :href="route('users.index')"
                :current="request()->routeIs('users.*')" wire:navigate>
                {{ __('Users') }}
              </flux:sidebar.item>
            @endif
          </div>

          @if (auth()->check())
            <livewire:sidebar-spaces />
          @endif

        @endif

      </flux:sidebar.nav>
    </div>

  </flux:sidebar>

<flux:header
    class="bg-white lg:bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 sticky top-0 z-40">
    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

    <flux:spacer />

    @if (auth()->check())
        <div class="flex min-w-0 items-center gap-1 sm:gap-2">
            <livewire:navbar-notifications wire:poll.30s />

            <flux:dropdown position="bottom" align="end" teleport>
                <flux:button variant="ghost" class="cursor-pointer px-1.5 sm:px-2" aria-label="{{ __('Menu profil') }}"
                    data-test="navbar-user-menu-button">
                    <flux:avatar circle size="sm" :name="auth()->user()->name" :initials="auth()->user()->initials()"
                        :src="auth()->user()->avatar" />
                    <span class="hidden max-w-28 truncate text-sm font-medium sm:inline">{{ auth()->user()->name }}</span>
                    <flux:icon name="chevron-down" variant="micro" class="hidden sm:block" />
                </flux:button>

                <flux:menu class="w-56 sm:w-64">
                    <div class="flex items-center gap-2 px-2 py-1.5 text-start text-sm">
                        <flux:avatar circle size="sm" :name="auth()->user()->name" :initials="auth()->user()->initials()"
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
        </div>
    @endif
</flux:header>

  {{ $slot }}

  <flux:toast position="bottom end" expanded />

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
