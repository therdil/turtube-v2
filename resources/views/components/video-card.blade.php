<a
    href="{{ route('videos.show', $video) }}"
    class="video-card turtube-card group block overflow-hidden bg-gray-900/80 backdrop-blur-sm"
>

    <div class="relative aspect-video overflow-hidden bg-black">

        {{-- Thumbnail --}}
        <div data-thumbnail-fallback class="absolute inset-0 z-10 flex {{ $video->thumbnail ? 'hidden' : '' }} items-center justify-center bg-gradient-to-br from-red-700 via-red-900 to-gray-950 text-4xl text-white/80">
            <x-heroicon-o-play class="h-12 w-12" />
        </div>

        @if ($video->thumbnail)
            <img
                src="{{ $video->thumbnail_url }}"
                alt="{{ $video->title }}"
                loading="lazy"
                decoding="async"
                onerror="this.classList.add('hidden'); this.parentElement.querySelector('[data-thumbnail-fallback]')?.classList.remove('hidden');"
                class="thumbnail absolute inset-0 z-10 h-full w-full object-cover opacity-100 transition-all duration-300 group-hover:scale-[1.03]"
            >
        @endif

        {{-- Preview --}}
        @if($video->preview)
            <video
                class="preview-video absolute inset-0 z-10 h-full w-full object-cover opacity-0 transition-opacity duration-300"
                muted
                preload="metadata"
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
        @if($video->formatted_duration)
            <div class="absolute bottom-3 left-3 z-30 rounded-md bg-black/80 px-2 py-1 text-xs font-semibold tracking-wide text-white">
                {{ $video->formatted_duration }}
            </div>
        @endif

        {{-- Kategori --}}
        @if($video->category)
            <div
                class="absolute top-3 left-3 z-30 rounded-full bg-red-600/90 px-3 py-1 text-xs font-semibold text-white shadow-lg backdrop-blur">
                {{ $video->category->name }}                
            </div>
        @endif

        {{-- Watch Progress --}}
        @if(auth()->check() && $video->relationLoaded('progress') && $video->progress->first())

        <div class="watch-progress absolute bottom-1 left-0 z-30 h-1 w-full bg-black/40 transition-opacity duration-200">

            <div
            class="h-full bg-red-600"
            style="width: {{ $video->progress->first()->percentage }}%">
            </div>

        </div>

        @endif
        
        {{-- Progress --}}
        <div class="preview-progress-wrapper absolute bottom-1 left-0 z-30 h-1 w-full bg-black/20 opacity-0 transition-opacity duration-200">
            <div class="preview-progress h-full w-0 bg-red-600"></div>
        </div>

    </div>

    <div class="space-y-3 p-4 sm:p-4.5">

        <h3 class="line-clamp-2 min-h-[52px] text-base font-semibold leading-6 text-white transition-colors duration-300 group-hover:text-red-400">
            {{ $video->title }}
        </h3>

        <div class="flex items-start gap-3">

    @if($video->user?->avatar)

        <img
            src="{{ asset('storage/'.$video->user->avatar) }}"
            class="h-10 w-10 rounded-full object-cover">

    @else

        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-600 font-semibold text-white">

            {{ strtoupper(substr($video->display_channel_name,0,1)) }}

        </div>

    @endif

    <div class="min-w-0 flex-1">

        <p class="truncate text-sm font-medium text-gray-300">

            {{ $video->display_channel_name }}

        </p>

            @if($video->category)
                <p class="text-xs font-medium text-red-400">
                    {{ $video->category->name }}
                </p>
            @endif

            <p class="text-xs text-gray-500">
                {{ number_format($video->views) }} görüntülenme
            </p>

        </div>

    </div>

</div>

</a>
