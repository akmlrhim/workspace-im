<div class="space-y-4">
  <div class="flex items-center justify-between">
    <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Daftar Tugas per List</h3>
    <span class="text-xs text-zinc-400">{{ $taskGroups->count() }} daftar</span>
  </div>

  @forelse ($taskGroups as $group)
    @php $groupDone = $group['total'] > 0 && $group['completed'] === $group['total']; @endphp
    <div
      class="overflow-hidden rounded-xl border shadow-sm
        {{ $groupDone ? 'border-emerald-200 dark:border-emerald-700/50' : 'border-zinc-200 dark:border-zinc-700' }}
        {{ $groupDone ? 'bg-emerald-50/30 dark:bg-emerald-900/5' : 'bg-white dark:bg-zinc-900' }}"
      wire:key="group-{{ $group['id'] }}">

      <div
        class="flex flex-wrap items-center justify-between gap-3 border-b px-6 py-3
          {{ $groupDone ? 'border-emerald-100 bg-emerald-50/60 dark:border-emerald-800/40 dark:bg-emerald-900/15' : 'border-zinc-200 bg-zinc-50/60 dark:border-zinc-700 dark:bg-zinc-800/40' }}">
        <div>
          <div class="flex items-center gap-2">
            @if ($groupDone)
              <svg class="size-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            @else
              <flux:icon name="rectangle-stack" variant="micro" class="size-4 text-teal-500" />
            @endif
            <span
              class="font-medium {{ $groupDone ? 'text-emerald-800 dark:text-emerald-300' : 'text-zinc-800 dark:text-zinc-100' }}">
              {{ $group['name'] }}
            </span>
            @if ($group['space'])
              <span class="text-xs text-zinc-400">· {{ $group['space'] }}</span>
            @endif
            @if ($groupDone)
              <span
                class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                <svg class="size-2.5 shrink-0" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd"
                    d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z"
                    clip-rule="evenodd" />
                </svg>
                Selesai
              </span>
            @endif
          </div>
          <div class="mt-1 flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
            <span><span class="tabular-nums">{{ $group['total'] }}</span> tugas</span>
            <span>·</span>
            <span class="{{ $group['completed'] > 0 ? 'text-emerald-600 dark:text-emerald-400 font-medium' : '' }}">
              <span class="tabular-nums">{{ $group['completed'] }}</span> selesai
            </span>
            @if ($group['overdue'] > 0)
              <span>·</span>
              <span class="text-red-500"><span class="tabular-nums">{{ $group['overdue'] }}</span> melewati
                tenggat</span>
            @endif
          </div>
        </div>
        <div class="flex items-center gap-2">
          <div class="h-2 w-32 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
            <div
              class="h-full rounded-full transition-all
                {{ $group['progress'] >= 100 ? 'bg-emerald-500' : ($group['progress'] >= 70 ? 'bg-teal-500' : ($group['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500')) }}"
              style="width: {{ $group['progress'] }}%"></div>
          </div>
          <span
            class="tabular-nums text-xs font-semibold {{ $group['progress'] >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-600 dark:text-zinc-300' }}">
            {{ $group['progress'] }}%
          </span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-left text-sm">
          <thead>
            <tr
              class="border-b {{ $groupDone ? 'border-emerald-100 dark:border-emerald-800/30' : 'border-zinc-100 dark:border-zinc-700' }}">
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Nama Tugas</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Prioritas</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Status</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Ditugaskan Ke</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Tenggat Waktu</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Jam Tercatat</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($group['tasks'] as $task)
              @include('livewire.partials.workload.task-row', ['task' => $task])
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @empty
    <div
      class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center text-sm text-zinc-400 dark:border-zinc-700 dark:bg-zinc-900">
      Tidak ada data tugas untuk periode ini.
    </div>
  @endforelse
</div>
