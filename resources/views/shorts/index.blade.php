@extends('layouts.turtube')

@section('title', 'TurTube Shorts - Kısa Videolar')
@section('meta_description', 'TurTube Shorts ile kısa, dikey ve hızlı tüketilen videoları keşfet.')
@section('og_title', 'TurTube Shorts')
@section('og_description', 'TurTube Shorts ile kısa, dikey ve hızlı tüketilen videoları keşfet.')

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Hızlı keşfet</p>
        <h1 class="mt-2 text-4xl font-bold text-white">TurTube Shorts</h1>
        <p class="mt-3 text-gray-400">Kısa, dikey ve hızlı tüketilen videolar.</p>
    </div>

    @if ($shorts->isNotEmpty())
        <div data-shorts-grid data-next-page-url="{{ $shorts->nextPageUrl() }}" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($shorts as $short)
                <a href="{{ route('shorts.show', $short) }}" class="group overflow-hidden rounded-3xl border border-gray-800 bg-gray-900 transition hover:-translate-y-1 hover:border-red-500">
                    <div class="relative aspect-[9/16] bg-black">
                        @if ($short->thumbnail)
                            <img src="{{ $short->thumbnail_url }}" alt="{{ $short->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                        @else
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-red-700 to-gray-950 text-5xl">▶</div>
                        @endif
                        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-5 pt-20">
                            <h2 class="line-clamp-2 font-bold text-white">{{ $short->title }}</h2>
                            <p class="mt-2 text-sm text-gray-300">{{ $short->display_channel_name }} · {{ number_format($short->views) }}</p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <div data-shorts-sentinel class="mt-8 text-center text-sm text-gray-500 {{ $shorts->hasMorePages() ? '' : 'hidden' }}">Yeni Shorts yükleniyor…</div>
        <noscript><div class="mt-8">{{ $shorts->links() }}</div></noscript>
    @else
        <x-ui.card class="p-12 text-center">
            <h2 class="text-2xl font-bold text-white">Henüz Shorts yok</h2>
            <p class="mt-3 text-gray-400">İlk kısa videonu yükleyerek akışı başlat.</p>
            @auth
                <a href="{{ route('videos.create') }}" class="mt-6 inline-flex rounded-xl bg-red-600 px-5 py-3 font-semibold text-white">Shorts yükle</a>
            @endauth
        </x-ui.card>
    @endif
</div>
@endsection
