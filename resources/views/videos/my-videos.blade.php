@extends('layouts.turtube')

@section('title', 'Videolarım')

@section('content')

<div class="flex items-center justify-between mb-8">

    <h1 class="text-3xl font-bold">
        🎥 Videolarım
    </h1>

    <a href="{{ url('/') }}"
       class="bg-gray-700 px-4 py-2 rounded-lg hover:bg-gray-600 transition">
        ← Ana Sayfa
    </a>

</div>

@if($videos->isEmpty())

<div class="bg-gray-800 rounded-xl p-8 text-center">

    <h2 class="text-xl">
        Henüz video yüklemediniz.
    </h2>

    <a href="{{ route('videos.create') }}"
       class="inline-block mt-6 bg-red-600 px-6 py-3 rounded-lg hover:bg-red-700 transition">

        İlk Videonu Yükle

    </a>

</div>

@else

<div class="space-y-6">

    @foreach($videos as $video)

    <div class="bg-gray-900 rounded-xl p-5 flex gap-6 items-center">

        <!-- Thumbnail -->
        <a href="{{ route('videos.show', $video) }}"
           class="shrink-0">

            <img
                src="{{ asset('storage/' . $video->thumbnail) }}"
                class="w-64 aspect-video object-cover rounded-lg">

        </a>

        <!-- Bilgiler -->
        <div class="flex-1">

            <h2 class="text-2xl font-bold">
                {{ $video->title }}
            </h2>

            <p class="text-gray-400 mt-2">
                👤 {{ $video->channel_name }}
            </p>

            <p class="text-gray-500 mt-2">
                👁 {{ number_format($video->views) }} görüntülenme
            </p>

        </div>

        <!-- İşlemler -->
        <div class="flex flex-col gap-3">

            <button
                class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg transition">

                ✏️ Düzenle

            </button>

            <button
                class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition">

                🗑️ Sil

            </button>

        </div>

    </div>

    @endforeach

</div>

@endif

@endsection