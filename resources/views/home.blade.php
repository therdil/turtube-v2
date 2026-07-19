<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TurTube</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-white">

    <header class="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6">

        <h1 class="text-2xl font-bold text-red-500">
            🎬 TurTube
        </h1>

        <input
            type="text"
            placeholder="Video ara..."
            class="w-96 rounded-lg bg-gray-800 border border-gray-700 px-4 py-2">

        <div class="space-x-3">
            <a href="/login" class="px-4 py-2 bg-gray-700 rounded-lg">
                Giriş
            </a>

            <a href="/register" class="px-4 py-2 bg-red-600 rounded-lg">
                Kayıt Ol
            </a>
        </div>

    </header>

    <div class="flex">

        <aside class="w-64 bg-gray-900 min-h-screen p-6">

            <ul class="space-y-4">

                <li>🏠 Ana Sayfa</li>
                <li>🔥 Trendler</li>
                <li>📺 Kanallar</li>
                <li>❤️ Beğenilenler</li>
                <li>⬆️ Video Yükle</li>

            </ul>

        </aside>

        <main class="flex-1 p-8">

            <h2 class="text-3xl font-bold mb-8">
                Öne Çıkan Videolar
            </h2>

            <div class="grid grid-cols-4 gap-6">

    @foreach($videos as $video)

        <x-video-card :video="$video" />

    @endforeach

</div>

        </main>

    </div>

</body>
</html>