<div x-show="activeTab === 'activity'" x-cloak class="space-y-6">
  <div class="rounded-xl border border-zinc-200 bg-zinc-50/50 p-4 dark:border-zinc-700/50 dark:bg-zinc-800/30">
    <div class="mb-3 flex items-center justify-between">
      <div class="flex items-center gap-1.5">
        <flux:icon name="clock" class="size-4 text-zinc-400" />
        <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Time Tracking</h3>
      </div>

      <div
        class="flex items-center gap-1.5 rounded-md bg-white px-2 py-1 shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-700">
        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500">Total</span>
        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
          {{ floor($totalTimeSeconds / 3600) }}h {{ floor(($totalTimeSeconds % 3600) / 60) }}m
        </span>
      </div>
    </div>

    @if ($timeEntries->isNotEmpty())
      <div class="space-y-2">
        <h4 class="text-[10px] font-semibold uppercase tracking-wider text-zinc-400">Riwayat Sesi — Semua Anggota
        </h4>

        <div
          class="max-h-56 overflow-y-auto rounded-lg border border-zinc-200 bg-white custom-scrollbar dark:border-zinc-700 dark:bg-zinc-900/50">
          <div class="divide-y divide-zinc-100 dark:divide-zinc-800/50">
            @foreach ($timeEntries as $entry)
              <div
                class="flex items-center justify-between gap-3 px-3 py-2.5 text-xs transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                <div class="flex items-center gap-2.5 min-w-0">
                  <flux:avatar circle :name="$entry->user?->name ?? 'Unknown'"
                    :initials="$entry->user?->initials() ?? '?'" :src="$entry->user?->avatar" size="xs"
                    class="size-6 shrink-0 ring-2 ring-white dark:ring-zinc-900" />
                  <div class="min-w-0">
                    <span class="block truncate font-medium text-zinc-700 dark:text-zinc-200">
                      {{ $entry->user?->name ?? 'Unknown' }}
                    </span>
                    <span class="block text-[10px] text-zinc-400 dark:text-zinc-500">
                      {{ $entry->started_at->format('d M Y, H:i') }}
                      @if ($entry->stopped_at)
                        — {{ $entry->stopped_at->format('H:i') }}
                      @endif
                    </span>
                  </div>
                </div>

                <span class="shrink-0">
                  @if ($entry->stopped_at)
                    <span
                      class="inline-flex items-center gap-1 rounded-md bg-zinc-100 px-2 py-0.5 font-mono text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                      <flux:icon name="clock" class="size-3 text-zinc-400" />
                      {{ floor($entry->duration_seconds / 3600) }}jam
                      {{ floor(($entry->duration_seconds % 3600) / 60) }}menit
                    </span>
                  @else
                    <span
                      class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2 py-0.5 text-[10px] font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                      <span class="size-1.5 rounded-full bg-green-500 animate-pulse"></span>
                      Berjalan
                    </span>
                  @endif
                </span>
              </div>
            @endforeach
          </div>
        </div>
      </div>
    @else
      <p class="text-xs italic text-zinc-400 dark:text-zinc-500">
        Belum ada sesi pencatatan waktu untuk tugas ini.
      </p>
    @endif
  </div>

  <div x-data="{ expanded: {{ $activities->count() <= 5 ? 'true' : 'false' }} }">
    <div class="mb-3 flex items-center justify-between">
      <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
        Riwayat Aktivitas
        @if ($activities->count() > 0)
          <span class="ml-1 text-xs text-zinc-400">({{ $activities->count() }})</span>
        @endif
      </h3>
      @if ($activities->count() > 5)
        <button @click="expanded = !expanded"
          class="flex items-center gap-1 rounded-md px-2 py-1 text-xs text-zinc-500 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-700 dark:hover:text-zinc-200">
          <span x-text="expanded ? 'Tutup' : 'Tampilkan semua'"></span>
          <flux:icon name="chevron-down" class="size-3.5 transition-transform"
            x-bind:class="expanded ? 'rotate-180' : ''" />
        </button>
      @endif
    </div>
    @if ($activities->isNotEmpty())
      <div class="space-y-2">
        @foreach ($activities as $index => $activity)
          <div class="flex items-start gap-2 text-xs" wire:key="activity-{{ $activity->id }}"
            @if ($index >= 5) x-show="expanded" x-cloak x-collapse @endif>
            <flux:avatar circle :name="$activity->user?->name ?? 'Unknown'"
              :initials="$activity->user?->initials() ?? '?'" :src="$activity->user?->avatar" size="xs"
              class="mt-0.5" />
            <div>
              <span
                class="font-medium text-zinc-700 dark:text-zinc-300">{{ $activity->user?->name ?? 'Unknown' }}</span>
              <span class="text-zinc-500 dark:text-zinc-400">
                @switch($activity->type)
                  @case('created')
                    membuat tugas ini
                  @break

                  @case('status_changed')
                    mengubah status dari <span class="font-medium">{{ $activity->old_value }}</span>
                    ke <span class="font-medium">{{ $activity->new_value }}</span>
                  @break

                  @case('priority_changed')
                    mengubah prioritas dari <span class="font-medium">{{ $activity->old_value }}</span>
                    ke <span class="font-medium">{{ $activity->new_value }}</span>
                  @break

                  @case('assignee_changed')
                    menugaskan ke <span class="font-medium">{{ $activity->new_value }}</span>
                  @break

                  @default
                    {{ $activity->type }}
                @endswitch
              </span>
              <span class="block text-zinc-400 dark:text-zinc-500">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <p class="text-xs italic text-zinc-400 dark:text-zinc-500">Belum ada aktivitas.</p>
    @endif
  </div>
</div>
