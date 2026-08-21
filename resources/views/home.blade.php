@extends('layouts.turtube')

@section('title', 'TurTube')

@section('content')

<div class="mx-auto max-w-[1800px] space-y-8">

    <section data-theme-hero class="relative overflow-hidden rounded-3xl border border-zinc-800 bg-gradient-to-br from-zinc-900 via-zinc-950 to-red-950/30 p-6 shadow-2xl sm:p-9">
        <div class="relative max-w-2xl"><p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-400">TurTube keşfet</p><h1 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">Her izleyişte sana daha yakın videolar.</h1><p class="mt-3 text-zinc-300">İzleme geçmişin, ilgi alanların ve popüler içerikler tek akışta buluşur.</p><div class="mt-6 flex flex-wrap gap-3"><a href="{{ route('trending') }}" class="rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-500">Trendleri keşfet</a><a href="{{ route('shorts.index') }}" class="rounded-xl border border-zinc-700 bg-zinc-900/80 px-5 py-3 text-sm font-semibold text-zinc-100 transition hover:border-red-500">Shorts izle</a><a href="{{ route('privacy') }}" class="rounded-xl border border-zinc-700 bg-zinc-900/80 px-5 py-3 text-sm font-semibold text-zinc-100 transition hover:border-red-500">Gizlilik Politikası</a></div></div>
        <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-red-600/20 blur-3xl"></div>
    </section>

    @if ($watchHistory->isNotEmpty())
        <x-home.section title="İzlemeye Devam Et" description="Kaldığın yerden devam et.">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($watchHistory as $video)<x-video-card :video="$video" />@endforeach
            </div>
        </x-home.section>
    @endif

    <x-home.section title="Sana Özel" description="İzleme alışkanlıklarına göre seçildi.">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @forelse ($forYou as $video)<x-video-card :video="$video" />@empty<p class="col-span-full rounded-2xl border border-dashed border-zinc-800 p-8 text-sm text-zinc-500">Önerilerini kişiselleştirmek için birkaç video izle.</p>@endforelse
        </div>
    </x-home.section>

    <x-home.section title="Trend Videolar" description="TurTube'da şu an popüler olanlar." :href="route('trending')">
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach ($trendingVideos as $video)<x-video-card :video="$video" />@endforeach
        </div>
    </x-home.section>

    @if ($shorts->isNotEmpty())
        <x-home.section title="Shorts" description="Kısa ve hızlı keşfet." :href="route('shorts.index')">
            <div class="flex snap-x gap-4 overflow-x-auto pb-2 scrollbar-hide">
                @foreach ($shorts as $short)
                    <a href="{{ route('shorts.show', $short) }}" class="group relative aspect-[9/16] w-40 shrink-0 snap-start overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 transition hover:-translate-y-1 hover:border-red-500 sm:w-48">
                        @if ($short->thumbnail)<img src="{{ $short->thumbnail_url }}" alt="{{ $short->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">@else<div class="flex h-full items-center justify-center bg-gradient-to-br from-red-700 to-zinc-950"><x-heroicon-o-play class="h-10 w-10 text-white" /></div>@endif
                        <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 to-transparent p-3 pt-12 text-sm font-semibold text-white line-clamp-2">{{ $short->title }}</span>
                    </a>
                @endforeach
            </div>
        </x-home.section>
    @endif

    @if ($premiumVideos->isNotEmpty())
        <x-home.section title="Premium İçerikler" description="Premium üyeler için seçili içerikler." :href="route('premium.index')">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                @foreach ($premiumVideos as $video)<div class="relative"><span class="absolute right-3 top-3 z-40 rounded-full border border-amber-300/30 bg-amber-300/15 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-amber-200">Premium</span><x-video-card :video="$video" /></div>@endforeach
            </div>
        </x-home.section>
    @endif

    @if ($popularChannels->isNotEmpty())
        <x-home.section title="Popüler Kanallar" description="Topluluğun takip ettiği üreticiler." :href="route('channels.index')">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($popularChannels as $channel)
                    <a href="{{ route('channels.show', $channel) }}" class="group flex items-center gap-4 rounded-2xl border border-zinc-800 bg-zinc-900/80 p-4 transition hover:border-red-500 hover:bg-zinc-900">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-red-600 font-bold text-white">@if($channel->avatar)<img src="{{ asset('storage/'.$channel->avatar) }}" alt="{{ $channel->name }}" class="h-full w-full object-cover">@else{{ strtoupper(substr($channel->channel_name ?: $channel->name, 0, 1)) }}@endif</div>
                        <div class="min-w-0 flex-1"><p class="truncate font-semibold text-white group-hover:text-red-300">{{ $channel->channel_name ?: $channel->name }}</p><p class="mt-1 text-xs text-zinc-500">{{ number_format($channel->subscribers_count) }} abone · {{ number_format($channel->public_videos_count) }} video</p></div>
                        @if($channel->is_verified)<x-heroicon-s-check-badge class="h-5 w-5 shrink-0 text-blue-400" />@endif
                    </a>
                @endforeach
            </div>
        </x-home.section>
    @endif

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

    @if ($videos->isNotEmpty())
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">

            @foreach($videos as $video)

                <x-video-card :video="$video" />

            @endforeach

        </div>

        <div>{{ $videos->links() }}</div>
    @else
        <x-ui.card class="p-12 text-center">
            <h2 class="text-2xl font-bold text-white">Henüz video yok</h2>
            <p class="mt-3 text-gray-400">İlk videoyu yükleyerek TurTube topluluğunu başlat.</p>
            @auth
                <a href="{{ route('videos.create') }}" class="mt-6 inline-flex rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">Video yükle</a>
            @endauth
        </x-ui.card>
    @endif

</div>

@endsection
