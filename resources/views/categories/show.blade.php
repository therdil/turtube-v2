@extends('layouts.turtube')

@section('title', $category->name)

@section('content')

<div class="space-y-8">

    <div>

        <h1 class="text-4xl font-bold text-white">
            {{ $category->name }}
        </h1>

        <p class="mt-2 text-gray-400">
            {{ $videos->total() }} video bulundu.
        </p>

    </div>

    @if($videos->count())

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

            @foreach($videos as $video)

                <x-video-card :video="$video" />

            @endforeach

        </div>

        <div class="pt-6">
            {{ $videos->links() }}
        </div>

    @else

        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-12 text-center">

            <h2 class="text-2xl font-semibold text-white">
                Bu kategoride henüz video yok.
            </h2>

            <p class="mt-3 text-gray-400">
                İlk videoyu sen yükleyebilirsin.
            </p>

        </div>

    @endif

</div>

@endsection