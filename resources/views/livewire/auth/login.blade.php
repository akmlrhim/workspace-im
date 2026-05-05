<x-layouts::auth.split :title="__('Masuk')">

  <div class="space-y-7">

    {{-- Header --}}
    <div>
      <h2 class="text-2xl text-center font-bold text-zinc-900 dark:text-white">Selamat datang kembali</h2>
      <p class="mt-1 text-sm text-center text-zinc-500 dark:text-zinc-400">Masuk ke akun anda untuk melanjutkan</p>
    </div>

    {{-- Session status --}}
    @if (session('status'))
      <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
        {{ session('status') }}
      </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
      @csrf

      {{-- Email --}}
      <div class="space-y-1.5">
        <label for="email" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@gmail.com"
          autofocus autocomplete="email"
          class="block w-full rounded-xl border px-4 py-3 text-sm shadow-sm outline-none transition hover:border-zinc-300 dark:hover:border-zinc-600 focus:ring-2
            {{ $errors->has('email') ? 'border-red-400 bg-red-50 text-zinc-900 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500/60 dark:bg-red-900/20 dark:text-zinc-100' : 'border-zinc-200 bg-white text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500' }}" />
        @error('email')
          <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" />
            </svg>
            {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Password --}}
      <div class="space-y-1.5" x-data="{ show: false }">
        <div class="flex items-center justify-between">
          <label for="password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Password</label>
          @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" wire:navigate
              class="text-xs font-medium text-indigo-600 transition hover:text-indigo-500">
              Lupa password?
            </a>
          @endif
        </div>
        <div class="relative">
          <input id="password" name="password" :type="show ? 'text' : 'password'" placeholder="••••••••"
            autocomplete="current-password"
            class="block w-full rounded-xl border px-4 py-3 pr-12 text-sm shadow-sm outline-none transition hover:border-zinc-300 dark:hover:border-zinc-600 focus:ring-2
              {{ $errors->has('password') ? 'border-red-400 bg-red-50 text-zinc-900 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500/60 dark:bg-red-900/20 dark:text-zinc-100' : 'border-zinc-200 bg-white text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500' }}" />
          <button type="button" tabindex="-1" @click="show = !show"
            class="absolute inset-y-0 right-0 flex items-center px-4 text-zinc-400 transition hover:text-zinc-600">
            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
              stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" />
            </svg>
          </button>
        </div>
        @error('password')
          <p class="flex items-center gap-1 text-xs text-red-600">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" clip-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" />
            </svg>
            {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Remember me --}}
      <div class="flex items-center gap-2.5">
        <input id="remember" name="remember" type="checkbox" {{ old('remember') ? 'checked' : '' }}
          class="h-4 w-4 cursor-pointer rounded border-zinc-300 accent-indigo-600" />
        <label for="remember" class="cursor-pointer select-none text-sm text-zinc-600 dark:text-zinc-400">Ingat
          saya</label>
      </div>

      {{-- Submit --}}
      <button type="submit" data-test="login-button"
        class="flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 active:scale-[0.98] cursor-pointer">
        Masuk
      </button>
    </form>

    {{-- Divider --}}
    <div class="relative flex items-center gap-3">
      <div class="flex-1 border-t border-zinc-200"></div>
      <span class="text-xs font-medium uppercase tracking-wider text-zinc-400">atau</span>
      <div class="flex-1 border-t border-zinc-200"></div>
    </div>

    {{-- Google --}}
    <a href="{{ route('auth.google') }}"
      class="flex w-full items-center justify-center gap-3 rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-medium text-zinc-700 shadow-sm transition hover:border-zinc-300 hover:bg-zinc-50 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 active:scale-[0.98] dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:border-zinc-600 dark:hover:bg-zinc-700/60">
      <svg class="h-4 w-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <path fill="#4285F4"
          d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
        <path fill="#34A853"
          d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
        <path fill="#FBBC05"
          d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
        <path fill="#EA4335"
          d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
      </svg>
      Lanjutkan dengan Google
    </a>

    {{-- Register link --}}
    @if (Route::has('register'))
      <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
        Belum punya akun?
        <a href="{{ route('register') }}" wire:navigate
          class="font-medium text-indigo-600 transition hover:text-indigo-500">
          Daftar sekarang
        </a>
      </p>
    @endif

  </div>

</x-layouts::auth.split>
