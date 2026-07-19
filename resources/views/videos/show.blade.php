<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $video->title }} - TurTube</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-white">

    <div class="max-w-7xl mx-auto p-8">

        <a href="/" class="text-red-500 hover:underline">
            ← Ana Sayfaya Dön
        </a>

        <div class="mt-6 bg-black rounded-xl overflow-hidden">

            <video
    controls
    class="w-full h-[600px] bg-black rounded-lg">

    <source
        src="{{ asset('storage/' . $video->video_path) }}"
        type="video/mp4">

    Tarayıcınız video oynatmayı desteklemiyor.
</video>

        </div>

        <div class="mt-6">

            <h1 class="text-3xl font-bold">
                {{ $video->title }}
            </h1>

            <p class="text-gray-400 mt-2">
                {{ $video->channel_name }}
            </p>

            <p class="text-gray-500 mt-2">
                👁 {{ number_format($video->views) }} görüntülenme
            </p>

            <hr class="my-6 border-gray-800">

            <p class="text-gray-300 leading-7">
                {{ $video->description }}
            </p>

        </div>

    </div>

</body>
</html>