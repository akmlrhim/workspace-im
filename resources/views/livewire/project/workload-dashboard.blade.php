<div>
  {{-- ─── Header ──────────────────────────────────────────── --}}
  <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <flux:heading size="xl">Workload & Traffic</flux:heading>
    </div>
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
      <flux:select wire:model.live="selectedMemberId" size="sm" class="w-full sm:w-56">
        <flux:select.option value="">Semua Anggota</flux:select.option>
        @foreach ($this->members as $member)
          <flux:select.option value="{{ $member->id }}">{{ $member->name }}</flux:select.option>
        @endforeach
      </flux:select>
      <flux:input onclick="this.showPicker()" type="month" wire:model.live="selectedMonth" size="sm" icon="calendar"
        class="w-full sm:w-56" />
    </div>
  </div>

  {{-- ─── Active member filter banner ────────────────────── --}}
  @if ($selectedMemberId)
    @php $activeMember = $this->members->firstWhere('id', $selectedMemberId); @endphp
    @if ($activeMember)
      <div
        class="mb-4 flex items-center justify-between gap-3 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2.5 dark:border-indigo-500/40 dark:bg-indigo-500/10">
        <div class="flex min-w-0 items-center gap-2 text-sm text-indigo-700 dark:text-indigo-300">
          <flux:avatar circle size="xs" :name="$activeMember->name" :initials="$activeMember->initials()"
            :src="$activeMember->avatar" />
          <span class="truncate">Menampilkan tugas yang dikerjakan oleh
            <span class="font-semibold">{{ $activeMember->name }}</span></span>
        </div>
        <button wire:click="$set('selectedMemberId', null)"
          class="inline-flex shrink-0 items-center gap-1 rounded-md px-2 py-1 text-xs font-medium text-indigo-600 transition hover:bg-indigo-100 dark:text-indigo-300 dark:hover:bg-indigo-500/20">
          <flux:icon name="x-mark" class="size-3.5" />
          Hapus filter
        </button>
      </div>
    @endif
  @endif

  {{-- ─── Space filter pills ─────────────────────────────── --}}
  @if ($spaces->isNotEmpty())
    <div class="mb-6 flex flex-wrap gap-2">
      <button wire:click="selectSpace(null)"
        class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition
          {{ $selectedSpaceId === null
              ? 'border-indigo-400 bg-indigo-50 text-indigo-700 dark:border-indigo-500/60 dark:bg-indigo-500/10 dark:text-indigo-300'
              : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-500 dark:hover:text-zinc-200' }}">
        <flux:icon name="squares-2x2" class="size-3" />
        Semua Space
      </button>

      @foreach ($spaces as $space)
        <button wire:click="selectSpace({{ $space->id }})" wire:key="space-pill-{{ $space->id }}"
          class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium transition
            {{ $selectedSpaceId === $space->id
                ? 'border-current bg-opacity-10 font-semibold'
                : 'border-zinc-200 bg-white text-zinc-500 hover:border-zinc-300 hover:text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:border-zinc-500 dark:hover:text-zinc-200' }}"
          @if ($selectedSpaceId === $space->id) style="border-color: {{ $space->color }}; color: {{ $space->color }}; background-color: {{ $space->color }}15;" @endif>
          <div class="size-2 rounded-full shrink-0" style="background-color: {{ $space->color }}"></div>
          {{ $space->name }}
        </button>
      @endforeach
    </div>
  @endif

  {{-- ─── View toggle ─────────────────────────────────────── --}}
  <div class="mb-6">
    <div
      class="flex w-full sm:inline-flex sm:w-auto items-center rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800">
      <button wire:click="switchView('task')"
        class="flex-1 sm:flex-none rounded-md px-2 sm:px-4 py-2.5 sm:py-2 text-sm font-medium transition-all
          {{ $view === 'task' ? 'bg-white text-blue-700 shadow-sm dark:bg-zinc-700 dark:text-blue-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center justify-center gap-2">
          <flux:icon name="clipboard-document-list" variant="micro" class="size-4 shrink-0" />
          <span class="truncate text-sm">View By Task</span>
        </span>
      </button>
      <button wire:click="switchView('member')"
        class="flex-1 sm:flex-none rounded-md px-2 sm:px-4 py-2.5 sm:py-2 text-sm font-medium transition-all
          {{ $view === 'member' ? 'bg-white text-blue-700 shadow-sm dark:bg-zinc-700 dark:text-blue-400' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' }}">
        <span class="flex items-center justify-center gap-2">
          <flux:icon name="trophy" variant="micro" class="size-4 shrink-0" />
          <span class="truncate text-sm">Peringkat</span>
        </span>
      </button>
    </div>
  </div>

  {{-- ─── View: Per Daftar ───────────────────────────────── --}}
  @if ($view === 'task')
    @php
      $onTimePercent = $completedTasks > 0 ? max(0, 100 - $latePercent - $noDeadlinePercent) : 0;
    @endphp

    {{-- ─── Stat cards ────────────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">

      {{-- Card 1: Total Tugas --}}
      <div
        class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="min-w-0 flex-1">
          <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Tugas</p>
          <p class="tabular-nums text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">{{ $totalTasks }}
          </p>
          @if ($totalTasks > 0)
            <div class="mt-1 flex flex-wrap gap-x-2 gap-y-0.5 text-xs text-zinc-400">
              <span><span
                  class="tabular-nums font-semibold text-zinc-600 dark:text-zinc-300">{{ $totalTasks - $completedTasks }}</span>
                belum selesai</span>
              <span class="text-zinc-300 dark:text-zinc-600">·</span>
              <span><span
                  class="tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">{{ $completedTasks }}</span>
                selesai</span>
            </div>
          @else
            <p class="mt-1 text-xs text-zinc-400">Belum ada tugas.</p>
          @endif
        </div>
      </div>

      {{-- Card 2: Tugas Selesai --}}
      <div
        class="relative overflow-hidden flex items-start gap-3 rounded-xl border bg-white p-4 shadow-sm dark:bg-zinc-900
          {{ $completedTasks > 0 ? 'border-emerald-200 dark:border-emerald-700/50' : 'border-zinc-200 dark:border-zinc-700' }}">

        <div class="min-w-0 flex-1">
          <p class="text-xs text-zinc-500 dark:text-zinc-400">Tugas Selesai</p>
          <p
            class="tabular-nums text-2xl font-bold tracking-tight {{ $completedTasks > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-900 dark:text-zinc-50' }}">
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
                  class="tabular-nums font-semibold text-emerald-600 dark:text-emerald-400">{{ $completedOnTime }}</span>
                tepat waktu</span>
              <span class="text-zinc-300 dark:text-zinc-600">·</span>
              <span><span class="tabular-nums font-semibold text-red-500 dark:text-red-400">{{ $completedLate }}</span>
                terlambat</span>
              <span class="text-zinc-300 dark:text-zinc-600">·</span>
              <span><span
                  class="tabular-nums font-semibold text-zinc-500 dark:text-zinc-400">{{ $completedNoDeadline }}</span>
                tanpa tenggat</span>
            </div>
          @else
            <p class="mt-1 text-xs text-zinc-400">Belum ada yang selesai.</p>
          @endif
        </div>
      </div>

      {{-- Card 3: Melewati Tenggat --}}
      <div
        class="flex items-start gap-3 rounded-xl border bg-white p-4 shadow-sm dark:bg-zinc-900
          {{ $overdueTasks > 0 ? 'border-red-200 dark:border-red-800/50' : 'border-zinc-200 dark:border-zinc-700' }}">

        <div class="min-w-0 flex-1">
          <p class="text-xs text-zinc-500 dark:text-zinc-400">Melewati Tenggat</p>
          <p
            class="tabular-nums text-2xl font-bold tracking-tight {{ $overdueTasks > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-50' }}">
            {{ $overdueTasks }}</p>
          <div class="mt-1 text-xs text-zinc-400">
            @if ($totalTasks > 0)
              <span
                class="tabular-nums font-semibold {{ $overduePercent > 0 ? 'text-red-500 dark:text-red-400' : 'text-zinc-500' }}">{{ $overduePercent }}%</span>
              dari total tugas
            @else
              Belum ada tugas.
            @endif
          </div>
        </div>
      </div>

      {{-- Card 4: Penyelesaian --}}
      <div
        class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="min-w-0 flex-1">
          <p class="text-xs text-zinc-500 dark:text-zinc-400">Penyelesaian</p>
          <p
            class="tabular-nums text-2xl font-bold tracking-tight
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
              <span class="tabular-nums font-semibold text-zinc-600 dark:text-zinc-300">{{ $completedTasks }}</span>
              dari {{ $totalTasks }} tugas selesai
            </p>
          @else
            <p class="mt-1 text-xs text-zinc-400">Belum ada tugas.</p>
          @endif
        </div>
      </div>
    </div>

    {{-- ─── Task groups ────────────────────────────────────── --}}
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
                <span><span class="tabular-nums">{{ $group['total'] }}</span> tugas</span>
                <span>·</span>
                <span
                  class="{{ $group['completed'] > 0 ? 'text-emerald-600 dark:text-emerald-400 font-medium' : '' }}">
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

          {{-- Tasks table --}}
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
                              default => 'Belum Dimulai',
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
                          <div class="mt-0.5 text-xs text-red-500">Melewati Tenggat</div>
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
                      {{ $task['hours'] > 0 ? $task['hours'] . ' jam' : '–' }}
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
          Tidak ada data tugas untuk periode ini.
        </div>
      @endforelse
    </div>

    {{-- ─── View: Peringkat Tim (Leaderboard) ─────────────── --}}
  @elseif ($view === 'member')
    {{-- ─── Summary stat cards ────────────────────────────── --}}
    <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">

      {{-- Anggota Aktif --}}
      <div
        class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="min-w-0 flex-1">
          <p class="text-xs text-zinc-500 dark:text-zinc-400">Anggota Aktif</p>
          <p class="tabular-nums text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
            {{ $totalMembers }}</p>
          <p class="mt-0.5 text-xs text-zinc-400">memiliki tugas bulan ini</p>
        </div>
      </div>

      {{-- Total Tugas --}}
      <div
        class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

        <div class="min-w-0 flex-1">
          <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Tugas</p>
          <p class="tabular-nums text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
            {{ $totalTasks }}</p>
          <p class="mt-0.5 text-xs text-zinc-400">ditetapkan ke anggota</p>
        </div>
      </div>

      {{-- Tugas Selesai --}}
      <div
        class="flex items-start gap-3 rounded-xl border p-4 shadow-sm
          {{ $completedTasks > 0 ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-700/50 dark:bg-emerald-900/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">

        <div class="min-w-0 flex-1">
          <p class="text-xs text-zinc-500 dark:text-zinc-400">Tugas Selesai</p>
          <p
            class="tabular-nums text-2xl font-bold tracking-tight {{ $completedTasks > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-900 dark:text-zinc-50' }}">
            {{ $completedTasks }}</p>
          <p
            class="mt-0.5 text-xs {{ $completedTasks > 0 ? 'text-emerald-600/70 dark:text-emerald-400/70' : 'text-zinc-400' }}">
            {{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}% dari total tugas
          </p>
        </div>
      </div>

      {{-- Melewati Tenggat --}}
      <div
        class="flex items-start gap-3 rounded-xl border p-4 shadow-sm
          {{ $overdueTasks > 0 ? 'border-red-200 bg-red-50/30 dark:border-red-800/50 dark:bg-red-900/5' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">

        <div class="min-w-0 flex-1">
          <p class="text-xs text-zinc-500 dark:text-zinc-400">Melewati Tenggat</p>
          <p
            class="tabular-nums text-2xl font-bold tracking-tight {{ $overdueTasks > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-50' }}">
            {{ $overdueTasks }}</p>
          <p class="mt-0.5 text-xs text-zinc-400">
            {{ $overdueTasks > 0 ? 'perlu segera diselesaikan' : 'Semua tugas tepat waktu' }}
          </p>
        </div>
      </div>
    </div>

    {{-- ─── Leaderboard ────────────────────────────────────── --}}
    @if ($memberStats->isEmpty())
      <div
        class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center text-sm text-zinc-400 dark:border-zinc-700 dark:bg-zinc-900">
        Tidak ada data anggota untuk periode ini.
      </div>
    @else
      {{-- Leaderboard header --}}
      <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
          <flux:icon name="trophy" class="size-5 text-amber-500" />
          <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Papan Peringkat Anggota</h3>
        </div>
        <span class="text-xs text-zinc-400">
          {{ $memberStats->count() }} anggota · diurutkan berdasarkan tugas selesai
        </span>
      </div>

      {{-- Top 3 podium cards --}}
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
            $allDone = $member['total'] > 0 && $member['progress'] >= 100;
          @endphp
          <div
            class="relative overflow-hidden rounded-xl border-2 p-5 shadow-sm transition {{ $style['border'] }} {{ $style['bg'] }}"
            wire:key="podium-{{ $member['user']->id }}">

            {{-- Medal + rank badge --}}
            @php $mc = $medalColors[$rankIndex]; @endphp
            <div class="mb-4 flex items-start justify-between">
              <svg viewBox="0 0 32 36" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-9 shrink-0">
                <rect x="10" y="0" width="5" height="14" rx="2" fill="{{ $mc['ribbonA'] }}" />
                <rect x="17" y="0" width="5" height="14" rx="2" fill="{{ $mc['ribbonB'] }}" />
                <circle cx="16" cy="24" r="11" fill="{{ $mc['body'] }}"
                  stroke="{{ $mc['stroke'] }}" stroke-width="1.5" />
                <circle cx="16" cy="24" r="7" fill="none" stroke="{{ $mc['stroke'] }}"
                  stroke-width="1" stroke-opacity="0.4" />
              </svg>
              <span
                class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $style['badge'] }}">#{{ $rankIndex + 1 }}</span>
            </div>

            {{-- Avatar + name --}}
            <div class="mb-4 flex flex-col items-center text-center">
              <flux:avatar circle size="lg" :name="$member['user']->name" :initials="$member['user']->initials()"
                :src="$member['user']->avatar" class="mb-2" />
              <p class="font-semibold leading-snug text-zinc-800 dark:text-zinc-100">{{ $member['user']->name }}</p>
              @if ($allDone)
                <span
                  class="mt-1 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                  <svg class="size-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5" />
                  </svg>
                  Semua Selesai
                </span>
              @endif
            </div>

            {{-- Score (completion %) --}}
            <div class="mb-3 text-center">
              <p class="tabular-nums text-3xl font-bold {{ $style['score'] }}">{{ $member['progress'] }}%</p>
              <p class="text-[11px] text-zinc-400 dark:text-zinc-500">tingkat penyelesaian</p>
            </div>

            {{-- Progress bar --}}
            <div class="mb-4 h-2 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
              <div
                class="h-full rounded-full transition-all duration-700
                  {{ $allDone ? 'bg-emerald-500' : ($member['progress'] >= 70 ? 'bg-teal-500' : ($member['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500')) }}"
                style="width: {{ $member['progress'] }}%"></div>
            </div>

            {{-- 3-col stats --}}
            <div class="grid grid-cols-2 gap-2 border-t pt-3 text-center {{ $style['divider'] }}">
              <div>
                <p class="tabular-nums text-sm font-bold text-zinc-800 dark:text-zinc-100">
                  {{ $member['completed'] }}<span
                    class="text-xs font-normal text-zinc-400">/{{ $member['total'] }}</span>
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

      {{-- Ranked list for #4 and beyond --}}
      @if ($memberStats->count() > 3)
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

                {{-- Rank number --}}
                <div
                  class="flex size-7 shrink-0 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                  <span
                    class="tabular-nums text-xs font-bold text-zinc-500 dark:text-zinc-400">#{{ $loop->index + 4 }}</span>
                </div>

                {{-- Avatar --}}
                <flux:avatar circle size="sm" :name="$member['user']->name"
                  :initials="$member['user']->initials()" :src="$member['user']->avatar" />

                {{-- Name --}}
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

                {{-- Progress bar --}}
                <div class="hidden sm:flex w-36 shrink-0 items-center gap-2">
                  <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-700">
                    <div
                      class="h-full rounded-full {{ $allDone ? 'bg-emerald-500' : ($member['progress'] >= 70 ? 'bg-teal-500' : ($member['progress'] >= 40 ? 'bg-amber-500' : 'bg-red-500')) }}"
                      style="width: {{ $member['progress'] }}%"></div>
                  </div>
                  <span
                    class="tabular-nums w-8 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400">{{ $member['progress'] }}%</span>
                </div>

                {{-- Task count --}}
                <div class="hidden md:block w-20 shrink-0 text-right">
                  <p class="tabular-nums text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                    {{ $member['completed'] }}/{{ $member['total'] }}</p>
                  <p class="text-[10px] text-zinc-400">selesai</p>
                </div>

                {{-- Overdue --}}
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
      @endif
    @endif

  @endif
</div>
