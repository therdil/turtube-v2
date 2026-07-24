@extends('layouts.turtube')

@section('title', 'TurTube')

@section('content')

<div class="space-y-8">

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

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">

        @foreach($videos as $video)

            <x-video-card :video="$video" />

        @endforeach

    </div>

</div>

@endsection