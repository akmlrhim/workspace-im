<x-layouts::app :title="__('Dashboard')">
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
        Selamat datang, {{ auth()->user()->name }}
      </h1>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
        Pilih modul yang ingin Anda akses hari ini.
      </p>
    </div>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
      @foreach (config('erp.modules', []) as $key => $module)
        @php
          $allowedPositions = $module['allowed_positions'] ?? [];
          $hasModuleAccess = empty($allowedPositions) || auth()->user()->isAdmin() || in_array(auth()->user()->position, $allowedPositions);
          $color = $module['color'] ?? 'indigo';
          
          $colorClasses = [
              'indigo' => [
                  'bg' => 'bg-indigo-50 dark:bg-indigo-500/10',
                  'hoverBg' => 'group-hover:bg-indigo-100 dark:group-hover:bg-indigo-500/20',
                  'text' => 'text-indigo-500',
                  'hugeText' => 'text-indigo-600'
              ],
              'emerald' => [
                  'bg' => 'bg-emerald-50 dark:bg-emerald-500/10',
                  'hoverBg' => 'group-hover:bg-emerald-100 dark:group-hover:bg-emerald-500/20',
                  'text' => 'text-emerald-500',
                  'hugeText' => 'text-emerald-600'
              ],
              'amber' => [
                  'bg' => 'bg-amber-50 dark:bg-amber-500/10',
                  'hoverBg' => 'group-hover:bg-amber-100 dark:group-hover:bg-amber-500/20',
                  'text' => 'text-amber-500',
                  'hugeText' => 'text-amber-600'
              ],
              'sky' => [
                  'bg' => 'bg-sky-50 dark:bg-sky-500/10',
                  'hoverBg' => 'group-hover:bg-sky-100 dark:group-hover:bg-sky-500/20',
                  'text' => 'text-sky-500',
                  'hugeText' => 'text-sky-600'
              ]
          ];
          $c = $colorClasses[$color] ?? $colorClasses['indigo'];
        @endphp

        @if ($hasModuleAccess)
          <a href="{{ url($module['url']) }}" wire:navigate
            class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
            <div
              class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl {{ $c['bg'] }} transition-colors {{ $c['hoverBg'] }}">
              <flux:icon name="{{ $module['icon'] }}" class="size-6 {{ $c['text'] }}" />
            </div>
            <h3 class="text-base font-semibold text-zinc-900 dark:text-white">{{ $module['name'] }}</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $module['description'] }}</p>
            <div class="pointer-events-none absolute right-4 top-4 opacity-5 transition-opacity group-hover:opacity-10">
              <flux:icon name="{{ $module['icon'] }}" class="size-24 {{ $c['hugeText'] }}" />
            </div>
          </a>
        @endif
      @endforeach
    </div>
  </div>
</x-layouts::app>
