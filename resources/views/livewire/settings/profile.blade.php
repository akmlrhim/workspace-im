<section class="w-full">
  @include('partials.settings-heading')

  <flux:heading class="sr-only">{{ __('Profile settings') }}</flux:heading>

  <div class="flex items-start max-md:flex-col">
    {{-- Sidebar nav --}}
    <div class="me-10 w-full pb-4 md:w-[220px]">
      <flux:navlist aria-label="{{ __('Settings') }}">
        <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
        <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
        <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
      </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    {{-- Content --}}
    <div class="flex-1 self-stretch max-md:pt-6 space-y-8">

      {{-- Avatar & Identity Card --}}
      <div
        class="flex items-center gap-5 rounded-xl border border-zinc-200 bg-zinc-50 px-6 py-5 dark:border-zinc-700 dark:bg-zinc-800/50">
        <div class="relative shrink-0">
          <flux:avatar :name="auth()->user()->name" :initials="auth()->user()->initials()" :src="auth()->user()->avatar"
            size="lg" class="size-16 text-lg" />
          @if ($this->isGoogleLinked)
            <span
              class="absolute -bottom-1 -right-1 flex size-5 items-center justify-center rounded-full bg-white shadow dark:bg-zinc-900">
              <img src="https://www.google.com/favicon.ico" alt="Google" class="size-3" />
            </span>
          @endif
        </div>
        <div>
          <flux:heading size="lg" class="leading-tight">{{ auth()->user()->name }}</flux:heading>
          <flux:text class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">{{ auth()->user()->email }}</flux:text>
          
          <div class="flex items-center gap-2 mt-1">
            <span class="inline-flex items-center rounded bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-700/50 dark:text-zinc-300">
              Peran: {{ \App\Models\User::roles()[auth()->user()->role] ?? 'Custom' }}
            </span>
            <span class="inline-flex items-center rounded bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-800 dark:bg-zinc-700/50 dark:text-zinc-300">
              Posisi: {{ auth()->user()->position ?? 'Belum Ditugaskan' }}
            </span>
          </div>

          @if ($this->isGoogleOnly)
            <span
              class="mt-1 inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
              <flux:icon name="check-circle" class="size-3" />
              {{ __('Masuk via Google') }}
            </span>
          @elseif ($this->isGoogleLinked)
            <span
              class="mt-1 inline-flex items-center gap-1 rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
              <flux:icon name="check-circle" class="size-3" />
              {{ __('Google terhubung') }}
            </span>
          @endif
        </div>
      </div>

      {{-- Profile Form --}}
      <div>
        <flux:heading>{{ __('Informasi Profil') }}</flux:heading>
        <flux:subheading>{{ __('Perbarui nama dan alamat email akun Anda') }}</flux:subheading>

        <form wire:submit="updateProfileInformation" class="mt-5 max-w-lg space-y-5">
          <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

          <div>
            @if ($this->isGoogleOnly)
              <flux:input :label="__('Email')" type="email" :value="auth()->user()->email" disabled />
              <flux:text class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Email dikelola oleh Google. Untuk mengubah email, set password terlebih dahulu lalu putuskan koneksi Google.') }}
              </flux:text>
            @else
              <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

              @if ($this->hasUnverifiedEmail)
                <div>
                  <flux:text class="mt-4">
                    {{ __('Your email address is unverified.') }}
                    <flux:link class="cursor-pointer text-sm" wire:click.prevent="resendVerificationNotification">
                      {{ __('Click here to re-send the verification email.') }}
                    </flux:link>
                  </flux:text>

                  @if (session('status') === 'verification-link-sent')
                    <flux:text class="mt-2 font-medium !text-green-600 dark:!text-green-400">
                      {{ __('A new verification link has been sent to your email address.') }}
                    </flux:text>
                  @endif
                </div>
              @endif
            @endif
          </div>

          <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit">{{ __('Simpan') }}</flux:button>
            <x-action-message class="text-sm text-zinc-500" on="profile-updated">
              {{ __('Tersimpan.') }}
            </x-action-message>
          </div>
        </form>
      </div>

      <flux:separator variant="subtle" />

      {{-- Connected Accounts --}}
      <div>
        <flux:heading>{{ __('Akun Terhubung') }}</flux:heading>
        <flux:subheading>{{ __('Hubungkan akun sosial untuk masuk dengan mudah') }}</flux:subheading>

        <div class="mt-5 max-w-lg">
          <div
            class="flex items-center justify-between rounded-xl border border-zinc-200 px-5 py-4 dark:border-zinc-700">
            <div class="flex items-center gap-4">
              <div
                class="flex size-10 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-700">
                <svg class="size-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                  <path
                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                    fill="#4285F4" />
                  <path
                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                    fill="#34A853" />
                  <path
                    d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
                    fill="#FBBC05" />
                  <path
                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
                    fill="#EA4335" />
                </svg>
              </div>
              <div>
                <p class="text-sm font-medium text-zinc-900 dark:text-white">Google</p>
                @if ($this->isGoogleLinked)
                  <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Terhubung') }}</p>
                @else
                  <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Belum terhubung') }}</p>
                @endif
              </div>
            </div>

            @if ($this->isGoogleLinked)
              @if ($this->isGoogleOnly)
                <flux:tooltip content="{{ __('Set password terlebih dahulu di halaman Security') }}">
                  <div>
                    <flux:button variant="ghost" size="sm" disabled class="text-zinc-400">
                      {{ __('Putuskan') }}
                    </flux:button>
                  </div>
                </flux:tooltip>
              @else
                <form method="POST" action="{{ route('auth.google.unlink') }}">
                  @csrf
                  <flux:button type="submit" variant="ghost" size="sm"
                    class="text-red-600 hover:text-red-700 dark:text-red-400">
                    {{ __('Putuskan') }}
                  </flux:button>
                </form>
              @endif
            @else
              <flux:button tag="a" :href="route('auth.google.link')" variant="outline" size="sm"
                icon="link">
                {{ __('Hubungkan') }}
              </flux:button>
            @endif
          </div>

          @if ($this->isGoogleOnly)
            <flux:callout variant="warning" icon="exclamation-triangle" class="mt-3">
              <flux:callout.text>
                {{ __('Google adalah satu-satunya cara login Anda. Set password di halaman') }}
                <flux:callout.link :href="route('security.edit')" wire:navigate>{{ __('Security') }}
                </flux:callout.link>
                {{ __('sebelum memutuskan koneksi Google.') }}
              </flux:callout.text>
            </flux:callout>
          @endif
        </div>
      </div>

      @if ($this->showDeleteUser)
        <flux:separator variant="subtle" />
        <livewire:settings.delete-user-form />
      @endif

    </div>
  </div>
</section>
