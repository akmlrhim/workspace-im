@props([
    'expandable' => false,
    'expanded' => true,
    'heading' => null,
    'href' => null,
])

<?php if ($expandable && $heading): ?>

<ui-disclosure {{ $attributes->class('group/disclosure') }} @if ($expanded === true) open @endif
  data-flux-navlist-group>
  > <div
    class="mb-[2px] flex h-10 w-full items-center rounded-lg text-zinc-500 hover:bg-zinc-800/5 hover:text-zinc-800 lg:h-8 dark:text-white/80 dark:hover:bg-white/[7%] dark:hover:text-white">

    <button type="button" class="group/disclosure-button flex h-full items-center ps-3 pe-2">
      <flux:icon.chevron-down class="hidden size-3! group-data-open/disclosure-button:block" />
      <flux:icon.chevron-right class="block size-3! group-data-open/disclosure-button:hidden" />
    </button>
    @if ($href)
      <a href="{{ $href }}" wire:navigate class="flex-1 h-full flex items-center pe-4">
        <span class="text-sm font-medium leading-none">{{ $heading }}</span>
      </a>
    @else
      <button type="button" class="group/disclosure-button flex-1 h-full flex items-center pe-4 text-left">
        <span class="text-sm font-medium leading-none">{{ $heading }}</span>
      </button>
    @endif

  </div>

  <div class="relative hidden space-y-[2px] ps-7 data-open:block" @if ($expanded === true) data-open @endif>
    <div class="absolute inset-y-[3px] start-0 ms-4 w-px bg-zinc-200 dark:bg-white/30"></div>

    {{ $slot }}
  </div>
</ui-disclosure>

<?php elseif ($heading): ?>

<div {{ $attributes->class('block space-y-[2px]') }}>
  <div class="px-1 py-2">
    <div class="text-xs leading-none text-zinc-400">{{ $heading }}</div>
  </div>

  <div>
    {{ $slot }}
  </div>
</div>

<?php else: ?>

<div {{ $attributes->class('block space-y-[2px]') }}>
  {{ $slot }}
</div>

<?php endif; ?>
