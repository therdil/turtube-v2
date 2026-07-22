@extends('layouts.turtube')

@section('title', 'TurTube')

@section('content')

<h2 class="text-4xl font-bold mb-10">
    🎬 Öne Çıkan Videolar
</h2>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-8">

    @foreach($videos as $video)

        <x-video-card :video="$video" />

    @endforeach

</div>

@endsection