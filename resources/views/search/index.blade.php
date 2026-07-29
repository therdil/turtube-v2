@extends('layouts.turtube')

@section('title', 'Arama')

@section('content')

<div class="mx-auto max-w-[1800px] px-6 py-6">

    <div class="mb-8">

        <h1 class="text-3xl font-bold text-white">
            Arama Sonuçları
        </h1>

        @if($query !== '')

            <p class="mt-2 text-gray-400">
                "<span class="font-semibold text-white">{{ $query }}</span>"
                için
                <span class="text-red-500">{{ $videos->total() }}</span>
                sonuç bulundu.
            </p>

        @endif

    </div>

    @if($videos->count())

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

            @foreach($videos as $video)
                <x-video-card :video="$video" />
            @endforeach

        </div>

        <div class="mt-10">

            {{ $videos->links() }}

        </div>

    @else

        <div class="rounded-2xl bg-gray-900 p-12 text-center">

            <div class="text-6xl mb-4">
                🔍
            </div>

            <h2 class="text-2xl font-bold text-white">

                Sonuç bulunamadı

            </h2>

            <p class="mt-3 text-gray-400">

                Farklı anahtar kelimeler deneyebilirsiniz.

            </p>

        </div>

    @endif

</div>

@endsection
