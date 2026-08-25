@if ($memberStats->isEmpty())
  <div
    class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center text-sm text-zinc-400 dark:border-zinc-700 dark:bg-zinc-900">
    Tidak ada data anggota untuk periode ini.
  </div>
@else
  <div class="mb-4 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <flux:icon name="trophy" class="size-5 text-amber-500" />
      <h3 class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Papan Peringkat Anggota</h3>
    </div>
    <span class="text-xs text-zinc-400">
      {{ $memberStats->count() }} anggota · diurutkan berdasarkan tugas selesai
    </span>
  </div>

  @include('livewire.partials.workload.podium')

  @if ($memberStats->count() > 3)
    @include('livewire.partials.workload.rank-list')
  @endif
@endif
