<div>
  {{-- Header --}}
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">Dasbor Beban Kerja & Lalu Lintas</flux:heading>
      <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
        Memantau beban kerja proyek, lalu lintas tugas, dan kinerja tim.
      </flux:text>
    </div>
  </div>

  {{-- Segmented Control --}}
  <div class="mb-6">
    <div
      class="inline-flex items-center rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
      <button wire:click="switchView('project')"
        class="rounded-md px-4 py-2 text-sm font-medium transition-all {{ $view === 'project' ? 'bg-white text-teal-700 shadow-sm dark:bg-zinc-700 dark:text-teal-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center gap-2">
          <flux:icon name="rectangle-stack" variant="micro" class="size-4" />
          Lihat berdasarkan proyek
        </span>
      </button>
      <button wire:click="switchView('member')"
        class="rounded-md px-4 py-2 text-sm font-medium transition-all {{ $view === 'member' ? 'bg-white text-teal-700 shadow-sm dark:bg-zinc-700 dark:text-teal-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center gap-2">
          <flux:icon name="users" variant="micro" class="size-4" />
          Lihat berdasarkan anggota
        </span>
      </button>
      <button wire:click="switchView('task')"
        class="rounded-md px-4 py-2 text-sm font-medium transition-all {{ $view === 'task' ? 'bg-white text-teal-700 shadow-sm dark:bg-zinc-700 dark:text-teal-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center gap-2">
          <flux:icon name="clipboard-document-list" variant="micro" class="size-4" />
          Lihat berdasarkan tugas
        </span>
      </button>
    </div>
  </div>

  @if ($view === 'project')
    {{-- ==================== PROJECT VIEW ==================== --}}

    {{-- Stat Cards Row --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

      {{-- Card 1: Project Workload Overview --}}
      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Ringkasan Beban Kerja Proyek</h3>
          <flux:icon name="chart-pie" class="size-5 text-zinc-400" />
        </div>
        <div class="flex items-center gap-6">
          {{-- Circular Progress --}}
          <div class="relative flex-shrink-0">
            <svg class="size-24 -rotate-90" viewBox="0 0 100 100">
              <circle cx="50" cy="50" r="42" fill="none" stroke-width="8"
                class="stroke-zinc-100 dark:stroke-zinc-700" />
              <circle cx="50" cy="50" r="42" fill="none" stroke-width="8" stroke-linecap="round"
                class="stroke-teal-500"
                stroke-dasharray="{{ $progressPercent * 2.64 }} {{ (100 - $progressPercent) * 2.64 }}" />
            </svg>
            <div class="absolute inset-0 flex items-center justify-center">
              <span class="text-lg font-bold text-zinc-800 dark:text-zinc-100">{{ $progressPercent }}%</span>
            </div>
          </div>
          <div class="space-y-2">
            <div class="flex items-center gap-2">
              <div class="size-2.5 rounded-full bg-teal-500"></div>
              <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $activeLists }} Proyek Aktif</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="size-2.5 rounded-full bg-amber-500"></div>
              <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $overdueTasks }} Tugas Terlambat</span>
            </div>
            <div class="flex items-center gap-2">
              <div class="size-2.5 rounded-full bg-blue-500"></div>
              <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ $totalTasks }} Total Tugas</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Card 2: Task Traffic Flow (Stacked Bar) --}}
      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Alur Lalu Lintas Tugas</h3>
          <flux:icon name="chart-bar" class="size-5 text-zinc-400" />
        </div>
        @if (count($trafficData) > 0)
          @php
            $trafficMax = max(1, max(array_map(fn($r) => $r['open'] + $r['active'] + $r['closed'], $trafficData)));
          @endphp
          <div class="flex items-end gap-2" style="height: 160px;">
            @foreach ($trafficData as $item)
              <div class="group relative flex flex-1 flex-col items-center justify-end" style="height: 100%;">
                {{-- Tooltip --}}
                <div
                  class="pointer-events-none absolute -top-2 z-10 hidden -translate-y-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs shadow-lg group-hover:block dark:border-zinc-600 dark:bg-zinc-800">
                  <div class="mb-1 font-semibold text-zinc-700 dark:text-zinc-200">{{ $item['name'] }}</div>
                  <div class="flex items-center gap-1.5"><span
                      class="inline-block size-2 rounded-full bg-zinc-400"></span>Belum Dikerjakan: {{ $item['open'] }}
                  </div>
                  <div class="flex items-center gap-1.5"><span
                      class="inline-block size-2 rounded-full bg-teal-500"></span>Sedang Dikerjakan:
                    {{ $item['active'] }}
                  </div>
                  <div class="flex items-center gap-1.5"><span
                      class="inline-block size-2 rounded-full bg-emerald-500"></span>Selesai: {{ $item['closed'] }}
                  </div>
                </div>
                {{-- Stacked bar --}}
                <div class="flex w-full flex-col items-center justify-end" style="height: calc(100% - 20px);">
                  <div class="w-[60%] overflow-hidden rounded-t-md"
                    style="height: {{ (($item['open'] + $item['active'] + $item['closed']) / $trafficMax) * 100 }}%;">
                    @if ($item['closed'] > 0)
                      <div class="w-full bg-emerald-500 dark:bg-emerald-400"
                        style="height: {{ ($item['closed'] / ($item['open'] + $item['active'] + $item['closed'])) * 100 }}%;">
                      </div>
                    @endif
                    @if ($item['active'] > 0)
                      <div class="w-full bg-teal-500 dark:bg-teal-400"
                        style="height: {{ ($item['active'] / ($item['open'] + $item['active'] + $item['closed'])) * 100 }}%;">
                      </div>
                    @endif
                    @if ($item['open'] > 0)
                      <div class="w-full bg-zinc-400 dark:bg-zinc-500"
                        style="height: {{ ($item['open'] / ($item['open'] + $item['active'] + $item['closed'])) * 100 }}%;">
                      </div>
                    @endif
                  </div>
                </div>
                <span class="mt-1 truncate text-center text-[10px] leading-tight text-zinc-500 dark:text-zinc-400"
                  style="max-width: 100%;">{{ Str::limit($item['name'], 10) }}</span>
              </div>
            @endforeach
          </div>
          <div class="mt-3 flex justify-center gap-4">
            <div class="flex items-center gap-1.5">
              <div class="size-2 rounded-full bg-zinc-400 dark:bg-zinc-500"></div>
              <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Belum Dikerjakan</span>
            </div>
            <div class="flex items-center gap-1.5">
              <div class="size-2 rounded-full bg-teal-500 dark:bg-teal-400"></div>
              <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Sedang Dikerjakan</span>
            </div>
            <div class="flex items-center gap-1.5">
              <div class="size-2 rounded-full bg-emerald-500 dark:bg-emerald-400"></div>
              <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Selesai</span>
            </div>
          </div>
        @else
          <div class="flex items-center justify-center text-sm text-zinc-400" style="height: 160px;">Tidak ada data
            tersedia
          </div>
        @endif
      </div>

      {{-- Card 3: Daily Task Activity (Sparkline Bars) --}}
      <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Aktivitas Tugas Harian</h3>
          <flux:icon name="calendar-days" class="size-5 text-zinc-400" />
        </div>
        @if (count($allocationData) > 0 && count($allocationFields) > 0)
          @php
            $dayMax = max(
                1,
                max(
                    array_map(
                        fn($row) => array_sum(array_filter($row, fn($v, $k) => $k !== 'day', ARRAY_FILTER_USE_BOTH)),
                        $allocationData,
                    ),
                ),
            );
          @endphp
          <div class="flex items-end gap-px" style="height: 140px;">
            @foreach ($allocationData as $row)
              @php
                $dayTotal = array_sum(array_filter($row, fn($v, $k) => $k !== 'day', ARRAY_FILTER_USE_BOTH));
              @endphp
              <div class="group relative flex flex-1 flex-col items-center justify-end" style="height: 100%;">
                {{-- Tooltip --}}
                @if ($dayTotal > 0)
                  <div
                    class="pointer-events-none absolute -top-2 z-10 hidden -translate-y-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs shadow-lg group-hover:block dark:border-zinc-600 dark:bg-zinc-800">
                    <div class="mb-1 font-semibold text-zinc-700 dark:text-zinc-200">Hari ke-{{ $row['day'] }}</div>
                    @foreach ($allocationFields as $field)
                      @if (($row[$field['field']] ?? 0) > 0)
                        <div class="flex items-center gap-1.5">
                          <span class="inline-block size-2 rounded-full {{ $field['bgColor'] }}"></span>
                          {{ $field['name'] }}: {{ $row[$field['field']] }}
                        </div>
                      @endif
                    @endforeach
                  </div>
                @endif
                {{-- Stacked bar --}}
                <div class="flex w-full flex-col justify-end" style="height: calc(100% - 16px);">
                  @if ($dayTotal > 0)
                    <div class="w-full overflow-hidden rounded-t-sm"
                      style="height: {{ ($dayTotal / $dayMax) * 100 }}%;">
                      @foreach ($allocationFields as $field)
                        @if (($row[$field['field']] ?? 0) > 0)
                          <div class="w-full {{ $field['bgColor'] }}"
                            style="height: {{ (($row[$field['field']] ?? 0) / $dayTotal) * 100 }}%;"></div>
                        @endif
                      @endforeach
                    </div>
                  @endif
                </div>
                @if ($row['day'] % 5 === 1 || $row['day'] === 1)
                  <span class="text-[8px] text-zinc-400">{{ $row['day'] }}</span>
                @else
                  <span class="text-[8px] text-transparent">.</span>
                @endif
              </div>
            @endforeach
          </div>
          <div class="mt-3 flex flex-wrap justify-center gap-3">
            @foreach ($allocationFields as $field)
              <div class="flex items-center gap-1.5">
                <div class="size-2 rounded-full {{ $field['bgColor'] }}"></div>
                <span class="text-[11px] text-zinc-500 dark:text-zinc-400">{{ $field['name'] }}</span>
              </div>
            @endforeach
          </div>
        @else
          <div class="flex items-center justify-center text-sm text-zinc-400" style="height: 140px;">Tidak ada data
            tersedia
          </div>
        @endif
      </div>
    </div>

    {{-- At-Risk Projects Table --}}
    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
      <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Proyek Berisiko</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b border-zinc-100 dark:border-zinc-700">
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Nama Proyek</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Penanggung Jawab</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Tenggat Waktu</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Status</th>
              <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">Waktu Tercatat</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($atRiskProjects as $project)
              <tr
                class="border-b border-zinc-50 transition hover:bg-zinc-50/50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                wire:key="risk-{{ $loop->index }}">
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $project['name'] }}</div>
                    @if ($project['space'])
                      <div class="text-xs text-zinc-400">{{ $project['space'] }}</div>
                    @endif
                    <div class="mt-2 flex items-center gap-2">
                      <div class="h-1.5 w-24 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                        <div
                          class="h-full rounded-full transition-all {{ $project['progress'] >= 70 ? 'bg-teal-500' : ($project['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500') }}"
                          style="width: {{ $project['progress'] }}%"></div>
                      </div>
                      <span class="text-xs text-zinc-400">{{ $project['progress'] }}%</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  @if ($project['lead'])
                    <div class="flex items-center gap-2">
                      <flux:avatar circle size="xs" :name="$project['lead']->name" :initials="$project['lead']->initials()" :src="$project['lead']->avatar" />
                      <span class="text-zinc-700 dark:text-zinc-300">{{ $project['lead']->name }}</span>
                    </div>
                  @else
                    <span class="text-zinc-400">Belum ditugaskan</span>
                  @endif
                </td>
                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                  @if ($project['deadline'])
                    {{ $project['deadline']->format('d M Y') }}
                  @else
                    <span class="text-zinc-400">Tidak ada tenggat</span>
                  @endif
                </td>
                <td class="px-6 py-4">
                  @if ($project['status'] === 'at-risk')
                    <flux:badge color="red" size="sm">Berisiko</flux:badge>
                  @elseif ($project['status'] === 'stuck')
                    <flux:badge color="yellow" size="sm">Terhambat</flux:badge>
                  @else
                    <flux:badge color="green" size="sm">Sesuai Rencana</flux:badge>
                  @endif
                </td>
                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400">
                  {{ $project['hours'] }} jam
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="px-6 py-12 text-center text-zinc-400">
                  Tidak ada data proyek tersedia.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @elseif ($view === 'member')
    {{-- ==================== MEMBER VIEW ==================== --}}

    {{-- Member Stats Summary --}}
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

    {{-- Member Traffic Chart --}}
    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
      <div class="mb-4 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Distribusi Tugas per Anggota</h3>
        <flux:icon name="chart-bar" class="size-5 text-zinc-400" />
      </div>
      @if (count($memberTraffic) > 0)
        @php
          $memberMax = max(1, max(array_map(fn($r) => $r['open'] + $r['active'] + $r['closed'], $memberTraffic)));
        @endphp
        <div class="flex items-end gap-3" style="height: 180px;">
          @foreach ($memberTraffic as $item)
            @php $itemTotal = $item['open'] + $item['active'] + $item['closed']; @endphp
            <div class="group relative flex flex-1 flex-col items-center justify-end" style="height: 100%;">
              {{-- Tooltip --}}
              <div
                class="pointer-events-none absolute -top-2 z-10 hidden -translate-y-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-xs shadow-lg group-hover:block dark:border-zinc-600 dark:bg-zinc-800">
                <div class="mb-1 font-semibold text-zinc-700 dark:text-zinc-200">{{ $item['name'] }}</div>
                <div class="flex items-center gap-1.5"><span
                    class="inline-block size-2 rounded-full bg-zinc-400"></span>Belum Dikerjakan: {{ $item['open'] }}
                </div>
                <div class="flex items-center gap-1.5"><span
                    class="inline-block size-2 rounded-full bg-teal-500"></span>Sedang Dikerjakan:
                  {{ $item['active'] }}
                </div>
                <div class="flex items-center gap-1.5"><span
                    class="inline-block size-2 rounded-full bg-emerald-500"></span>Selesai: {{ $item['closed'] }}
                </div>
              </div>
              {{-- Stacked bar --}}
              <div class="flex w-full flex-col items-center justify-end" style="height: calc(100% - 24px);">
                @if ($itemTotal > 0)
                  <div class="w-[50%] overflow-hidden rounded-t-md"
                    style="height: {{ ($itemTotal / $memberMax) * 100 }}%;">
                    @if ($item['closed'] > 0)
                      <div class="w-full bg-emerald-500 dark:bg-emerald-400"
                        style="height: {{ ($item['closed'] / $itemTotal) * 100 }}%;"></div>
                    @endif
                    @if ($item['active'] > 0)
                      <div class="w-full bg-teal-500 dark:bg-teal-400"
                        style="height: {{ ($item['active'] / $itemTotal) * 100 }}%;"></div>
                    @endif
                    @if ($item['open'] > 0)
                      <div class="w-full bg-zinc-400 dark:bg-zinc-500"
                        style="height: {{ ($item['open'] / $itemTotal) * 100 }}%;"></div>
                    @endif
                  </div>
                @endif
              </div>
              <span class="mt-1 truncate text-center text-[11px] leading-tight text-zinc-500 dark:text-zinc-400"
                style="max-width: 100%;">{{ Str::limit($item['name'], 12) }}</span>
            </div>
          @endforeach
        </div>
        <div class="mt-3 flex justify-center gap-4">
          <div class="flex items-center gap-1.5">
            <div class="size-2 rounded-full bg-zinc-400 dark:bg-zinc-500"></div>
            <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Belum Dikerjakan</span>
          </div>
          <div class="flex items-center gap-1.5">
            <div class="size-2 rounded-full bg-teal-500 dark:bg-teal-400"></div>
            <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Sedang Dikerjakan</span>
          </div>
          <div class="flex items-center gap-1.5">
            <div class="size-2 rounded-full bg-emerald-500 dark:bg-emerald-400"></div>
            <span class="text-[11px] text-zinc-500 dark:text-zinc-400">Selesai</span>
          </div>
        </div>
      @else
        <div class="flex items-center justify-center text-sm text-zinc-400" style="height: 180px;">Tidak ada data
          tersedia
        </div>
      @endif
    </div>

    {{-- Member Table --}}
    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
      <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Beban Kerja Anggota</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
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
                    <flux:avatar circle size="sm" :name="$member['user']->name" :initials="$member['user']->initials()" :src="$member['user']->avatar" />
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
  @elseif ($view === 'task')
    {{-- ==================== TASK VIEW ==================== --}}

    {{-- Task Stats Summary --}}
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

    {{-- Charts Row --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
      {{-- Priority Distribution --}}
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

      {{-- Status Distribution --}}
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

    {{-- Task Table --}}
    <div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
      <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
        <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Daftar Tugas</h3>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
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
            @forelse ($taskList as $task)
              <tr
                class="border-b border-zinc-50 transition hover:bg-zinc-50/50 dark:border-zinc-800 dark:hover:bg-zinc-800/50"
                wire:key="task-{{ $task['id'] }}">
                <td class="px-6 py-4">
                  <div>
                    <div class="font-medium text-zinc-800 dark:text-zinc-100">{{ $task['title'] }}</div>
                    @if ($task['list_name'])
                      <div class="text-xs text-zinc-400">
                        {{ $task['space_name'] ? $task['space_name'] . ' / ' : '' }}{{ $task['list_name'] }}
                      </div>
                    @endif
                  </div>
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
                        <flux:avatar circle size="xs" :name="$assignee->name" :initials="$assignee->initials()" :src="$assignee->avatar" />
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
            @empty
              <tr>
                <td colspan="6" class="px-6 py-12 text-center text-zinc-400">
                  Tidak ada data tugas tersedia.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
