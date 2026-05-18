@props([
    'icon',
    'iconBgClass' => 'bg-zinc-100 dark:bg-zinc-500/20',
    'iconClass' => 'size-9 text-zinc-400 dark:text-zinc-500',
    'heading',
    'subheading' => null,
])

<div
  class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-zinc-200 bg-zinc-50/50 py-20 dark:border-zinc-700 dark:bg-zinc-800/20">
  <div class="mb-4 rounded-full {{ $iconBgClass }} p-4">
    <flux:icon :name="$icon" class="{{ $iconClass }}" />
  </div>
  <flux:heading size="lg">{{ $heading }}</flux:heading>
  @if ($subheading)
    <flux:subheading class="mt-1">{{ $subheading }}</flux:subheading>
  @endif
  @if ($slot->isNotEmpty())
    <div class="mt-6 flex gap-2">
      {{ $slot }}
    </div>
  @endif
</div>
