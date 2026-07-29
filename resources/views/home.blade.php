@extends('layouts.turtube')

@section('title', 'TurTube')

@section('content')

<div class="mx-auto max-w-[1800px] space-y-8">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-3xl font-bold tracking-tight">
                Öne Çıkan Videolar
            </h1>

            <p class="mt-2 text-sm text-gray-400">
                TurTube topluluğundan en yeni ve en popüler videolar
            </p>
        </div>

    </div>

    @if ($videos->isNotEmpty())
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">

            @foreach($videos as $video)

                <x-video-card :video="$video" />

            @endforeach

        </div>

        <div>{{ $videos->links() }}</div>
    @else
        <x-ui.card class="p-12 text-center">
            <h2 class="text-2xl font-bold text-white">Henüz video yok</h2>
            <p class="mt-3 text-gray-400">İlk videoyu yükleyerek TurTube topluluğunu başlat.</p>
            @auth
                <a href="{{ route('videos.create') }}" class="mt-6 inline-flex rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">Video yükle</a>
            @endauth
        </x-ui.card>
    @endif

</div>

@endsection
