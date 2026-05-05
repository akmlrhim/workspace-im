<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-950">
  <div class="min-h-screen lg:grid lg:grid-cols-[440px_1fr] xl:grid-cols-[500px_1fr]">

    {{-- ─── Left branding panel ─────────────────────────────── --}}
    <div class="relative hidden flex-col overflow-hidden p-10 lg:flex">

      {{-- Background photo --}}
      <img src="/login.webp" alt=""
        class="absolute inset-0 h-full w-full object-cover object-center" />

      {{-- Dark gradient overlay for readability --}}
      <div class="absolute inset-0 bg-gradient-to-br from-indigo-950/90 via-indigo-900/80 to-violet-900/85"></div>

      {{-- Subtle dot pattern on top --}}
      <svg class="absolute inset-0 h-full w-full opacity-30" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <pattern id="dots" x="0" y="0" width="24" height="24" patternUnits="userSpaceOnUse">
            <circle cx="2" cy="2" r="1.2" fill="rgba(255,255,255,0.12)" />
          </pattern>
        </defs>
        <rect width="100%" height="100%" fill="url(#dots)" />
      </svg>

      {{-- Logo --}}
      <div class="relative z-10 flex items-center gap-3">
        <div
          class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20 backdrop-blur-sm">
          <img src="/logo_original.webp" alt="{{ config('app.name') }}"
            class="h-6 w-6 object-contain brightness-0 invert" />
        </div>
        <span class="text-lg font-bold tracking-tight text-white">{{ config('app.name') }}</span>
      </div>
    </div>

    <div class="flex min-h-screen items-center justify-center bg-zinc-50 px-6 py-12 dark:bg-zinc-950 lg:px-16">
      <div class="w-full max-w-[400px]">

        <div class="mb-8 flex items-center justify-center gap-3 lg:hidden">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600">
            <img src="/logo_original.webp" alt="{{ config('app.name') }}"
              class="h-6 w-6 object-contain brightness-0 invert" />
          </div>
        </div>

        {{ $slot }}
      </div>
    </div>

  </div>

  @fluxScripts
</body>

</html>
