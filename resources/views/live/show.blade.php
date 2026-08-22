@extends('layouts.turtube')

@php
    $liveMetaDescription = trim(strip_tags((string) $stream->description));
    $liveMetaDescription = $liveMetaDescription !== ''
        ? \Illuminate\Support\Str::limit($liveMetaDescription, 155)
        : ($stream->status === 'live' ? 'TurTube üzerinde canlı yayınlanan içerik.' : 'TurTube üzerinde planlanan canlı yayın.');
@endphp
@section('title', $stream->title.' - Canlı Yayın - TurTube')
@section('meta_description', $liveMetaDescription)
@section('og_title', $stream->title)
@section('og_description', $liveMetaDescription)
@if ($stream->thumbnail)
    @section('og_image', \App\Services\MediaUrl::for($stream->thumbnail))
@endif

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="grid gap-8 xl:grid-cols-[1fr_340px]">
        <section>
            <div class="overflow-hidden rounded-3xl border border-gray-800 bg-black">
                @if ($stream->status === 'live' && $stream->stream_url)
                    <iframe src="{{ $stream->stream_url }}" title="{{ $stream->title }}" class="aspect-video w-full" allow="autoplay; fullscreen" allowfullscreen></iframe>
                @else
                    <div class="flex aspect-video items-center justify-center bg-gradient-to-br from-red-950 via-gray-950 to-black p-8 text-center">
                        <div>
                            <div class="text-6xl">📡</div>
                            <h1 class="mt-5 text-3xl font-bold text-white">{{ $stream->status === 'live' ? 'Yayın hazırlanıyor' : 'Canlı yayın planlandı' }}</h1>
                            <p class="mt-3 text-gray-400">{{ $stream->status === 'scheduled' ? 'Yayın başladığında bu sayfadan izleyebileceksin.' : 'Oynatıcı adresi yapılandırıldığında yayın burada görünecek.' }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-6">
                <div class="flex items-center gap-3">
                    <span class="rounded-md px-2.5 py-1 text-xs font-bold {{ $stream->status === 'live' ? 'bg-red-600 text-white' : 'bg-gray-800 text-gray-300' }}">{{ strtoupper($stream->status) }}</span>
                    @if ($stream->status === 'live')
                        <span class="text-sm text-gray-400">{{ number_format($stream->viewer_count) }} izleyici</span>
                    @endif
                </div>
                <h1 class="mt-3 text-3xl font-bold text-white">{{ $stream->title }}</h1>
                <a href="{{ route('channels.show', $stream->user) }}" class="mt-3 inline-block text-gray-300 hover:text-red-400">{{ $stream->user->channel_name ?: $stream->user->name }}</a>
                <p class="mt-5 whitespace-pre-line leading-7 text-gray-400">{{ $stream->description }}</p>
            </div>
        </section>

        @if (auth()->id() === $stream->user_id)
            <aside class="h-fit rounded-2xl border border-gray-800 bg-gray-900 p-6 xl:sticky xl:top-24">
                <h2 class="text-xl font-bold text-white">Yayın kontrolü</h2>
                <p class="mt-3 text-sm text-gray-400">Yayın anahtarını yalnızca güvendiğin yayın yazılımında kullan.</p>
                <code class="mt-4 block break-all rounded-xl bg-gray-950 p-3 text-xs text-red-300">{{ $stream->stream_key }}</code>
                @if ($stream->status === 'scheduled')
                    <form method="POST" action="{{ route('live.start', $stream) }}" class="mt-5">
                        @csrf
                        <button class="w-full rounded-xl bg-red-600 px-4 py-3 font-semibold text-white hover:bg-red-700">Yayını başlat</button>
                    </form>
                @elseif ($stream->status === 'live')
                    <form method="POST" action="{{ route('live.stop', $stream) }}" class="mt-5">
                        @csrf
                        <button class="w-full rounded-xl border border-red-700 px-4 py-3 font-semibold text-red-300 hover:bg-red-600 hover:text-white">Yayını sonlandır</button>
                    </form>
                @endif
            </aside>
        @endif
    </div>
</div>
@endsection
