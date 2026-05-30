<x-layouts::auth.split :title="__('Autentikasi Dua Faktor')">

  <div class="space-y-7" x-cloak x-data="{
    showRecovery: @js($errors->has('recovery_code')),
    loading: false,
    code: '',
    recovery_code: '',
    toggle() {
      this.showRecovery = !this.showRecovery;
      this.code = '';
      this.recovery_code = '';
      $dispatch('clear-2fa-auth-code');
      $nextTick(() => {
        this.showRecovery
          ? this.$refs.recovery_code?.focus()
          : $dispatch('focus-2fa-auth-code');
      });
    },
  }">

    {{-- Icon + header --}}
    <div class="space-y-4">
      <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800/60">
        <svg class="h-7 w-7 text-zinc-700 dark:text-zinc-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
        </svg>
      </div>
      <div>
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white" x-show="!showRecovery">Kode Autentikasi</h2>
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white" x-show="showRecovery" x-cloak>Kode Pemulihan</h2>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400" x-show="!showRecovery">
          Masukkan kode 6 digit dari aplikasi autentikator Anda.
        </p>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400" x-show="showRecovery" x-cloak>
          Masukkan salah satu kode pemulihan darurat Anda.
        </p>
      </div>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('two-factor.login.store') }}" class="space-y-6" @submit="loading = true">
      @csrf

      {{-- OTP input --}}
      <div x-show="!showRecovery" class="flex justify-center">
        <flux:otp x-model="code" length="6" name="code" label="OTP Code" label:sr-only />
      </div>

      {{-- Recovery code input --}}
      <div x-show="showRecovery" x-cloak class="space-y-1.5">
        <label for="recovery_code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
          Kode Pemulihan
        </label>
        <input
          id="recovery_code"
          type="text"
          name="recovery_code"
          x-ref="recovery_code"
          :required="showRecovery"
          autocomplete="one-time-code"
          x-model="recovery_code"
          placeholder="xxxx-xxxx"
          class="block w-full rounded-xl border px-4 py-3 text-sm shadow-sm outline-none transition hover:border-zinc-300 dark:hover:border-zinc-600 focus:ring-2
            {{ $errors->has('recovery_code') ? 'border-red-400 bg-red-50 text-zinc-900 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500/60 dark:bg-red-900/20 dark:text-zinc-100' : 'border-zinc-200 bg-white text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:ring-zinc-900/10 dark:focus:border-zinc-400 dark:focus:ring-zinc-400/10 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500' }}"
        />
        @error('recovery_code')
          <p class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" /></svg>
            {{ $message }}
          </p>
        @enderror
      </div>

      <button type="submit" :disabled="loading"
        class="flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 active:scale-[0.98] disabled:opacity-75 disabled:cursor-not-allowed dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus:ring-white dark:focus:ring-offset-zinc-950">
        <svg x-show="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span x-show="!loading">Lanjutkan</span>
        <span x-show="loading">Memverifikasi...</span>
      </button>
    </form>

    {{-- Toggle --}}
    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
      <span x-show="!showRecovery">Tidak bisa akses autentikator?</span>
      <span x-show="showRecovery" x-cloak>Sudah punya autentikator?</span>
      <button type="button" @click="toggle()"
        class="ml-1 font-medium text-zinc-900 transition hover:text-zinc-600 dark:text-white dark:hover:text-zinc-300 underline underline-offset-2">
        <span x-show="!showRecovery">Gunakan kode pemulihan</span>
        <span x-show="showRecovery" x-cloak>Gunakan kode autentikator</span>
      </button>
    </p>

  </div>

</x-layouts::auth.split>
