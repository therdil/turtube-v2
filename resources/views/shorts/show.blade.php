@extends('layouts.turtube')

@section('title', $video->title.' · Shorts')

@section('content')
@php($feed = collect([$video])->concat($nextShorts))
<div class="mx-auto grid max-w-6xl gap-6 lg:grid-cols-[minmax(0,1fr)_300px]">
    <div data-shorts-player-feed class="h-[calc(100vh-7rem)] snap-y snap-mandatory overflow-y-auto rounded-3xl border border-zinc-800 bg-black scrollbar-hide">
        @foreach ($feed as $short)
            <article data-short-item class="relative flex min-h-full snap-start items-center justify-center bg-black">
                <video controls playsinline preload="metadata" @if ($loop->first) autoplay @endif class="h-full w-full object-contain">
                    <source src="{{ $short->video_url }}" type="video/mp4">
                </video>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent p-5 pt-24 sm:p-7">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-red-400">TurTube Shorts</p>
                    <h1 class="mt-2 line-clamp-2 text-xl font-bold text-white sm:text-2xl">{{ $short->title }}</h1>
                    <a href="{{ route('channels.show', $short->user) }}" class="pointer-events-auto mt-3 inline-flex items-center gap-2 text-sm font-semibold text-zinc-200 hover:text-white"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-red-600 text-xs">{{ strtoupper(substr($short->display_channel_name, 0, 1)) }}</span>{{ $short->display_channel_name }}</a>
                </div>
            </article>
        @endforeach
    </div>

    <aside class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5 lg:sticky lg:top-24 lg:h-fit">
        <p class="text-sm font-semibold text-red-400">SHORTS AKIŞI</p>
        <h2 class="mt-2 text-xl font-bold text-white">Kaydırarak keşfet</h2>
        <p class="mt-3 text-sm leading-6 text-zinc-400">Mouse tekerleği veya mobilde kaydırma hareketiyle sonraki videoya geç. Bir Short tamamlandığında akış otomatik ilerler.</p>
        <a href="{{ route('shorts.index') }}" class="mt-5 inline-flex rounded-xl border border-zinc-700 px-4 py-2 text-sm font-semibold text-white transition hover:border-red-500">Shorts keşfete dön</a>
        <a href="{{ route('videos.show', $video) }}" class="mt-3 inline-flex text-sm font-semibold text-zinc-400 transition hover:text-white">Tam video sayfası →</a>
    </aside>
</div>
@endsection
