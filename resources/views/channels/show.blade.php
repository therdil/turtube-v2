@extends('layouts.turtube')

@section('title', $channel->name)

@section('content')

<div class="mx-auto max-w-[1800px] px-6 py-6">

    {{-- Banner --}}
    <div class="h-56 rounded-2xl bg-gradient-to-r from-red-600 via-red-500 to-gray-900"></div>

    {{-- Kanal Bilgisi --}}
    <div class="-mt-16 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">

        <div class="flex items-end gap-6">

            <div class="flex h-32 w-32 items-center justify-center rounded-full border-4 border-gray-950 bg-red-600 text-5xl font-bold text-white">
                {{ strtoupper(substr($channel->name, 0, 1)) }}
            </div>

            <div class="pb-2">

                <h1 class="text-4xl font-bold text-white">
                    {{ $channel->name }}
                </h1>

                <p class="mt-2 text-gray-400">
                    {{ number_format($subscribersCount) }} abone
                    •
                    {{ number_format($totalViews) }} görüntülenme
                </p>

            </div>

        </div>

        <div>

            @auth

                @if(auth()->id() !== $channel->id)

                    <x-watch.subscribe-button
                        :video="$videos->first()"
                        :isSubscribed="$isSubscribed"
                        :subscribersCount="$subscribersCount" />

                @endif

            @endauth

        </div>

    </div>

    {{-- Videolar --}}
    <div class="mt-12">

        <h2 class="mb-6 text-2xl font-bold text-white">
            Videolar
        </h2>

        @if($videos->isEmpty())

            <div class="rounded-2xl bg-gray-900 p-10 text-center text-gray-400">

                Bu kanalda henüz video bulunmuyor.

            </div>

        @else

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

                @foreach($videos as $video)

                    <a
                        href="{{ route('videos.show', $video) }}"
                        class="group">

                        <img
                            src="{{ asset('storage/' . $video->thumbnail) }}"
                            class="aspect-video w-full rounded-xl object-cover transition group-hover:scale-[1.02]"
                            alt="{{ $video->title }}">

                        <h3 class="mt-3 line-clamp-2 font-semibold text-white">

                            {{ $video->title }}

                        </h3>

                        <p class="mt-1 text-sm text-gray-400">

                            👁 {{ number_format($video->views) }}

                        </p>

                    </a>

                @endforeach

            </div>

        @endif

    </div>

</div>

@endsection