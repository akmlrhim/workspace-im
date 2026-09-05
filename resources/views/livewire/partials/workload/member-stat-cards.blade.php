<div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">

  <div
    class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
    <div class="min-w-0 flex-1">
      <p class="text-xs text-zinc-500 dark:text-zinc-400">Anggota Aktif</p>
      <p class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
        {{ $totalMembers }}</p>
      <p class="mt-0.5 text-xs text-zinc-400">memiliki tugas bulan ini</p>
    </div>
  </div>

  <div
    class="flex items-start gap-3 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

    <div class="min-w-0 flex-1">
      <p class="text-xs text-zinc-500 dark:text-zinc-400">Total Tugas</p>
      <p class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
        {{ $totalTasks }}</p>
      <p class="mt-0.5 text-xs text-zinc-400">ditetapkan ke anggota</p>
    </div>
  </div>

  <div
    class="flex items-start gap-3 rounded-xl border p-4 shadow-sm
      {{ $completedTasks > 0 ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-700/50 dark:bg-emerald-900/10' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">

    <div class="min-w-0 flex-1">
      <p class="text-xs text-zinc-500 dark:text-zinc-400">Tugas Selesai</p>
      <p
        class="text-2xl font-bold tracking-tight {{ $completedTasks > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-900 dark:text-zinc-50' }}">
        {{ $completedTasks }}</p>
      <p
        class="mt-0.5 text-xs {{ $completedTasks > 0 ? 'text-emerald-600/70 dark:text-emerald-400/70' : 'text-zinc-400' }}">
        {{ $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0 }}% dari total tugas
      </p>
    </div>
  </div>

  <div
    class="flex items-start gap-3 rounded-xl border p-4 shadow-sm
      {{ $overdueTasks > 0 ? 'border-red-200 bg-red-50/30 dark:border-red-800/50 dark:bg-red-900/5' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">

    <div class="min-w-0 flex-1">
      <p class="text-xs text-zinc-500 dark:text-zinc-400">Melewati Tenggat</p>
      <p
        class="text-2xl font-bold tracking-tight {{ $overdueTasks > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-50' }}">
        {{ $overdueTasks }}</p>
      <p class="mt-0.5 text-xs text-zinc-400">
        {{ $overdueTasks > 0 ? 'perlu segera diselesaikan' : 'Semua tugas tepat waktu' }}
      </p>
    </div>
  </div>
</div>
