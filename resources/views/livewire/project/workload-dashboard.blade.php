<div>
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">Beban Kerja & Traffic</flux:heading>
    </div>

    <div class="flex items-center gap-2">
      <flux:input onclick="this.showPicker()" type="month" wire:model.live="selectedMonth" size="sm" icon="calendar"
        class="w-full sm:w-72" />
    </div>
  </div>

  <div class="mb-6">
    <div
      class="flex w-full sm:inline-flex sm:w-auto items-center rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
      <button wire:click="switchView('task')"
        class="flex-1 sm:flex-none rounded-md px-2 sm:px-4 py-2.5 sm:py-2 text-sm font-medium transition-all {{ $view === 'task' ? 'bg-white text-teal-700 shadow-sm dark:bg-zinc-700 dark:text-teal-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center justify-center gap-2">
          <flux:icon name="clipboard-document-list" variant="micro" class="size-4 shrink-0" />
          <span class="truncate">View by Task</span>
        </span>
      </button>

      <button wire:click="switchView('member')"
        class="flex-1 sm:flex-none rounded-md px-2 sm:px-4 py-2.5 sm:py-2 text-sm font-medium transition-all {{ $view === 'member' ? 'bg-white text-teal-700 shadow-sm dark:bg-zinc-700 dark:text-teal-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center justify-center gap-2">
          <flux:icon name="users" variant="micro" class="size-4 shrink-0" />
          <span class="truncate">View by Member</span>
        </span>
      </button>
    </div>
  </div>

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
      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center gap-3">
          <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
            <flux:icon name="check-circle" class="size-5 text-emerald-600 dark:text-emerald-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $completedTasks }}</p>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">Tugas Selesai</p>
          </div>
        </div>
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
          <div class="flex size-10 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-900/30">
            <flux:icon name="chart-pie" class="size-5 text-teal-600 dark:text-teal-400" />
          </div>
          <div>
            <p class="text-2xl font-bold text-zinc-800 dark:text-zinc-100">{{ $progressPercent }}%</p>
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
        @php
          $priorityTotal = max(1, array_sum(array_column($priorityData, 'count')));
        @endphp
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
        @php
          $statusTotal = max(1, array_sum(array_column($statusData, 'count')));
        @endphp
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

    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Daftar Tugas per Proyek</h3>
        <span class="text-xs text-zinc-400">{{ $taskGroups->count() }} proyek</span>
      </div>

      @forelse ($taskGroups as $group)
        <div
          class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
          wire:key="group-{{ $loop->index }}">
          <div
            class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 bg-zinc-50/60 px-6 py-3 dark:border-zinc-700 dark:bg-zinc-800/40">
            <div>
              <div class="flex items-center gap-2">
                <flux:icon name="rectangle-stack" variant="micro" class="size-4 text-teal-500" />
                <span class="font-medium text-zinc-800 dark:text-zinc-100">{{ $group['name'] }}</span>
                @if ($group['space'])
                  <span class="text-xs text-zinc-400">· {{ $group['space'] }}</span>
                @endif
              </div>
              <div class="mt-1 flex items-center gap-3 text-xs text-zinc-500 dark:text-zinc-400">
                <span>{{ $group['total'] }} tugas</span>
                <span>·</span>
                <span>{{ $group['completed'] }} selesai</span>
                @if ($group['overdue'] > 0)
                  <span>·</span>
                  <span class="text-red-500">{{ $group['overdue'] }} terlambat</span>
                @endif
              </div>
            </div>
            <div class="flex items-center gap-2">
              <div class="h-1.5 w-32 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                <div
                  class="h-full rounded-full {{ $group['progress'] >= 70 ? 'bg-teal-500' : ($group['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500') }}"
                  style="width: {{ $group['progress'] }}%"></div>
              </div>
              <span class="text-xs font-medium text-zinc-600 dark:text-zinc-300">{{ $group['progress'] }}%</span>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap text-left text-sm">
              <thead>
                <tr class="border-b border-zinc-100 dark:border-zinc-700">
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
                  <tr
                    class="border-b border-zinc-50 transition last:border-b-0 hover:bg-zinc-50/50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                    wire:key="task-{{ $task['id'] }}">
                    <td class="px-6 py-4">
                      <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $task['title'] }}</div>
                    </td>
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
                      <flux:badge color="{{ $priorityColor }}" size="sm">{{ $priorityLabel }}</flux:badge>
                    </td>
                    <td class="px-6 py-4">
                      @php
                        $statusColor = match ($task['status_type']) {
                            'open' => 'zinc',
                            'active' => 'teal',
                            'closed' => 'green',
                            default => 'zinc',
                        };
                        $statusLabel = match ($task['status_type']) {
                            'open' => 'Belum Dikerjakan',
                            'active' => 'Sedang Dikerjakan',
                            'closed' => 'Selesai',
                            default => 'Belum Dikerjakan',
                        };
                      @endphp
                      <flux:badge color="{{ $statusColor }}" size="sm">{{ $statusLabel }}</flux:badge>
                    </td>
                    <td class="px-6 py-4">
                      @if ($task['assignees']->isNotEmpty())
                        <div class="flex items-center gap-1">
                          @foreach ($task['assignees']->take(3) as $assignee)
                            <flux:avatar circle size="xs" :name="$assignee->name"
                              :initials="$assignee->initials()" :src="$assignee->avatar" />
                          @endforeach
                          @if ($task['assignees']->count() > 3)
                            <span class="text-xs text-zinc-400">+{{ $task['assignees']->count() - 3 }}</span>
                          @endif
                        </div>
                      @else
                        <span class="text-zinc-400">Belum ditugaskan</span>
                      @endif
                    </td>
                    <td class="px-6 py-4">
                      @if ($task['due_date'])
                        <span
                          class="{{ $task['is_overdue'] ? 'font-medium text-red-600 dark:text-red-400' : 'text-zinc-600 dark:text-zinc-400' }}">
                          {{ $task['due_date']->format('d M Y') }}
                        </span>
                        @if ($task['is_overdue'])
                          <div class="text-xs text-red-500">Terlambat</div>
                        @endif
                      @else
                        <span class="text-zinc-400">Tidak ada tenggat</span>
                      @endif
                    </td>
                    <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
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
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
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
              <tr
                class="border-b border-zinc-50 transition hover:bg-zinc-50/50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                wire:key="member-{{ $member['user']->id }}">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <flux:avatar circle size="sm" :name="$member['user']->name"
                      :initials="$member['user']->initials()" :src="$member['user']->avatar" />
                    <div>
                      <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $member['user']->name }}</div>
                      <div class="text-xs text-zinc-400">{{ $member['user']->email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $member['total'] }}</span>
                  <span class="text-zinc-400"> ({{ $member['completed'] }} selesai)</span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center gap-2">
                    <div class="h-1.5 w-20 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                      <div
                        class="h-full rounded-full {{ $member['progress'] >= 70 ? 'bg-teal-500' : ($member['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500') }}"
                        style="width: {{ $member['progress'] }}%"></div>
                    </div>
                    <span class="text-xs text-zinc-500">{{ $member['progress'] }}%</span>
                  </div>
                </td>
                <td class="px-6 py-4">
                  @if ($member['overdue'] > 0)
                    <flux:badge color="red" size="sm">{{ $member['overdue'] }}</flux:badge>
                  @else
                    <span class="text-zinc-400">0</span>
                  @endif
                </td>
                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
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
