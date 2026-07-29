@extends('layouts.turtube')

@section('content')

<div class="mx-auto max-w-6xl">

    <div class="mb-8 flex items-center justify-between">

        <div>

            <h1 class="text-3xl font-bold text-white">
                Oynatma Listelerim
            </h1>

            <p class="mt-2 text-gray-400">
                Oluşturduğun tüm oynatma listeleri burada görünür.
            </p>

        </div>

        <a
            href="{{ route('playlists.create') }}"
            class="rounded-lg bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-700">

            + Yeni Playlist

        </a>

    </div>

    @if($playlists->isEmpty())

        <div class="rounded-xl border border-gray-800 bg-gray-900 p-10 text-center">

            <h2 class="text-xl font-semibold text-white">
                Henüz playlist oluşturmadın.
            </h2>

            <p class="mt-2 text-gray-400">
                İlk oynatma listeni oluştur.
            </p>

        </div>

    @else

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

            @foreach($playlists as $playlist)

                <a
                    href="{{ route('playlists.show',$playlist) }}"
                    class="rounded-xl border border-gray-800 bg-gray-900 p-6 transition hover:border-red-500">

                    <h2 class="text-xl font-semibold text-white">

                        {{ $playlist->name }}

                    </h2>

                    <p class="mt-3 text-gray-400">

                        {{ $playlist->description ?: 'Açıklama yok.' }}

                    </p>

                    <p class="mt-5 text-sm text-gray-500">

                        {{ $playlist->videos_count }} video

                    </p>

                </a>

            @endforeach

        </div>

    @endif

</div>

@endsection