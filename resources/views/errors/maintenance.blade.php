<!DOCTYPE html>
<html lang="tr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Bakım çalışması · {{ $settings->site_name }}</title>@vite(['resources/css/app.css'])</head>
<body class="flex min-h-screen items-center justify-center bg-gray-950 p-6 text-gray-100"><main class="w-full max-w-xl rounded-3xl border border-gray-800 bg-gray-900 p-10 text-center shadow-2xl"><div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-600 text-3xl">🛠</div><h1 class="mt-6 text-3xl font-bold">Kısa bir bakım çalışması yapıyoruz</h1><p class="mt-4 leading-7 text-gray-400">{{ $settings->maintenance_message ?: 'TurTube’u daha iyi hale getirmek için kısa süreli bakım yapıyoruz. Lütfen biraz sonra tekrar deneyin.' }}</p><p class="mt-7 text-sm text-gray-500">Yakında yeniden yayında olacağız.</p></main></body>
</html>
