<a
    href="{{ route('videos.show', $video) }}"
    class="video-card group block overflow-hidden rounded-2xl border border-gray-800 bg-gray-900/80 shadow-md backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-gray-700 hover:shadow-2xl"
>

    <div class="relative aspect-video overflow-hidden bg-black">

        {{-- Thumbnail --}}
        <img
            src="{{ $video->thumbnail_url }}"
            alt="{{ $video->title }}"
            class="thumbnail absolute inset-0 z-10 h-full w-full object-cover opacity-100 transition-all duration-300 group-hover:scale-[1.03]"
        >

        {{-- Preview --}}
        @if($video->preview)
            <video
                class="preview-video absolute inset-0 z-10 h-full w-full object-cover opacity-0 transition-opacity duration-300"
                muted
                preload="auto"
                playsinline
            >
                <source src="{{ $video->preview_url }}" type="video/mp4">
            </video>

            <button
                type="button"
                class="mute-button absolute bottom-3 right-3 z-30 hidden h-9 w-9 items-center justify-center rounded-full bg-black/70 text-white backdrop-blur transition hover:bg-black/90"
            >
                🔇
            </button>
        @endif

        {{-- Gradient --}}
        <div class="pointer-events-none absolute inset-0 z-20 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>

        {{-- Duration --}}
        @if($video->duration)
            <div class="absolute bottom-3 left-3 z-30 rounded-md bg-black/80 px-2 py-1 text-xs font-semibold tracking-wide text-white">
                {{ $video->duration }}
            </div>
        @endif

        {{-- Progress --}}
        <div class="absolute bottom-0 left-0 z-30 h-1 w-full bg-black/20">
            <div class="preview-progress h-full w-0 bg-red-600"></div>
        </div>

    </div>

    <div class="space-y-3 p-4">

        <h3 class="line-clamp-2 min-h-[52px] text-base font-semibold leading-6 text-white transition-colors duration-300 group-hover:text-red-400">
            {{ $video->title }}
        </h3>

        <div class="space-y-1">

            <p class="text-sm font-medium text-gray-300">
                {{ $video->channel_name }}
            </p>

            <p class="text-xs text-gray-500">
                {{ number_format($video->views) }} görüntülenme
            </p>

        </div>

    </div>

</a>