<x-layouts::app :title="__('HR Dashboard')">
  <div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">HR Dashboard</h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Manajemen sumber daya manusia perusahaan</p>
      </div>
      <flux:button icon="plus" variant="primary" disabled>
        Tambah Karyawan
      </flux:button>
    </div>

    {{-- Stats Row --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
      @foreach ([['label' => 'Total Karyawan', 'value' => '128', 'icon' => 'users', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-500/10', 'trend' => '+3 bulan ini'], ['label' => 'Hadir Hari Ini', 'value' => '112', 'icon' => 'check-badge', 'color' => 'text-blue-500', 'bg' => 'bg-blue-500/10', 'trend' => '87.5%'], ['label' => 'Pengajuan Cuti', 'value' => '8', 'icon' => 'calendar-days', 'color' => 'text-amber-500', 'bg' => 'bg-amber-500/10', 'trend' => '3 pending'], ['label' => 'Payroll Bulan Ini', 'value' => 'Rp 285 Jt', 'icon' => 'banknotes', 'color' => 'text-violet-500', 'bg' => 'bg-violet-500/10', 'trend' => 'Belum diproses']] as $s)
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
          <div class="flex items-center gap-3 mb-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg {{ $s['bg'] }}">
              <flux:icon name="{{ $s['icon'] }}" class="size-5 {{ $s['color'] }}" />
            </div>
            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $s['label'] }}</span>
          </div>
          <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $s['value'] }}</div>
          <div class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">{{ $s['trend'] }}</div>
        </div>
      @endforeach
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

      {{-- Employee List Skeleton --}}
      <div class="lg:col-span-2 rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">
          <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">Karyawan Terbaru</h2>
          <flux:button size="xs" variant="ghost" disabled>Lihat Semua</flux:button>
        </div>
        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
          @foreach ([['name' => 'Ahmad Fauzi', 'role' => 'Software Engineer', 'dept' => 'IT', 'status' => 'Aktif'], ['name' => 'Siti Rahayu', 'role' => 'Finance Manager', 'dept' => 'Finance', 'status' => 'Aktif'], ['name' => 'Budi Santoso', 'role' => 'Marketing Lead', 'dept' => 'Marketing', 'status' => 'Cuti'], ['name' => 'Dewi Lestari', 'role' => 'HR Specialist', 'dept' => 'HR', 'status' => 'Aktif'], ['name' => 'Eko Prasetyo', 'role' => 'Project Manager', 'dept' => 'IT', 'status' => 'Aktif']] as $emp)
            <div class="flex items-center gap-3 px-5 py-3">
              <flux:avatar :name="$emp['name']"
                :initials="collect(explode(' ', $emp['name']))->map(fn($w) => strtoupper($w[0]))->take(2)->join('')"
                size="sm" class="shrink-0" />
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-zinc-900 dark:text-white truncate">{{ $emp['name'] }}</div>
                <div class="text-xs text-zinc-400 dark:text-zinc-500 truncate">{{ $emp['role'] }} · {{ $emp['dept'] }}
                </div>
              </div>
              <span
                class="rounded-full px-2 py-0.5 text-[10px] font-medium @if ($emp['status'] === 'Aktif') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 @else bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                {{ $emp['status'] }}
              </span>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Quick Actions & Leave Requests --}}
      <div class="space-y-5">
        {{-- Quick Actions --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 p-4">
          <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">Aksi Cepat</h2>
          <div class="space-y-2">
            @foreach ([['label' => 'Tambah Karyawan', 'icon' => 'user-plus'], ['label' => 'Proses Payroll', 'icon' => 'banknotes'], ['label' => 'Setujui Cuti', 'icon' => 'calendar-days'], ['label' => 'Generate Laporan', 'icon' => 'document-chart-bar']] as $action)
              <button disabled
                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-left text-zinc-600 hover:bg-zinc-50 dark:text-zinc-400 dark:hover:bg-zinc-800 cursor-not-allowed opacity-50 transition-colors">
                <flux:icon name="{{ $action['icon'] }}" class="size-4 shrink-0" />
                {{ $action['label'] }}
              </button>
            @endforeach
          </div>
        </div>

        {{-- Pending Leave --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 p-4">
          <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-white">Pengajuan Cuti <span
              class="ml-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">3
              pending</span></h2>
          <div class="space-y-2.5">
            @foreach ([['name' => 'Budi Santoso', 'dates' => '5–8 Apr', 'type' => 'Tahunan'], ['name' => 'Rina Hardianti', 'dates' => '12 Apr', 'type' => 'Sakit'], ['name' => 'Yoga Pratama', 'dates' => '15–16 Apr', 'type' => 'Izin']] as $leave)
              <div class="flex items-center gap-2">
                <flux:avatar :name="$leave['name']"
                  :initials="collect(explode(' ', $leave['name']))->map(fn($w) => strtoupper($w[0]))->take(2)->join('')"
                  size="xs" class="shrink-0" />
                <div class="flex-1 min-w-0">
                  <div class="text-xs font-medium text-zinc-700 dark:text-zinc-300 truncate">{{ $leave['name'] }}</div>
                  <div class="text-[10px] text-zinc-400">{{ $leave['dates'] }} · {{ $leave['type'] }}</div>
                </div>
                <div class="flex gap-1">
                  <button disabled
                    class="rounded p-1 text-emerald-500 hover:bg-emerald-50 cursor-not-allowed opacity-50 dark:hover:bg-emerald-900/20">
                    <flux:icon name="check" class="size-3.5" />
                  </button>
                  <button disabled
                    class="rounded p-1 text-red-500 hover:bg-red-50 cursor-not-allowed opacity-50 dark:hover:bg-red-900/20">
                    <flux:icon name="x-mark" class="size-3.5" />
                  </button>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

    </div>
  </div>
</x-layouts::app>
