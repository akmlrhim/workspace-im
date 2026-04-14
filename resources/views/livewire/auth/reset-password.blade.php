<x-layouts::auth :title="__('Atur Ulang Password')">
  <div class="flex flex-col gap-6">
    <x-auth-header :title="__('Atur Ulang Password')" :description="__('Masukkan password baru Anda di bawah ini')" />
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
      @csrf
      <flux:input type="hidden" name="token" value="{{ request()->route('token') }}" />
      <flux:input name="email" value="{{ request('email') }}" :label="__('Email')" type="email" required
        autocomplete="email" />
      <div class="flex flex-col gap-1">
        <flux:input name="password" :label="__('Password')" type="password" required autocomplete="new-password"
          :placeholder="__('Password')" viewable />
        @if (app()->isProduction())
          <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ __('Minimal 12 karakter yang terdiri dari huruf besar, huruf kecil, angka, dan simbol.') }}
          </flux:text>
        @else
          <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ __('Minimal 8 karakter.') }}
          </flux:text>
        @endif
      </div>
      <flux:input name="password_confirmation" :label="__('Konfirmasi Password')" type="password" required
        autocomplete="new-password" :placeholder="__('Konfirmasi password')" viewable />

      <div class="flex items-center justify-end">
        <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
          {{ __('Atur Ulang Password') }}
        </flux:button>
      </div>
    </form>
  </div>
</x-layouts::auth>
