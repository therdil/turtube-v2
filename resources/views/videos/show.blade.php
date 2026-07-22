@extends('layouts.turtube')

@section('title', $video->title)

@section('content')

<div class="grid grid-cols-12 gap-8">

    <!-- Sol Taraf -->
    <div class="col-span-9">

        <!-- Video -->
        <div class="bg-black rounded-xl overflow-hidden">

            <video
                controls
                class="w-full max-h-[650px] bg-black">

                <source
                    src="{{ asset('storage/' . $video->video_path) }}"
                    type="video/mp4">

                Tarayıcınız video oynatmayı desteklemiyor.

            </video>

        </div>

        <!-- Bilgiler -->
        <div class="mt-6">

            <h1 class="text-3xl font-bold">
                {{ $video->title }}
            </h1>

            <div class="flex justify-between items-center mt-4">

                <div>

                    <p class="text-lg font-semibold">
                        👤 {{ $video->channel_name }}
                    </p>

                    <p class="text-gray-400 text-sm mt-1">
                        👁 {{ number_format($video->views) }} görüntülenme
                    </p>

                </div>

            </div>

            <div class="mt-6 bg-gray-900 rounded-lg p-6">

                <h2 class="font-semibold mb-3">
                    Açıklama
                </h2>

                <p class="text-gray-300 leading-7 whitespace-pre-line">
                    {{ $video->description }}
                </p>

            </div>

        </div>

    </div>

    <!-- Sağ Taraf -->
    <div class="col-span-3">

        <h2 class="text-xl font-bold mb-5">
            Önerilen Videolar
        </h2>

        <div class="space-y-5">

            @forelse($recommendedVideos as $recommended)

                <a
                    href="{{ route('videos.show', $recommended) }}"
                    class="block hover:bg-gray-900 rounded-lg p-2 transition">

                    <img
                        src="{{ asset('storage/' . $recommended->thumbnail) }}"
                        class="rounded-lg w-full aspect-video object-cover">

                    <h3 class="mt-2 font-semibold line-clamp-2">
                        {{ $recommended->title }}
                    </h3>

                    <p class="text-sm text-gray-400 mt-1">
                        {{ $recommended->channel_name }}
                    </p>

                    <p class="text-xs text-gray-500">
                        👁 {{ number_format($recommended->views) }}
                    </p>

                </a>

            @empty

                <div class="text-gray-500">

                    Henüz önerilecek başka video yok.

                </div>

            @endforelse

        </div>

    </div>

</div>

@endsection