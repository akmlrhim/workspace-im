@props([
    'model',
    'selectedClass' =>
        'border-zinc-900 bg-zinc-100 text-zinc-900 dark:border-zinc-400 dark:bg-zinc-700 dark:text-white',
])

<div>
  <flux:label class="mb-2">Ikon</flux:label>
  <div class="flex flex-wrap gap-1.5">
    @foreach (['folder', 'squares-2x2', 'briefcase', 'rocket-launch', 'star', 'bolt', 'fire', 'globe-alt', 'heart', 'cube'] as $icon)
      <button type="button" @click="{{ $model }} = '{{ $icon }}'"
        class="flex h-9 w-9 items-center justify-center rounded-lg border transition-colors"
        :class="{{ $model }} === '{{ $icon }}' ?
            '{{ $selectedClass }}' :
            'border-zinc-200 text-zinc-500 hover:border-zinc-300 dark:border-zinc-700 dark:text-zinc-400'">
        <flux:icon name="{{ $icon }}" class="size-4" />
      </button>
    @endforeach
  </div>
</div>
