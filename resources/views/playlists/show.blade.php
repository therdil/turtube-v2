@extends('layouts.turtube')

@if (! $playlist->is_public)
    @section('meta_robots', 'noindex,follow')
@endif

@section('content')

<div class="mx-auto max-w-6xl">

    <div class="mb-8 flex flex-col gap-5 border-b border-gray-800 pb-8 sm:flex-row sm:items-start sm:justify-between">

        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-bold text-white">
                    {{ $playlist->name }}
                </h1>

                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $playlist->is_public ? 'bg-green-500/15 text-green-300' : 'bg-gray-800 text-gray-300' }}">
                    {{ $playlist->is_public ? 'Herkese açık' : 'Gizli' }}
                </span>
            </div>

            <p class="mt-3 text-sm text-gray-400">
                <a href="{{ route('channels.show', $playlist->user) }}" class="font-medium text-white hover:text-red-400">{{ $playlist->user->name }}</a>
                tarafından oluşturuldu
            </p>

            @if($playlist->description)
                <p class="mt-3 text-gray-400">
                    {{ $playlist->description }}
                </p>
            @endif

            <p class="mt-4 text-sm text-gray-500">
                {{ $playlist->videos->count() }} video
            </p>
        </div>

        @if (auth()->id() === $playlist->user_id)
            <form method="POST" action="{{ route('playlists.destroy', $playlist) }}" onsubmit="return confirm('Bu oynatma listesini silmek istediğine emin misin?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex rounded-xl border border-red-700 px-4 py-2 text-sm font-semibold text-red-300 transition hover:bg-red-600 hover:text-white">
                    Listeyi sil
                </button>
            </form>
        @endif

    </div>

    @if($playlist->videos->isEmpty())

        <div class="rounded-xl border border-gray-800 bg-gray-900 p-10 text-center">

            <h2 class="text-xl font-semibold text-white">
                Bu playlist henüz boş.
            </h2>

            <p class="mt-2 text-gray-400">
                Bir videoyu bu playlist'e eklediğinde burada görünecek.
            </p>

        </div>

    @else

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">

            @foreach($playlist->videos as $video)

                <article class="overflow-hidden rounded-xl border border-gray-800 bg-gray-900 transition hover:border-red-500">

                    <a href="{{ route('videos.show', $video) }}" class="block">
                        @if ($video->thumbnail)
                            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="aspect-video w-full object-cover">
                        @else
                            <div class="flex aspect-video items-center justify-center bg-gradient-to-br from-red-700 to-gray-950 text-3xl text-white/80">▶</div>
                        @endif

                        <div class="p-5">
                            <h2 class="font-semibold text-white">
                                {{ $video->title }}
                            </h2>

                            <p class="mt-2 text-sm text-gray-400">
                                {{ $video->user->name }}
                            </p>
                        </div>
                    </a>

                    @if (auth()->id() === $playlist->user_id)
                        <form method="POST" action="{{ route('playlists.toggle', $playlist) }}" class="border-t border-gray-800 px-5 py-3">
                            @csrf
                            <input type="hidden" name="video_id" value="{{ $video->id }}">
                            <button type="submit" class="text-sm font-medium text-red-400 transition hover:text-red-300">Listeden kaldır</button>
                        </form>
                    @endif

                </article>

            @endforeach

        </div>

    @endif

</div>

@endsection
