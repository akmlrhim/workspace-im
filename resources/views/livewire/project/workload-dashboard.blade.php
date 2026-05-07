<div>
  {{-- ─── Header ──────────────────────────────────────────── --}}
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">Workload & Traffic</flux:heading>
    </div>
    <div class="flex items-center gap-2">
      <flux:input onclick="this.showPicker()" type="month" wire:model.live="selectedMonth" size="sm" icon="calendar"
        class="w-full sm:w-72" />
    </div>
  </div>

  {{-- toggle switch  --}}
  <div class="mb-6">
    <div
      class="flex w-full sm:inline-flex sm:w-auto items-center rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
      <button wire:click="switchView('task')"
        class="flex-1 sm:flex-none rounded-md px-2 sm:px-4 py-2.5 sm:py-2 text-sm font-medium transition-all
          {{ $view === 'task' ? 'bg-white text-blue-700 shadow-sm dark:bg-zinc-700 dark:text-blue-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center justify-center gap-2">
          <flux:icon name="clipboard-document-list" variant="micro" class="size-4 shrink-0" />
          <span class="truncate text-sm">View by Task</span>
        </span>
      </button>
      <button wire:click="switchView('member')"
        class="flex-1 sm:flex-none rounded-md px-2 sm:px-4 py-2.5 sm:py-2 text-sm font-medium transition-all
          {{ $view === 'member' ? 'bg-white text-blue-700 shadow-sm dark:bg-zinc-700 dark:text-blue-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center justify-center gap-2">
          <flux:icon name="users" variant="micro" class="size-4 shrink-0" />
          <span class="truncate text-sm">View by Member</span>
        </span>
      </button>
    </div>
  </div>

  {{-- view by task  --}}
  @if ($view === 'task')
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center gap-3">
          <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
            <flux:icon name="clipboard-document-list" class="size-5 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $totalTasks }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Tugas</p>
          </div>
        </div>
      </div>

      <div
        class="rounded-xl border bg-white p-6 shadow-sm dark:bg-zinc-900
          {{ $completedTasks > 0 ? 'border-emerald-200 dark:border-emerald-700/50' : 'border-zinc-200 dark:border-zinc-700' }}">
        <div class="flex items-center gap-3">
          <div
            class="flex size-10 items-center justify-center rounded-lg
              {{ $completedTasks > 0 ? 'bg-emerald-50 dark:bg-emerald-900/30' : 'bg-zinc-50 dark:bg-zinc-800' }}">
            <flux:icon name="check-circle"
              class="size-5 {{ $completedTasks > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}" />
          </div>
          <div>
            <p
              class="text-2xl font-bold {{ $completedTasks > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-800 dark:text-zinc-100' }}">
              {{ $completedTasks }}
            </p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Tugas Selesai</p>
          </div>
        </div>
        @if ($totalTasks > 0 && $completedTasks === $totalTasks)
          <div class="mt-3 flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1.5 dark:bg-emerald-900/20">
            <svg class="size-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            <span class="text-[11px] font-semibold text-emerald-700 dark:text-emerald-400">Semua tugas selesai!</span>
          </div>
        @endif
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center gap-3">
          <div class="flex size-10 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/30">
            <flux:icon name="exclamation-triangle" class="size-5 text-red-600 dark:text-red-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $overdueTasks }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Tugas Terlambat</p>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center gap-3">
          <div
            class="flex size-10 items-center justify-center rounded-lg
              {{ $progressPercent >= 70 ? 'bg-emerald-50 dark:bg-emerald-900/30' : ($progressPercent >= 40 ? 'bg-amber-50 dark:bg-amber-900/30' : 'bg-red-50 dark:bg-red-900/30') }}">
            <flux:icon name="chart-pie"
              class="size-5 {{ $progressPercent >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($progressPercent >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}" />
          </div>
          <div>
            <p
              class="text-2xl font-bold {{ $progressPercent >= 70 ? 'text-emerald-600 dark:text-emerald-400' : ($progressPercent >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-800 dark:text-zinc-100') }}">
              {{ $progressPercent }}%
            </p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Tingkat Penyelesaian</p>
          </div>
        </div>
      </div>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Distribusi Prioritas</h3>
          <flux:icon name="flag" class="size-5 text-zinc-400" />
        </div>
        @php $priorityTotal = max(1, array_sum(array_column($priorityData, 'count'))); @endphp
        <div class="space-y-3">
          @foreach ($priorityData as $priority)
            <div>
              <div class="mb-1 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="size-2.5 rounded-full {{ $priority['color'] }}"></div>
                  <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $priority['label'] }}</span>
                </div>
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $priority['count'] }}</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                <div class="h-full rounded-full {{ $priority['color'] }} transition-all"
                  style="width: {{ ($priority['count'] / $priorityTotal) * 100 }}%"></div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Distribusi Status</h3>
          <flux:icon name="chart-bar" class="size-5 text-zinc-400" />
        </div>
        @php $statusTotal = max(1, array_sum(array_column($statusData, 'count'))); @endphp
        <div class="space-y-3">
          @foreach ($statusData as $status)
            <div>
              <div class="mb-1 flex items-center justify-between">
                <div class="flex items-center gap-2">
                  <div class="size-2.5 rounded-full {{ $status['color'] }}"></div>
                  <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $status['label'] }}</span>
                </div>
                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $status['count'] }}</span>
              </div>
              <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                <div class="h-full rounded-full {{ $status['color'] }} transition-all"
                  style="width: {{ ($status['count'] / $statusTotal) * 100 }}%"></div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Task groups table --}}
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Daftar Tugas per Proyek</h3>
        <span class="text-xs text-zinc-400">{{ $taskGroups->count() }} proyek</span>
      </div>

      @forelse ($taskGroups as $group)
        @php $groupDone = $group['total'] > 0 && $group['completed'] === $group['total']; @endphp
        <div
          class="overflow-hidden rounded-xl border shadow-sm
            {{ $groupDone ? 'border-emerald-200 dark:border-emerald-700/50' : 'border-zinc-200 dark:border-zinc-700' }}
            {{ $groupDone ? 'bg-emerald-50/30 dark:bg-emerald-900/5' : 'bg-white dark:bg-zinc-900' }}"
          wire:key="group-{{ $loop->index }}">

          {{-- Group header --}}
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
                <span>{{ $group['total'] }} tugas</span>
                <span>·</span>
                <span
                  class="{{ $group['completed'] > 0 ? 'text-emerald-600 dark:text-emerald-400 font-medium' : '' }}">
                  {{ $group['completed'] }} selesai
                </span>
                @if ($group['overdue'] > 0)
                  <span>·</span>
                  <span class="text-red-500">{{ $group['overdue'] }} terlambat</span>
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
                class="text-xs font-semibold {{ $group['progress'] >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-600 dark:text-zinc-300' }}">
                {{ $group['progress'] }}%
              </span>
            </div>
          </div>

          {{-- Tasks table --}}
          <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left text-sm">
              <thead>
                <tr
                  class="border-b {{ $groupDone ? 'border-emerald-100 dark:border-emerald-800/30' : 'border-zinc-100 dark:border-zinc-700' }}">
                  <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Judul Tugas</th>
                  <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Prioritas</th>
                  <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Status</th>
                  <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Ditugaskan Kepada</th>
                  <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Tenggat Waktu</th>
                  <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Waktu Tercatat</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($group['tasks'] as $task)
                  @php $isDone = $task['status_type'] === 'closed'; @endphp
                  <tr
                    class="border-b transition last:border-b-0
                      {{ $isDone
                          ? 'border-emerald-50 bg-emerald-50/50 hover:bg-emerald-50 dark:border-emerald-900/20 dark:bg-emerald-900/10 dark:hover:bg-emerald-900/15'
                          : 'border-zinc-50 hover:bg-zinc-50/50 dark:border-zinc-800 dark:hover:bg-zinc-800/50' }}"
                    wire:key="task-{{ $task['id'] }}">

                    {{-- Title --}}
                    <td class="px-6 py-4">
                      <div class="flex items-center gap-2">
                        @if ($isDone)
                          <svg class="size-4 shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                          </svg>
                          <span class="font-medium text-emerald-700 dark:text-emerald-400">
                            {{ $task['title'] }}
                          </span>
                        @else
                          <span class="font-medium text-zinc-800 dark:text-zinc-100">{{ $task['title'] }}</span>
                        @endif
                      </div>
                    </td>

                    {{-- Priority --}}
                    <td class="px-6 py-4">
                      @php
                        $priorityLabel = match ($task['priority']) {
                            'urgent' => 'Mendesak',
                            'high' => 'Tinggi',
                            'normal' => 'Normal',
                            'low' => 'Rendah',
                            default => 'Normal',
                        };
                        $priorityColor = match ($task['priority']) {
                            'urgent' => 'red',
                            'high' => 'orange',
                            'normal' => 'blue',
                            'low' => 'zinc',
                            default => 'blue',
                        };
                      @endphp
                      <flux:badge color="{{ $isDone ? 'zinc' : $priorityColor }}" size="sm"
                        class="{{ $isDone ? 'opacity-60' : '' }}">
                        {{ $priorityLabel }}
                      </flux:badge>
                    </td>

                    {{-- Status --}}
                    <td class="px-6 py-4">
                      @if ($isDone)
                        <span
                          class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                          <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                          </svg>
                          Selesai
                        </span>
                      @else
                        @php
                          $statusColor = match ($task['status_type']) {
                              'active' => 'teal',
                              default => 'zinc',
                          };
                          $statusLabel = match ($task['status_type']) {
                              'active' => 'Sedang Dikerjakan',
                              default => 'Belum Dikerjakan',
                          };
                        @endphp
                        <flux:badge color="{{ $statusColor }}" size="sm">{{ $statusLabel }}</flux:badge>
                      @endif
                    </td>

                    {{-- Assignees --}}
                    <td class="px-6 py-4">
                      @if ($task['assignees']->isNotEmpty())
                        <div class="flex items-center gap-1">
                          @foreach ($task['assignees']->take(3) as $assignee)
                            <flux:avatar circle size="xs" :name="$assignee->name"
                              :initials="$assignee->initials()" :src="$assignee->avatar"
                              class="{{ $isDone ? 'opacity-70' : '' }}" />
                          @endforeach
                          @if ($task['assignees']->count() > 3)
                            <span class="text-xs text-zinc-400">+{{ $task['assignees']->count() - 3 }}</span>
                          @endif
                        </div>
                      @else
                        <span class="text-zinc-400">Belum ditugaskan</span>
                      @endif
                    </td>

                    {{-- Due date --}}
                    <td class="px-6 py-4">
                      @if ($task['due_date'])
                        @if ($isDone)
                          <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                              stroke-width="2">
                              <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm">{{ $task['due_date']->format('d M Y') }}</span>
                          </span>
                        @elseif ($task['is_overdue'])
                          <span class="font-medium text-red-600 dark:text-red-400">
                            {{ $task['due_date']->format('d M Y') }}
                          </span>
                          <div class="mt-0.5 text-xs text-red-500">Terlambat</div>
                        @else
                          <span
                            class="text-zinc-600 dark:text-zinc-400">{{ $task['due_date']->format('d M Y') }}</span>
                        @endif
                      @else
                        <span class="text-zinc-400">–</span>
                      @endif
                    </td>

                    {{-- Hours --}}
                    <td
                      class="px-6 py-4 {{ $isDone ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                      {{ $task['hours'] }} jam
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @empty
        <div
          class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center text-sm text-zinc-400 dark:border-zinc-700 dark:bg-zinc-900">
          Tidak ada data tugas tersedia.
        </div>
      @endforelse
    </div>


    {{-- view by member  --}}
  @elseif ($view === 'member')
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center gap-3">
          <div class="flex size-10 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-900/30">
            <flux:icon name="users" class="size-5 text-teal-600 dark:text-teal-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $totalMembers }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Anggota Tim</p>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center gap-3">
          <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30">
            <flux:icon name="clipboard-document-list" class="size-5 text-blue-600 dark:text-blue-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $totalTasks }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Tugas</p>
          </div>
        </div>
      </div>

      <div
        class="rounded-xl border p-6 shadow-sm
          {{ $completedTasks > 0 ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-700/50 dark:bg-emerald-900/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">
        <div class="flex items-center gap-3">
          <div
            class="flex size-10 items-center justify-center rounded-lg
              {{ $completedTasks > 0 ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-zinc-50 dark:bg-zinc-800' }}">
            <flux:icon name="check-circle"
              class="size-5 {{ $completedTasks > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}" />
          </div>
          <div>
            <p
              class="text-2xl font-bold {{ $completedTasks > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-800 dark:text-zinc-100' }}">
              {{ $completedTasks }}
            </p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Tugas Selesai</p>
          </div>
        </div>
      </div>
    </div>

    {{-- Member table --}}
    <div
      class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
      <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Beban Kerja Anggota</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap text-left text-sm">
          <thead>
            <tr class="border-b border-zinc-100 dark:border-zinc-700">
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Anggota</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Tugas</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Progres</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Terlambat</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Waktu Tercatat</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($memberStats as $member)
              @php $allDone = $member['total'] > 0 && $member['progress'] >= 100; @endphp
              <tr
                class="border-b transition last:border-b-0
                  {{ $allDone
                      ? 'border-emerald-50 bg-emerald-50/60 hover:bg-emerald-50 dark:border-emerald-900/20 dark:bg-emerald-900/10 dark:hover:bg-emerald-900/15'
                      : 'border-zinc-50 hover:bg-zinc-50/50 dark:border-zinc-800 dark:hover:bg-zinc-800/50' }}"
                wire:key="member-{{ $member['user']->id }}">

                {{-- Member info --}}
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <div class="relative">
                      <flux:avatar circle size="sm" :name="$member['user']->name"
                        :initials="$member['user']->initials()" :src="$member['user']->avatar" />
                      @if ($allDone)
                        <span
                          class="absolute -right-1 -top-1 flex size-4 items-center justify-center rounded-full bg-emerald-500 ring-2 ring-white dark:ring-zinc-900">
                          <svg class="size-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                          </svg>
                        </span>
                      @endif
                    </div>
                    <div>
                      <div
                        class="font-medium {{ $allDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-zinc-800 dark:text-zinc-100' }}">
                        {{ $member['user']->name }}
                        @if ($allDone)
                          <span
                            class="ml-1 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                            <svg class="size-2.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"
                              aria-hidden="true">
                              <path fill-rule="evenodd"
                                d="M12.416 3.376a.75.75 0 0 1 .208 1.04l-5 7.5a.75.75 0 0 1-1.154.114l-3-3a.75.75 0 0 1 1.06-1.06l2.353 2.353 4.493-6.74a.75.75 0 0 1 1.04-.207Z"
                                clip-rule="evenodd" />
                            </svg>
                            Clear
                          </span>
                        @endif
                      </div>
                      <div class="text-xs text-zinc-400">{{ $member['user']->email }}</div>
                    </div>
                  </div>
                </td>

                {{-- Task count --}}
                <td class="px-6 py-4">
                  <span
                    class="font-semibold {{ $allDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-zinc-700 dark:text-zinc-300' }}">
                    {{ $member['total'] }}
                  </span>
                  <span class="text-zinc-400">
                    ({{ $member['completed'] }} selesai)
                  </span>
                </td>

                {{-- Progress bar --}}
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="h-2 w-24 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                      <div
                        class="h-full rounded-full transition-all
                          {{ $allDone ? 'bg-emerald-500' : ($member['progress'] >= 70 ? 'bg-teal-500' : ($member['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500')) }}"
                        style="width: {{ $member['progress'] }}%"></div>
                    </div>
                    <span
                      class="text-xs font-semibold {{ $allDone ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-500' }}">
                      {{ $member['progress'] }}%
                    </span>
                  </div>
                </td>

                {{-- Overdue --}}
                <td class="px-6 py-4">
                  @if ($allDone)
                    <span class="text-emerald-500 dark:text-emerald-400">–</span>
                  @elseif ($member['overdue'] > 0)
                    <flux:badge color="red" size="sm">{{ $member['overdue'] }}</flux:badge>
                  @else
                    <span class="text-zinc-400">0</span>
                  @endif
                </td>

                {{-- Hours --}}
                <td
                  class="px-6 py-4 {{ $allDone ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                  {{ $member['hours'] }} jam
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-6 py-12 text-center text-zinc-400">
                  Tidak ada data anggota tersedia.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
