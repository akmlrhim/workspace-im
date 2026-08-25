@php
  $topMembers = $memberStats->take(3);
  $topCount = $topMembers->count();
  $podiumGridClass = match (true) {
      $topCount === 1 => 'grid-cols-1 max-w-xs',
      $topCount === 2 => 'grid-cols-2',
      default => 'grid-cols-1 sm:grid-cols-3',
  };
  $medalColors = [
      ['body' => '#FBBF24', 'stroke' => '#D97706', 'ribbonA' => '#FBBF24', 'ribbonB' => '#D97706'],
      ['body' => '#D4D4D8', 'stroke' => '#9CA3AF', 'ribbonA' => '#D4D4D8', 'ribbonB' => '#9CA3AF'],
      ['body' => '#FB923C', 'stroke' => '#EA580C', 'ribbonA' => '#FB923C', 'ribbonB' => '#EA580C'],
  ];
  $medalStyles = [
      [
          'border' => 'border-amber-300 dark:border-amber-500/40',
          'bg' => 'bg-gradient-to-b from-amber-50 to-white dark:from-amber-900/15 dark:to-zinc-900',
          'badge' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
          'score' => 'text-amber-600 dark:text-amber-400',
          'divider' => 'border-amber-100 dark:border-amber-800/30',
      ],
      [
          'border' => 'border-zinc-300 dark:border-zinc-500/40',
          'bg' => 'bg-gradient-to-b from-zinc-50 to-white dark:from-zinc-800/60 dark:to-zinc-900',
          'badge' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300',
          'score' => 'text-zinc-700 dark:text-zinc-300',
          'divider' => 'border-zinc-100 dark:border-zinc-700/50',
      ],
      [
          'border' => 'border-orange-300 dark:border-orange-600/40',
          'bg' => 'bg-gradient-to-b from-orange-50 to-white dark:from-orange-900/10 dark:to-zinc-900',
          'badge' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
          'score' => 'text-orange-600 dark:text-orange-400',
          'divider' => 'border-orange-100 dark:border-orange-800/30',
      ],
  ];
@endphp

<div class="mb-6 grid gap-4 {{ $podiumGridClass }}">
  @foreach ($topMembers as $rankIndex => $member)
    @php
      $style = $medalStyles[$rankIndex];
      $mc = $medalColors[$rankIndex];
      $allDone = $member['total'] > 0 && $member['progress'] >= 100;
    @endphp
    <div
      class="relative overflow-hidden rounded-xl border-2 p-5 shadow-sm transition {{ $style['border'] }} {{ $style['bg'] }}"
      wire:key="podium-{{ $member['user']->id }}">

      <div class="mb-4 flex items-start justify-between">
        <svg viewBox="0 0 32 36" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-9 shrink-0">
          <rect x="10" y="0" width="5" height="14" rx="2" fill="{{ $mc['ribbonA'] }}" />
          <rect x="17" y="0" width="5" height="14" rx="2" fill="{{ $mc['ribbonB'] }}" />
          <circle cx="16" cy="24" r="11" fill="{{ $mc['body'] }}" stroke="{{ $mc['stroke'] }}"
            stroke-width="1.5" />
          <circle cx="16" cy="24" r="7" fill="none" stroke="{{ $mc['stroke'] }}" stroke-width="1"
            stroke-opacity="0.4" />
        </svg>
        <span
          class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $style['badge'] }}">#{{ $rankIndex + 1 }}</span>
      </div>

      <div class="mb-4 flex flex-col items-center text-center">
        <flux:avatar circle size="lg" :name="$member['user']->name" :initials="$member['user']->initials()"
          :src="$member['user']->avatar" class="mb-2" />
        <p class="font-semibold leading-snug text-zinc-800 dark:text-zinc-100">{{ $member['user']->name }}</p>
        @if ($allDone)
          <span
            class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
            <svg class="size-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
              stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 6 9 17l-5-5" />
            </svg>
            Semua Selesai
          </span>
        @endif
      </div>

      <div class="mb-3 text-center">
        <p class="tabular-nums text-3xl font-bold {{ $style['score'] }}">{{ $member['progress'] }}%</p>
        <p class="text-[11px] text-zinc-400 dark:text-zinc-500">tingkat penyelesaian</p>
      </div>

      <div class="mb-4 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
        <div
          class="h-full rounded-full transition-all duration-700
            {{ $allDone ? 'bg-emerald-500' : ($member['progress'] >= 70 ? 'bg-teal-500' : ($member['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500')) }}"
          style="width: {{ $member['progress'] }}%"></div>
      </div>

      <div class="grid grid-cols-2 gap-2 border-t pt-3 text-center {{ $style['divider'] }}">
        <div>
          <p class="tabular-nums text-sm font-bold text-zinc-800 dark:text-zinc-100">
            {{ $member['completed'] }}<span class="text-xs font-normal text-zinc-400">/{{ $member['total'] }}</span>
          </p>
          <p class="text-[10px] text-zinc-400">Selesai</p>
        </div>
        <div>
          <p
            class="tabular-nums text-sm font-bold {{ $member['overdue'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
            {{ $member['overdue'] }}</p>
          <p class="text-[10px] text-zinc-400">Terlambat</p>
        </div>
      </div>
    </div>
  @endforeach
</div>
