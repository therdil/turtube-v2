<a
    href="{{ route('videos.show', $video) }}"
    class="video-card group block overflow-hidden rounded-xl bg-gray-900 shadow-lg transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl"
>

    <div class="relative aspect-video overflow-hidden bg-black">

        {{-- Thumbnail --}}
        <img
            src="{{ $video->thumbnail_url }}"
            alt="{{ $video->title }}"
            class="thumbnail absolute inset-0 h-full w-full object-cover transition-opacity duration-300"
        >

        {{-- Preview Video --}}
        @if($video->preview)
            <video
                class="preview-video absolute inset-0 hidden h-full w-full object-cover"
                muted
                preload="metadata"
                playsinline
            >
                <source src="{{ $video->preview_url }}" type="video/mp4">
            </video>
        @endif

        {{-- Üst Gradient --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent"></div>

        {{-- Süre --}}
        @if($video->duration)
            <div
                class="absolute bottom-2 right-2 rounded bg-black/80 px-2 py-1 text-xs font-semibold text-white"
            >
                {{ $video->duration }}
            </div>
        @endif

        {{-- Progress Bar (Şimdilik gizli, sonraki adımda aktif olacak) --}}
        <div class="absolute bottom-0 left-0 h-1 w-full bg-black/40">

            <div
                class="preview-progress h-full w-0 bg-red-600 transition-all duration-75">
            </div>

        </div>

    </div>

    <div class="space-y-2 p-4">

        <h3
            class="line-clamp-2 min-h-[56px] text-lg font-bold text-white group-hover:text-red-400 transition-colors duration-300"
        >
            {{ $video->title }}
        </h3>

        <p class="text-sm text-gray-400">
            {{ $video->channel_name }}
        </p>

        <p class="text-xs text-gray-500">
            👁 {{ number_format($video->views) }} görüntülenme
        </p>

    </div>

</a>