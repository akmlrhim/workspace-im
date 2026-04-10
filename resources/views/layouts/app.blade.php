<x-layouts::app.sidebar :title="$title ?? null">
  <flux:main class="overflow-y-auto">
    {{ $slot }}
  </flux:main>
</x-layouts::app.sidebar>
