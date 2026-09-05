@php
  $onTimePercent = $completedTasks > 0 ? max(0, 100 - $latePercent - $noDeadlinePercent) : 0;
@endphp

<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">

  <div
    class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
    <div class="min-w-0 flex-1">
      <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Tugas</p>
      <p class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $totalTasks }}
      </p>
      @if ($totalTasks > 0)
        <div class="mt-1 flex flex-wrap gap-x-2 gap-y-0.5 text-xs text-zinc-400">
          <span><span
              class="font-semibold text-zinc-600 dark:text-zinc-300">{{ $totalTasks - $completedTasks }}</span>
            belum selesai</span>
          <span class="text-zinc-300 dark:text-zinc-600">·</span>
          <span><span
              class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $completedTasks }}</span>
            selesai</span>
        </div>
      @else
        <p class="mt-1 text-xs text-zinc-400">Belum ada tugas.</p>
      @endif
    </div>
  </div>

  <div
    class="relative overflow-hidden flex items-start gap-3 rounded-xl border bg-white p-4 shadow-sm dark:bg-zinc-900
      {{ $completedTasks > 0 ? 'border-emerald-200 dark:border-emerald-700/50' : 'border-zinc-200 dark:border-zinc-700' }}">

    <div class="min-w-0 flex-1">
      <p class="text-xs text-zinc-500 dark:text-zinc-400">Tugas Selesai</p>
      <p
        class="text-2xl font-bold tracking-tight {{ $completedTasks > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-900 dark:text-zinc-50' }}">
        {{ $completedTasks }}</p>
      @if ($completedTasks > 0)
        <div class="mt-1.5 flex h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
          @if ($onTimePercent > 0)
            <div class="h-full bg-emerald-500" style="width:{{ $onTimePercent }}%"></div>
          @endif
          @if ($latePercent > 0)
            <div class="h-full bg-red-400" style="width:{{ $latePercent }}%"></div>
          @endif
          @if ($noDeadlinePercent > 0)
            <div class="h-full bg-zinc-300 dark:bg-zinc-500" style="width:{{ $noDeadlinePercent }}%"></div>
          @endif
        </div>
        <div class="mt-1 flex flex-wrap gap-x-1 gap-y-0.5 text-xs text-zinc-400">
          <span><span
              class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $completedOnTime }}</span>
            tepat waktu</span>
          <span class="text-zinc-300 dark:text-zinc-600">·</span>
          <span><span class="font-semibold text-red-500 dark:text-red-400">{{ $completedLate }}</span>
            terlambat</span>
          <span class="text-zinc-300 dark:text-zinc-600">·</span>
          <span><span
              class="font-semibold text-zinc-500 dark:text-zinc-400">{{ $completedNoDeadline }}</span>
            tanpa tenggat</span>
        </div>
      @else
        <p class="mt-1 text-xs text-zinc-400">Belum ada yang selesai.</p>
      @endif
    </div>
  </div>

  <div
    class="flex items-start gap-3 rounded-xl border bg-white p-4 shadow-sm dark:bg-zinc-900
      {{ $overdueTasks > 0 ? 'border-red-200 dark:border-red-800/50' : 'border-zinc-200 dark:border-zinc-700' }}">

    <div class="min-w-0 flex-1">
      <p class="text-xs text-zinc-500 dark:text-zinc-400">Melewati Tenggat</p>
      <p
        class="text-2xl font-bold tracking-tight {{ $overdueTasks > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-50' }}">
        {{ $overdueTasks }}</p>
      <div class="mt-1 text-xs text-zinc-400">
        @if ($totalTasks > 0)
          <span
            class="font-semibold {{ $overduePercent > 0 ? 'text-red-500 dark:text-red-400' : 'text-zinc-500' }}">{{ $overduePercent }}%</span>
          dari total tugas
        @else
          Belum ada tugas.
        @endif
      </div>
    </div>
  </div>

  <div
    class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

    <div class="min-w-0 flex-1">
      <p class="text-xs text-zinc-500 dark:text-zinc-400">Penyelesaian</p>
      <p
        class="text-2xl font-bold tracking-tight
          {{ $progressPercent >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($progressPercent >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-zinc-50') }}">
        {{ $progressPercent }}%
      </p>
      @if ($totalTasks > 0)
        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
          <div
            class="h-full rounded-full transition-all duration-500
              {{ $progressPercent >= 70 ? 'bg-emerald-500' : ($progressPercent >= 40 ? 'bg-amber-500' : 'bg-red-400') }}"
            style="width: {{ $progressPercent }}%"></div>
        </div>
        <p class="mt-1 text-xs text-zinc-400">
          <span class="font-semibold text-zinc-600 dark:text-zinc-300">{{ $completedTasks }}</span>
          dari {{ $totalTasks }} tugas selesai
        </p>
      @else
        <p class="mt-1 text-xs text-zinc-400">Belum ada tugas.</p>
      @endif
    </div>
  </div>
</div>
