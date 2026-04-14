<x-layouts::auth :title="__('Konfirmasi Password')">
  <div class="flex flex-col gap-6">
    <x-auth-header :title="__('Konfirmasi Password')" :description="__('Silakan konfirmasi password Anda sebelum melanjutkan.')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" action="{{ route('password.confirm.store') }}" class="flex flex-col gap-6">
      @csrf

      <flux:input name="password" :label="__('Password')" type="password" required autocomplete="current-password"
        :placeholder="__('Password')" viewable />

      <flux:button variant="primary" type="submit" class="w-full" data-test="confirm-password-button">
        {{ __('Confirm') }}
      </flux:button>
    </form>
  </div>
</x-layouts::auth>
