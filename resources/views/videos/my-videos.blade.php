<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Videolarım - TurTube</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-white">

<div class="max-w-6xl mx-auto p-8">

    <div class="flex items-center justify-between mb-8">

        <h1 class="text-3xl font-bold">
            🎥 Videolarım
        </h1>

        <a href="{{ url('/') }}"
           class="bg-gray-700 px-4 py-2 rounded hover:bg-gray-600">
            ← Ana Sayfa
        </a>

    </div>

    @if($videos->isEmpty())

        <div class="bg-gray-800 rounded-lg p-8 text-center">

            <h2 class="text-xl">
                Henüz video yüklemediniz.
            </h2>

            <a href="{{ route('videos.create') }}"
               class="inline-block mt-6 bg-red-600 px-6 py-3 rounded hover:bg-red-700">

                İlk Videonu Yükle

            </a>

        </div>

    @else

        <div class="grid grid-cols-3 gap-6">

            @foreach($videos as $video)

                <x-video-card :video="$video" />

            @endforeach

        </div>

    @endif

</div>

</body>
</html>