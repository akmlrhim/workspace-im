<x-layouts::app :title="__('Finance Dashboard')">
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Finance Dashboard</h1>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Ringkasan keuangan & transaksi perusahaan</p>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
      @foreach ([
        ['label' => 'Total Invoice', 'value' => 'Rp 1,24 M', 'icon' => 'document-duplicate', 'color' => 'text-blue-500', 'bg' => 'bg-blue-500/10', 'trend' => '24 invoice aktif'],
        ['label' => 'Total Pengeluaran', 'value' => 'Rp 485 Jt', 'icon' => 'credit-card', 'color' => 'text-red-500', 'bg' => 'bg-red-500/10', 'trend' => 'Bulan ini'],
        ['label' => 'Profit Bersih', 'value' => 'Rp 755 Jt', 'icon' => 'trending-up', 'color' => 'text-emerald-500', 'bg' => 'bg-emerald-500/10', 'trend' => '+12% vs bulan lalu'],
        ['label' => 'Tagihan Jatuh Tempo', 'value' => '7', 'icon' => 'exclamation-circle', 'color' => 'text-amber-500', 'bg' => 'bg-amber-500/10', 'trend' => 'Perlu tindakan'],
      ] as $s)
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

    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
      <h2 class="mb-4 text-sm font-semibold text-zinc-900 dark:text-white">Invoice Terbaru</h2>
      <div class="text-sm text-zinc-400 dark:text-zinc-500 text-center py-8">
        <flux:icon name="document-duplicate" class="mx-auto mb-2 size-8 opacity-40" />
        <p>Fitur sedang dalam pengembangan</p>
      </div>
    </div>
  </div>
</x-layouts::app>
