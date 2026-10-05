<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  @include('partials.head')
</head>

<body class="min-h-screen bg-zinc-50 antialiased dark:bg-zinc-950">
  <div class="flex min-h-screen items-center justify-center bg-zinc-50 px-6 py-12 dark:bg-zinc-950">
    <div class="w-full max-w-[400px]">
      {{ $slot }}
    </div>
  </div>

  @fluxScripts
</body>

</html>
