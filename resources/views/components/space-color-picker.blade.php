@props(['model'])

<div>
  <flux:label class="mb-2">Warna</flux:label>
  <div class="flex flex-wrap gap-2">
    @foreach (['#6366f1', '#8b5cf6', '#ec4899', '#ef4444', '#f97316', '#f59e0b', '#10b981', '#14b8a6', '#06b6d4', '#3b82f6'] as $color)
      <button type="button" @click="{{ $model }} = '{{ $color }}'"
        class="h-8 w-8 rounded-full transition-all hover:scale-110"
        :class="{{ $model }} === '{{ $color }}' ?
            'ring-2 ring-offset-2 ring-zinc-900 scale-110 dark:ring-white dark:ring-offset-zinc-900' : ''"
        style="background-color: {{ $color }}"></button>
    @endforeach
  </div>
</div>
