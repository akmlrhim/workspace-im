<x-layouts::auth.split :title="__('Lupa Password')">

  <div class="space-y-7">

    {{-- Back link --}}
    <a href="{{ route('login') }}" wire:navigate
      class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200">
      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
      Kembali ke halaman masuk
    </a>

    {{-- Icon + header --}}
    <div class="space-y-4">
      <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 dark:bg-indigo-900/30">
        <svg class="h-7 w-7 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
        </svg>
      </div>
      <div>
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Lupa password?</h2>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
          Masukkan email Anda dan kami akan mengirimkan link untuk mengatur ulang password.
        </p>
      </div>
    </div>

    {{-- Session status --}}
    @if (session('status'))
      <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/50 dark:bg-green-900/20 dark:text-green-400">
        {{ session('status') }}
      </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
      @csrf

      <div class="space-y-1.5">
        <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
        <input
          id="email"
          name="email"
          type="email"
          value="{{ old('email') }}"
          placeholder="nama@perusahaan.com"
          required
          autofocus
          autocomplete="email"
          class="block w-full rounded-xl border px-4 py-3 text-sm shadow-sm outline-none transition hover:border-zinc-300 dark:hover:border-zinc-600 focus:ring-2
            {{ $errors->has('email') ? 'border-red-400 bg-red-50 text-zinc-900 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500/60 dark:bg-red-900/20 dark:text-zinc-100' : 'border-zinc-200 bg-white text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500' }}"
        />
        @error('email')
          <p class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" />
            </svg>
            {{ $message }}
          </p>
        @enderror
      </div>

      <button type="submit" data-test="email-password-reset-link-button"
        class="flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-zinc-950 active:scale-[0.98]">
        Kirim Link Reset Password
      </button>
    </form>

  </div>

</x-layouts::auth.split>
