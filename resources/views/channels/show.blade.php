@extends('layouts.turtube')

@php
    $channelTitle = $channel->channel_name ?: $channel->name;
    $channelMetaDescription = trim(strip_tags((string) $channel->channel_description));
    $channelMetaDescription = $channelMetaDescription !== ''
        ? \Illuminate\Support\Str::limit($channelMetaDescription, 155)
        : $channelTitle.' kanalını TurTube üzerinde keşfet.';
    $channelSocialImage = $channel->banner ?: $channel->avatar;
@endphp
@section('title', $channelTitle.' - TurTube')
@section('meta_description', $channelMetaDescription)
@section('og_title', $channelTitle)
@section('og_description', $channelMetaDescription)

@if ($channel->channel_visibility === 'private')
    @section('meta_robots', 'noindex,follow')
@endif

@if (!empty($channel->seo_keywords))
@section('meta_keywords', implode(', ', $channel->seo_keywords))
@endif

@if ($channelSocialImage)
    @section('og_image', \App\Services\MediaUrl::for($channelSocialImage))
@endif

@section('content')
@php
    $socialLabels = ['website' => 'Web sitesi', 'instagram' => 'Instagram', 'x' => 'X / Twitter', 'facebook' => 'Facebook', 'youtube' => 'YouTube'];
