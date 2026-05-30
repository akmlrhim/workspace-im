<x-layouts::auth.split :title="__('Verifikasi Email')">

  <div class="space-y-7">

    {{-- Icon + header --}}
    <div class="space-y-4">
      <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800/60">
        <svg class="h-7 w-7 text-zinc-700 dark:text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
        </svg>
      </div>
      <div>
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Verifikasi email Anda</h2>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
          Kami telah mengirimkan link verifikasi ke email Anda. Silakan cek inbox atau folder spam.
        </p>
      </div>
    </div>

    {{-- Success status --}}
    @if (session('status') == 'verification-link-sent')
      <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/50 dark:bg-green-900/20 dark:text-green-400">
        Link verifikasi baru telah dikirim ke alamat email Anda.
      </div>
    @endif

    {{-- Info box --}}
    <div class="rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3.5 dark:border-zinc-700/60 dark:bg-zinc-800/50">
      <p class="text-sm text-zinc-600 dark:text-zinc-400">
        Belum menerima email? Periksa folder spam, atau klik tombol di bawah untuk mengirim ulang.
      </p>
    </div>

    {{-- Actions --}}
    <div class="space-y-3">
      <form method="POST" action="{{ route('verification.send') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <button type="submit" :disabled="loading"
          class="flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-75 disabled:cursor-not-allowed dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus:ring-white dark:focus:ring-offset-zinc-950">
          <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span x-show="!loading">Kirim Ulang Email Verifikasi</span>
          <span x-show="loading" x-cloak>Mengirim...</span>
        </button>
      </form>

      <form method="POST" action="{{ route('logout') }}" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <button type="submit" data-test="logout-button" :disabled="loading"
          class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-700 shadow-sm transition hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-zinc-300 disabled:opacity-75 disabled:cursor-not-allowed dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700/60 active:scale-[0.98]">
          <svg x-show="loading" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          <span x-show="!loading">Keluar</span>
          <span x-show="loading" x-cloak>Keluar...</span>
        </button>
      </form>
    </div>

  </div>

</x-layouts::auth.split>
