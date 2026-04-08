<x-layouts::app :title="__('Dashboard')">
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">
        Selamat datang, {{ auth()->user()->name }}
      </h1>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
        Pilih modul yang ingin Anda akses hari ini.
      </p>
    </div>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

      <a href="{{ url('/project-management') }}" wire:navigate
        class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
        <div
          class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 transition-colors group-hover:bg-indigo-100 dark:bg-indigo-500/10 dark:group-hover:bg-indigo-500/20">
          <flux:icon name="folder-open" class="size-6 text-indigo-500" />
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Project</h3>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Kelola space, task list, dan tugas tim Anda.</p>
        <div class="pointer-events-none absolute right-4 top-4 opacity-5 transition-opacity group-hover:opacity-10">
          <flux:icon name="folder-open" class="size-24 text-indigo-600" />
        </div>
      </a>
      <a href="{{ url('/hr') }}" wire:navigate
        class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
        <div
          class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 transition-colors group-hover:bg-emerald-100 dark:bg-emerald-500/10 dark:group-hover:bg-emerald-500/20">
          <flux:icon name="users" class="size-6 text-emerald-500" />
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">HR</h3>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Manajemen karyawan, absensi, payroll, dan cuti.</p>
        <div class="pointer-events-none absolute right-4 top-4 opacity-5 transition-opacity group-hover:opacity-10">
          <flux:icon name="users" class="size-24 text-emerald-600" />
        </div>
      </a>
      <a href="{{ url('/finance') }}" wire:navigate
        class="group relative overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:border-zinc-800 dark:bg-zinc-900">
        <div
          class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 transition-colors group-hover:bg-amber-100 dark:bg-amber-500/10 dark:group-hover:bg-amber-500/20">
          <flux:icon name="banknotes" class="size-6 text-amber-500" />
        </div>
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">Finance</h3>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Pantau invoice, pengeluaran, dan laporan keuangan.</p>
        <div class="pointer-events-none absolute right-4 top-4 opacity-5 transition-opacity group-hover:opacity-10">
          <flux:icon name="banknotes" class="size-24 text-amber-600" />
        </div>
      </a>

    </div>
  </div>
</x-layouts::app>
