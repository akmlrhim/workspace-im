<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
  @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
  <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
    <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

    <x-app-logo href="{{ route('project-management.general-taskboard') }}" wire:navigate />

    @php
      $modules = config('erp.modules', []);
      $currentModuleKey = null;
      if (request()->routeIs('project-management.*')) {
          $currentModuleKey = 'project';
      } elseif (request()->routeIs('users.*')) {
          $currentModuleKey = 'users';
      }
      $currentModuleKey ??= 'project';
    @endphp

    <flux:navbar class="-mb-px max-lg:hidden">
      @foreach ($modules as $key => $module)
        <flux:navbar.item :icon="$module['icon']" :href="url($module['url'])" :current="$currentModuleKey === $key"
          wire:navigate>
          {{ $module['name'] }}
        </flux:navbar.item>
      @endforeach
    </flux:navbar>

    <flux:spacer />

  </flux:header>

  {{ $slot }}

  @fluxScripts
</body>

</html>
