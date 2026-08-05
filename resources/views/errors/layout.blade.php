<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('code') · TurTube</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: radial-gradient(circle at top, #351018, #09090b 55%); color: #f4f4f5; }
        main { width: min(92vw, 620px); padding: 48px; border: 1px solid #3f3f46; border-radius: 24px; background: rgba(24,24,27,.92); box-shadow: 0 24px 80px rgba(0,0,0,.35); text-align: center; }
        .code { color: #f87171; font-size: clamp(64px, 16vw, 120px); font-weight: 800; letter-spacing: -.08em; line-height: 1; }
        h1 { margin: 20px 0 12px; font-size: 28px; } p { margin: 0 auto; max-width: 480px; color: #a1a1aa; line-height: 1.7; }
        a { display: inline-block; margin-top: 28px; padding: 12px 20px; border-radius: 12px; background: #dc2626; color: white; font-weight: 700; text-decoration: none; }
        a:hover { background: #ef4444; }
    </style>
</head>
<body>
<main>
    <div class="code">@yield('code')</div>
    <h1>@yield('title')</h1>
    <p>@yield('message')</p>
    <a href="{{ route('home') }}">Ana sayfaya dön</a>
</main>
</body>
</html>