@endphp
<div class="mx-auto max-w-[1800px] px-6 py-6">
    <div class="h-56 overflow-hidden rounded-2xl bg-gradient-to-r from-red-600 via-red-500 to-gray-900">
        @if($channel->banner)<img src="{{ asset('storage/'.$channel->banner) }}" alt="{{ $channelTitle }} kanal kapak görseli" class="h-full w-full object-cover">@endif
    </div>

    <div class="-mt-16 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-end gap-6">
            @if($channel->avatar)
                <img src="{{ asset('storage/'.$channel->avatar) }}" alt="{{ $channelTitle }} profil görseli" class="h-32 w-32 rounded-full border-4 border-gray-950 object-cover">
            @else
                <div class="flex h-32 w-32 items-center justify-center rounded-full border-4 border-gray-950 bg-red-600 text-5xl font-bold text-white">{{ strtoupper(substr($channelTitle, 0, 1)) }}</div>
            @endif
            <div class="pb-2">
                <div class="flex flex-wrap items-center gap-2"><h1 class="text-4xl font-bold text-white">{{ $channelTitle }}</h1>@if($channel->is_verified)<span title="Doğrulanmış kanal" class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-sky-500 text-sm font-black text-white" aria-label="Doğrulanmış kanal">✓</span>@endif</div>
                <p class="mt-2 text-gray-400">{{ number_format($subscribersCount) }} abone <span class="mx-1">·</span> {{ number_format($totalViews) }} görüntülenme</p>
            </div>
        </div>
        @auth
            @if(auth()->id() !== $channel->id)<x-watch.subscribe-button :channel="$channel" :is-subscribed="$isSubscribed" :subscribers-count="$subscribersCount" />@endif
        @endauth
    </div>

    <div class="mt-8 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-4"><p class="text-sm text-gray-400">Toplam video</p><p class="mt-1 text-2xl font-bold text-white">{{ number_format($totalVideos) }}</p></div>
        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-4"><p class="text-sm text-gray-400">Shorts</p><p class="mt-1 text-2xl font-bold text-white">{{ number_format($shortsCount) }}</p></div>
        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-4"><p class="text-sm text-gray-400">Toplam izlenme</p><p class="mt-1 text-2xl font-bold text-white">{{ number_format($totalViews) }}</p></div>
        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-4"><p class="text-sm text-gray-400">Katılım</p><p class="mt-1 text-2xl font-bold text-white">{{ $channel->created_at->translatedFormat('F Y') }}</p></div>
    </div>

    <nav class="mt-8 flex overflow-x-auto border-b border-gray-800" aria-label="Kanal bölümleri">
        @foreach (['videos' => 'Videolar', 'shorts' => 'Shorts', 'playlists' => 'Oynatma Listeleri', 'about' => 'Hakkında'] as $value => $label)
            <a href="{{ route('channels.show', ['user' => $channel, 'tab' => $value]) }}" class="whitespace-nowrap border-b-2 px-5 py-4 font-semibold transition {{ $tab === $value ? 'border-red-500 text-white' : 'border-transparent text-gray-400 hover:text-white' }}">{{ $label }}@if($value === 'videos') <span class="ml-1 text-xs text-gray-500">{{ number_format($totalVideos) }}</span>@elseif($value === 'shorts') <span class="ml-1 text-xs text-gray-500">{{ number_format($shortsCount) }}</span>@endif</a>
        @endforeach
    </nav>

    @if (in_array($tab, ['videos', 'shorts'], true))
        <div class="mt-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div><h2 class="text-2xl font-bold text-white">{{ $tab === 'shorts' ? 'Shorts' : 'Videolar' }}</h2><p class="mt-2 text-gray-400">{{ $tab === 'shorts' ? 'Kısa dikey videolar' : 'Kanalın yayınladığı videolar' }}</p></div>
            <form method="GET" class="w-full sm:max-w-sm"><input type="hidden" name="tab" value="{{ $tab }}"><label class="sr-only" for="channel-search">Kanal içinde ara</label><input id="channel-search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Kanal içinde ara..." class="w-full rounded-xl border border-gray-700 bg-gray-900 px-4 py-3 text-white placeholder:text-gray-500 focus:border-red-500 focus:outline-none"></form>
        </div>
        @if($videos->isEmpty())
            <div class="mt-6 rounded-2xl border border-dashed border-gray-700 bg-gray-900 p-12 text-center text-gray-400">{{ request('q') ? 'Aramanızla eşleşen içerik bulunamadı.' : 'Bu kanalda henüz içerik bulunmuyor.' }}</div>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">@foreach($videos as $video)<x-video-card :video="$video" />@endforeach</div>
            @if ($videos->hasPages())<div class="mt-8">{{ $videos->links() }}</div>@endif
        @endif
    @elseif ($tab === 'playlists')
        <div class="mt-8"><h2 class="text-2xl font-bold text-white">Oynatma Listeleri</h2>
            @if($playlists->isNotEmpty())<div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">@foreach ($playlists as $playlist)<a href="{{ route('playlists.show', $playlist) }}" class="rounded-2xl border border-gray-800 bg-gray-900 p-6 transition hover:border-red-500"><p class="text-xs font-semibold uppercase tracking-wider text-red-400">Oynatma listesi</p><h3 class="mt-2 text-xl font-bold text-white">{{ $playlist->name }}</h3><p class="mt-3 line-clamp-2 text-sm text-gray-400">{{ $playlist->description ?: 'Açıklama yok.' }}</p><p class="mt-5 text-sm text-gray-500">{{ $playlist->videos_count }} video</p></a>@endforeach</div>@else<div class="mt-6 rounded-2xl bg-gray-900 p-10 text-center text-gray-400">Bu kanalda herkese açık oynatma listesi bulunmuyor.</div>@endif
        </div>
    @else
        <div class="mt-8 grid gap-6 lg:grid-cols-[1.4fr_0.8fr]">
            <section class="rounded-2xl border border-gray-800 bg-gray-900 p-6"><h2 class="text-2xl font-bold text-white">{{ $channelTitle }} hakkında</h2><p class="mt-5 whitespace-pre-line leading-8 text-gray-300">{{ $channel->channel_description ?: 'Bu kanal henüz açıklama eklemedi.' }}</p></section>
            <aside class="rounded-2xl border border-gray-800 bg-gray-900 p-6"><h2 class="text-lg font-bold text-white">Kanal ayrıntıları</h2><dl class="mt-5 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-gray-400">Katılım tarihi</dt><dd class="text-white">{{ $channel->created_at->translatedFormat('d F Y') }}</dd></div><div class="flex justify-between gap-4"><dt class="text-gray-400">Toplam görüntülenme</dt><dd class="text-white">{{ number_format($totalViews) }}</dd></div><div class="flex justify-between gap-4"><dt class="text-gray-400">Abone</dt><dd class="text-white">{{ number_format($subscribersCount) }}</dd></div></dl>
                @if (!empty($channel->social_links))<div class="mt-6 border-t border-gray-800 pt-5"><h3 class="font-semibold text-white">Bağlantılar</h3><div class="mt-3 flex flex-col gap-2">@foreach ($channel->social_links as $key => $url) @if(isset($socialLabels[$key]))<a href="{{ $url }}" target="_blank" rel="noopener noreferrer nofollow" class="text-sm font-medium text-red-400 hover:text-red-300">{{ $socialLabels[$key] }} ↗</a>@endif @endforeach</div></div>@endif
            </aside>
        </div>
    @endif
</div>
@endsection
