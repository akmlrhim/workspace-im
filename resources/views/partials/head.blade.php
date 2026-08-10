<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
  {{ filled($title ?? null) ? $title . ' - ' . config('app.name', 'Laravel') : config('app.name', 'Laravel') }}
</title>

{{-- PWA --}}
<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
<meta name="theme-color" content="#09090b" media="(prefers-color-scheme: dark)">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Workspace') }}">

<link rel="icon" href="/favicon/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/favicon/favicon.ico" sizes="32x32">
<link rel="icon" href="/favicon/favicon-96x96.png" type="image/png" sizes="96x96">
<link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">
<link rel="manifest" href="/favicon/site.webmanifest">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
  href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
  rel="stylesheet">

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Pusher WebSocket config — read at runtime by echo.js --}}
<meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
<meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster') }}">

{{-- Attachment upload limits — read at runtime by uploads.js, so the browser
     check can never disagree with server-side validation. --}}
<meta name="upload-max-size" content="{{ config('erp.attachments.max_size_kb') * 1024 }}">
<meta name="upload-extensions" content="{{ implode(',', config('erp.attachments.extensions')) }}">

@fluxAppearance
