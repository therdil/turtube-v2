@extends('layouts.turtube')

@section('title', $video->title)
@section('meta_description', \Illuminate\Support\Str::limit($video->description ?: $video->title, 155))
@section('og_type', 'video.other')
@if ($video->thumbnail)
    @section('og_image', $video->thumbnail_url)
@endif

@section('content')

<div class="mx-auto max-w-[1800px] px-6 py-6">

    <div class="grid grid-cols-12 gap-8">

        {{-- Sol Alan --}}
        <div class="col-span-12 xl:col-span-8">

            <x-watch.player :video="$video" :progress-seconds="$progressSeconds" :recommended-videos="$recommendedVideos" />

            <h1 class="mt-6 text-3xl font-bold text-white">
                {{ $video->title }}
            </h1>

            {{-- Kanal Kartı --}}
            <x-ui.card class="mt-6">

                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex items-center gap-4">

                        <a
                            href="{{ route('channels.show', $video->user) }}"
                            class="flex items-center gap-4">

                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-red-600 text-xl font-bold text-white">
                                {{ strtoupper(substr($video->display_channel_name, 0, 1)) }}
                            </div>

                            <div>

                                <h2 class="text-lg font-bold text-white hover:text-red-500 transition">
                                    {{ $video->display_channel_name }}
                                </h2>

                                <p class="text-sm text-gray-400">
                                    TurTube Resmî Kanal
                                </p>

                            </div>

                        </a>

                    </div>

                    <x-watch.subscribe-button
                        :channel="$video->user"
                        :isSubscribed="$isSubscribed"
                        :subscribersCount="$subscribersCount" />

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

                <x-watch.share-menu :video="$video" />

                <x-watch.favorite-button :video="$video" :is-favorited="$isFavorited" />

                <x-watch.rating-control :video="$video" :user-rating="$userRating" :rating-average="$ratingAverage" :ratings-count="$ratingsCount" />

                <x-watch.watch-later-button
                    :video="$video"
                    :isWatchLater="$isWatchLater" />

                <x-watch.playlist-button
                    :video="$video"
                    :playlists="$playlists"
                    :playlist-video-ids="$playlistVideoIds" />

                @auth
                    @if (auth()->id() !== $video->user_id)
                        <details class="relative">
                            <summary class="cursor-pointer rounded-full border border-gray-700 px-5 py-3 text-sm font-medium text-gray-200 transition hover:border-red-500 hover:text-white">Raporla</summary>
                            <form method="POST" action="{{ route('videos.reports.store', $video) }}" class="absolute right-0 z-20 mt-3 w-80 rounded-2xl border border-gray-700 bg-gray-950 p-4 shadow-2xl">
                                @csrf
                                <label class="text-sm font-medium text-white" for="report-reason">Rapor nedeni</label>
                                <select id="report-reason" name="reason" required class="mt-2 w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-white">
                                    <option value="">Seçin</option>
                                    @foreach (\App\Models\VideoReport::REASONS as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <textarea name="details" rows="3" maxlength="2000" placeholder="İsteğe bağlı açıklama" class="mt-3 w-full rounded-lg border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-white"></textarea>
                                <button class="mt-3 w-full rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Raporu gönder</button>
                            </form>
                        </details>
                    @endif
                @endauth

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

            @if ($video->chapters->isNotEmpty())
                <x-ui.card class="mt-5">
                    <h2 class="text-lg font-bold text-white">Bölümler</h2>
                    <div class="mt-4 divide-y divide-gray-800">
                        @foreach ($video->chapters as $chapter)
                            <button type="button" data-seek-to="{{ $chapter->start_seconds }}" class="flex w-full items-center gap-4 py-3 text-left transition hover:text-red-400">
                                <span class="w-12 font-mono text-sm text-red-400">{{ $chapter->formatted_start }}</span>
                                <span class="text-gray-200">{{ $chapter->title }}</span>
                            </button>
                        @endforeach
                    </div>
                </x-ui.card>
            @endif

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
                            loading="lazy"
                            decoding="async"
                            class="aspect-video w-44 rounded-lg object-cover"
                            alt="{{ $recommended->title }}">

                        <div class="min-w-0 flex-1">

                            <h3 class="line-clamp-2 font-semibold text-white">
                                {{ $recommended->title }}
                            </h3>

                            <p class="mt-2 text-sm text-gray-400">
                                {{ $recommended->display_channel_name }}
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

@push('head')
@php
    $videoSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'VideoObject',
        'name' => $video->title,
        'description' => \Illuminate\Support\Str::limit($video->description ?: $video->title, 500),
        'uploadDate' => $video->created_at->toAtomString(),
        'contentUrl' => $video->video_url,
        'embedUrl' => route('videos.show', $video),
        'url' => route('videos.show', $video),
        'publisher' => ['@type' => 'Organization', 'name' => 'TurTube'],
        'author' => ['@type' => 'Person', 'name' => $video->display_channel_name],
        'interactionStatistic' => [
            '@type' => 'InteractionCounter',
            'interactionType' => ['@type' => 'WatchAction'],
            'userInteractionCount' => (int) $video->views,
        ],
    ];

    if ($video->thumbnail) {
        $videoSchema['thumbnailUrl'] = [$video->thumbnail_url];
    }
@endphp
<script type="application/ld+json">{!! json_encode($videoSchema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@push('scripts')
<script>
document.querySelectorAll('[data-seek-to]').forEach((button) => {
    button.addEventListener('click', () => {
        const player = document.getElementById('video-player');

        if (!player) return;

        player.currentTime = Number(button.dataset.seekTo);
        player.play();
        player.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});
</script>
@endpush
