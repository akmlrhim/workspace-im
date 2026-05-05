<x-layouts::auth.split :title="__('Daftar')">

  @php
    $inputClass = "block w-full rounded-xl border px-4 py-3 text-sm shadow-sm outline-none transition hover:border-zinc-300 dark:hover:border-zinc-600 focus:ring-2 border-zinc-200 bg-white text-zinc-900 placeholder-zinc-400 focus:border-indigo-500 focus:ring-indigo-500/20 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500";
    $inputErrorClass = "block w-full rounded-xl border px-4 py-3 text-sm shadow-sm outline-none transition focus:ring-2 border-red-400 bg-red-50 text-zinc-900 focus:border-red-500 focus:ring-red-500/20 dark:border-red-500/60 dark:bg-red-900/20 dark:text-zinc-100";
    $labelClass = "block text-sm font-medium text-zinc-700 dark:text-zinc-300";
  @endphp

  <div class="space-y-7">

    {{-- Header --}}
    <div>
      <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Buat akun baru</h2>
      <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Isi data di bawah untuk membuat akun Anda</p>
    </div>

    {{-- Form --}}
    <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
      @csrf

      {{-- Nama --}}
      <div class="space-y-1.5">
        <label for="name" class="{{ $labelClass }}">Nama Lengkap</label>
        <input id="name" name="name" type="text" value="{{ old('name') }}"
          placeholder="John Doe" required autofocus autocomplete="name"
          class="{{ $errors->has('name') ? $inputErrorClass : $inputClass }}" />
        @error('name')
          <p class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" /></svg>
            {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Email --}}
      <div class="space-y-1.5">
        <label for="email" class="{{ $labelClass }}">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}"
          placeholder="nama@perusahaan.com" required autocomplete="email"
          class="{{ $errors->has('email') ? $inputErrorClass : $inputClass }}" />
        @error('email')
          <p class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" /></svg>
            {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Password --}}
      <div class="space-y-1.5" x-data="{ show: false }">
        <label for="password" class="{{ $labelClass }}">Password</label>
        <div class="relative">
          <input id="password" name="password" :type="show ? 'text' : 'password'"
            placeholder="••••••••" required autocomplete="new-password"
            class="{{ $errors->has('password') ? $inputErrorClass . ' pr-12' : $inputClass . ' pr-12' }}" />
          <button type="button" tabindex="-1" @click="show = !show"
            class="absolute inset-y-0 right-0 flex items-center px-4 text-zinc-400 transition hover:text-zinc-600 dark:hover:text-zinc-300">
            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" /></svg>
          </button>
        </div>
        <p class="text-xs text-zinc-400 dark:text-zinc-500">
          @if (app()->isProduction())
            Minimal 12 karakter: huruf besar, kecil, angka, dan simbol.
          @else
            Minimal 8 karakter.
          @endif
        </p>
        @error('password')
          <p class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" /></svg>
            {{ $message }}
          </p>
        @enderror
      </div>

      {{-- Konfirmasi Password --}}
      <div class="space-y-1.5" x-data="{ show2: false }">
        <label for="password_confirmation" class="{{ $labelClass }}">Konfirmasi Password</label>
        <div class="relative">
          <input id="password_confirmation" name="password_confirmation"
            :type="show2 ? 'text' : 'password'" placeholder="••••••••"
            required autocomplete="new-password"
            class="{{ $errors->has('password_confirmation') ? $inputErrorClass . ' pr-12' : $inputClass . ' pr-12' }}" />
          <button type="button" tabindex="-1" @click="show2 = !show2"
            class="absolute inset-y-0 right-0 flex items-center px-4 text-zinc-400 transition hover:text-zinc-600 dark:hover:text-zinc-300">
            <svg x-show="!show2" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
            <svg x-show="show2" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 4.411m0 0L21 21" /></svg>
          </button>
        </div>
        @error('password_confirmation')
          <p class="flex items-center gap-1 text-xs text-red-600 dark:text-red-400">
            <svg class="h-3.5 w-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" clip-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" /></svg>
            {{ $message }}
          </p>
        @enderror
      </div>

      <button type="submit" data-test="register-user-button"
        class="flex w-full items-center justify-center rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-zinc-950 active:scale-[0.98]">
        Buat Akun
      </button>
    </form>

    {{-- Login link --}}
    <p class="text-center text-sm text-zinc-500 dark:text-zinc-400">
      Sudah punya akun?
      <a href="{{ route('login') }}" wire:navigate
        class="font-medium text-indigo-600 transition hover:text-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300">
        Masuk sekarang
      </a>
    </p>

  </div>

</x-layouts::auth.split>
