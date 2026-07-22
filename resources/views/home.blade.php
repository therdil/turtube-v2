@extends('layouts.turtube')

@section('title', 'TurTube')

@section('content')

    <h2 class="text-3xl font-bold mb-8">
        🎬 Öne Çıkan Videolar
    </h2>

    <div class="grid grid-cols-4 gap-6">

        @foreach($videos as $video)

            <x-video-card :video="$video" />

        @endforeach

    </div>

@endsection