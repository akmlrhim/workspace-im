<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('code', 'Error') — {{ config('app.name') }}</title>
  <link rel="icon" href="/favicon/favicon.svg" type="image/svg+xml">
  <link rel="icon" href="/favicon/favicon.ico" sizes="32x32">
  <link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">
  <link rel="manifest" href="/favicon/site.webmanifest">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap"
    rel="stylesheet">

  <style>
    *,
    *::before,
    *::after {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    :root {
      --bg: #ffffff;
      --text: #18181b;
      --muted: #71717a;
      --subtle: #a1a1aa;
      --accent: #6366f1;
    }

    @media (prefers-color-scheme: dark) {
      :root {
        --bg: #09090b;
        --text: #fafafa;
        --muted: #a1a1aa;
        --subtle: #52525b;
      }
    }

    html,
    body {
      height: 100%;
      font-family: 'Geist', system-ui, sans-serif;
      background: var(--bg);
      color: var(--text);
      -webkit-font-smoothing: antialiased;
    }

    body {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      min-height: 100dvh;
      padding: 2rem;
      gap: 0;
      text-align: center;
    }

    .code {
      font-size: clamp(4.5rem, 20vw, 8rem);
      font-weight: 800;
      letter-spacing: -.05em;
      line-height: 1;
      color: var(--text);
      margin-bottom: 1rem;
    }

    .title {
      font-size: 1.125rem;
      font-weight: 600;
      color: var(--text);
      margin-bottom: .5rem;
    }

    .desc {
      font-size: .9375rem;
      color: var(--muted);
      line-height: 1.6;
      max-width: 360px;
      margin-bottom: 2rem;
    }

    .actions {
      display: flex;
      gap: .75rem;
      flex-wrap: wrap;
      justify-content: center;
    }

    a.link {
      font-size: .875rem;
      font-weight: 500;
      color: var(--accent);
      text-decoration: none;
      border-bottom: 1px solid transparent;
      transition: border-color .15s;
    }

    a.link:hover {
      border-color: var(--accent);
    }

    a.link-muted {
      font-size: .875rem;
      font-weight: 500;
      color: var(--subtle);
      text-decoration: none;
      border-bottom: 1px solid transparent;
      transition: color .15s, border-color .15s;
    }

    a.link-muted:hover {
      color: var(--muted);
      border-color: var(--muted);
    }

    footer {
      position: fixed;
      bottom: 1.5rem;
      font-size: .75rem;
      color: var(--subtle);
    }
  </style>
</head>

<body>
  <div class="code">@yield('code', '?')</div>
  <p class="title">@yield('title', 'Terjadi Kesalahan')</p>
  <p class="desc">@yield('description', 'Silakan coba lagi.')</p>
  <div class="actions">@yield('actions')</div>
  <footer>{{ config('app.name') }}</footer>
</body>

</html>
