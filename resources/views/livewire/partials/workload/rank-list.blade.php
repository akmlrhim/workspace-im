<div
  class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
  <div class="border-b border-zinc-100 px-5 py-3 dark:border-zinc-700">
    <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">Peringkat Selanjutnya</p>
  </div>
  <div class="divide-y divide-zinc-50 dark:divide-zinc-800">
    @foreach ($memberStats->slice(3) as $member)
      @php $allDone = $member['total'] > 0 && $member['progress'] >= 100; @endphp
      <div class="flex items-center gap-3 px-5 py-3.5 transition hover:bg-zinc-50/60 dark:hover:bg-zinc-800/40"
        wire:key="rank-{{ $member['user']->id }}">

        <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
          <span
            class="text-xs font-bold text-zinc-500 dark:text-zinc-400">#{{ $loop->index + 4 }}</span>
        </div>

        <flux:avatar circle size="sm" :name="$member['user']->name" :initials="$member['user']->initials()"
          :src="$member['user']->avatar" />

        <div class="min-w-0 flex-1">
          <p
            class="truncate text-sm font-medium {{ $allDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-zinc-800 dark:text-zinc-100' }}">
            {{ $member['user']->name }}
            @if ($allDone)
              <span
                class="ml-1 inline-flex items-center gap-0.5 rounded-full bg-emerald-100 px-1.5 py-0.5 text-[9px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                <svg class="size-2.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M20 6 9 17l-5-5" />
                </svg>
                Clear</span>
            @endif
          </p>
        </div>

        <div class="hidden sm:flex w-36 shrink-0 items-center gap-2">
          <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
            <div
              class="h-full rounded-full {{ $allDone ? 'bg-emerald-500' : ($member['progress'] >= 70 ? 'bg-teal-500' : ($member['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500')) }}"
              style="width: {{ $member['progress'] }}%"></div>
          </div>
          <span
            class="w-8 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $member['progress'] }}%</span>
        </div>

        <div class="hidden md:block w-20 shrink-0 text-right">
          <p class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
            {{ $member['completed'] }}/{{ $member['total'] }}</p>
          <p class="text-[10px] text-zinc-400">selesai</p>
        </div>

        <div class="hidden md:flex w-20 shrink-0 justify-end">
          @if ($member['overdue'] > 0)
            <flux:badge color="red" size="sm">{{ $member['overdue'] }} terlambat</flux:badge>
          @else
            <span class="text-xs text-zinc-300 dark:text-zinc-600">–</span>
          @endif
        </div>

      </div>
    @endforeach
  </div>
</div>
