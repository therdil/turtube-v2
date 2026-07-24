@extends('layouts.turtube')

@section('title', $video->title)

@section('content')

<div class="mx-auto max-w-[1800px] px-6 py-6">

    <div class="grid grid-cols-12 gap-8">

        {{-- Sol Alan --}}
        <div class="col-span-12 xl:col-span-8">

            <x-watch.player :video="$video" />

            <h1 class="mt-6 text-3xl font-bold text-white">
                {{ $video->title }}
            </h1>

            {{-- Kanal Kartı --}}
            <x-ui.card class="mt-6">

                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-600 text-xl font-bold text-white">
                            {{ strtoupper(substr($video->channel_name, 0, 1)) }}
                        </div>

                        <div>

                            <h2 class="text-lg font-bold text-white">
                                {{ $video->channel_name }}
                            </h2>

                            <p class="text-sm text-gray-400">
                                TurTube Resmî Kanal
                            </p>

                        </div>

                    </div>

                    <x-ui.button class="rounded-full px-6 py-3">
                        Abone Ol
                    </x-ui.button>

                </div>

            </x-ui.card>

            {{-- Aksiyon Butonları --}}
            <div class="mt-5 flex flex-wrap gap-3">

                <x-watch.like-button
                    :video="$video"
                    :isLiked="$isLiked" />

                <x-ui.button variant="secondary" class="rounded-full px-5 py-3">
                    👎 Beğenme
                </x-ui.button>

                <x-ui.button variant="secondary" class="rounded-full px-5 py-3">
                    🔗 Paylaş
                </x-ui.button>

                <x-ui.button variant="secondary" class="rounded-full px-5 py-3">
                    💾 Kaydet
                </x-ui.button>

            </div>

            {{-- Açıklama --}}
            <x-ui.card class="mt-5">

                <div class="mb-4 flex flex-wrap gap-6 text-sm text-gray-400">

                    <span>
                        👁 {{ number_format($video->views) }} görüntülenme
                    </span>

                    <span>
                        📅 {{ $video->created_at->diffForHumans() }}
                    </span>

                </div>

                <p class="whitespace-pre-line leading-8 text-gray-300">
                    {{ $video->description ?: 'Bu video için henüz açıklama eklenmemiş.' }}
                </p>

            </x-ui.card>

            {{-- Yorumlar --}}
            <x-watch.comments :video="$video" />

        </div>

        {{-- Sağ Alan --}}
        <div class="col-span-12 xl:col-span-4">

            <h2 class="mb-5 text-xl font-bold text-white">
                Önerilen Videolar
            </h2>

            <div class="space-y-4">

                @forelse($recommendedVideos as $recommended)

                    <a
                        href="{{ route('videos.show', $recommended) }}"
                        class="flex gap-3 rounded-xl p-2 transition hover:bg-gray-900">

                        <img
                            src="{{ asset('storage/' . $recommended->thumbnail) }}"
                            class="aspect-video w-44 rounded-lg object-cover"
                            alt="{{ $recommended->title }}">

                        <div class="min-w-0 flex-1">

                            <h3 class="line-clamp-2 font-semibold text-white">
                                {{ $recommended->title }}
                            </h3>

                            <p class="mt-2 text-sm text-gray-400">
                                {{ $recommended->channel_name }}
                            </p>

                            <p class="text-xs text-gray-500">
                                👁 {{ number_format($recommended->views) }}
                            </p>

                        </div>

                    </a>

                @empty

                    <div class="rounded-xl bg-gray-900 p-5 text-gray-400">
                        Henüz önerilecek video yok.
                    </div>

                @endforelse

            </div>

        </div>

    </div>

</div>

@endsection