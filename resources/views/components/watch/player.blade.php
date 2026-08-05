@props(['video', 'progressSeconds' => 0, 'recommendedVideos' => collect()])

@php
    $nextVideo = $recommendedVideos->first();
    $sources = $video->playback_sources;
    $chapters = $video->chapters->map(fn ($chapter) => [
        'title' => $chapter->title,
        'start' => $chapter->start_seconds,
        'formatted' => $chapter->formatted_start,
    ])->values();
@endphp

<section id="watch-player-shell" class="watch-player-shell" data-next-url="{{ $nextVideo ? route('videos.show', $nextVideo) : '' }}">
    <div class="watch-player-frame relative overflow-hidden rounded-2xl bg-black shadow-2xl" data-player-frame aria-busy="false">
        <video
            id="video-player"
            controls
            autoplay
            playsinline
            preload="metadata"
            class="aspect-video w-full bg-black"
            data-sources='@json($sources)'
            data-chapters='@json($chapters)'
            @auth
                data-progress-url="{{ route('videos.progress', $video) }}"
                data-csrf="{{ csrf_token() }}"
                data-start-seconds="{{ $progressSeconds }}"
            @endauth
        >
            <source src="{{ $sources[0]['url'] ?? $video->video_url }}" type="video/mp4">
            @foreach ($video->captions as $caption)
                <track kind="captions" src="{{ $caption->url }}" srclang="{{ $caption->language }}" label="{{ $caption->label }}" @if($caption->is_default) default @endif>
            @endforeach
            Tarayıcınız video oynatmayı desteklemiyor.
        </video>

        <div data-player-buffer-track class="pointer-events-none absolute bottom-0 left-0 right-0 z-10 h-1 bg-white/10">
            <div data-player-buffer class="h-full w-0 bg-white/35 transition-[width] duration-300"></div>
        </div>

        <div data-player-loading class="pointer-events-none absolute inset-0 z-20 hidden items-center justify-center bg-black/25 backdrop-blur-[1px]" aria-hidden="true">
            <div class="flex items-center gap-3 rounded-xl border border-white/10 bg-black/70 px-4 py-3 text-sm font-semibold text-white shadow-2xl">
                <span class="h-5 w-5 animate-spin rounded-full border-2 border-white/30 border-t-red-500"></span>
                Video hazırlanıyor
            </div>
        </div>

        <div data-player-volume-indicator class="pointer-events-none absolute bottom-16 left-4 z-20 hidden items-center gap-2 rounded-xl border border-white/10 bg-black/75 px-3 py-2 text-xs font-semibold text-white shadow-xl" aria-hidden="true">
            <x-heroicon-o-speaker-wave class="h-4 w-4 text-red-400" />
            <span data-player-volume-label>Ses %100</span>
            <span class="h-1.5 w-16 overflow-hidden rounded-full bg-white/20"><span data-player-volume-level class="block h-full rounded-full bg-red-500 transition-[width] duration-150" style="width: 100%"></span></span>
        </div>

        <button type="button" data-player-mini-close class="absolute right-3 top-3 z-30 hidden h-9 w-9 items-center justify-center rounded-xl border border-white/15 bg-black/70 text-white backdrop-blur transition hover:border-red-500 hover:bg-red-600" aria-label="Mini oynatıcıdan çık">
            <x-heroicon-o-arrows-pointing-out class="h-4 w-4" />
        </button>

        <div id="video-end-screen" class="absolute inset-0 hidden items-center justify-center bg-black/85 p-6 text-center">
            <div class="max-w-lg rounded-2xl border border-white/10 bg-zinc-950/80 p-5 shadow-2xl backdrop-blur-xl sm:p-6">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-400">Sıradaki video</p>
                @if ($nextVideo)
                    <div class="mt-4 overflow-hidden rounded-xl border border-white/10 bg-black">
                        @if ($nextVideo->thumbnail_url)
                            <img src="{{ $nextVideo->thumbnail_url }}" alt="{{ $nextVideo->title }}" class="aspect-video w-full object-cover" loading="lazy">
                        @else
                            <div class="flex aspect-video items-center justify-center bg-zinc-900 text-sm text-zinc-500">Sonraki video hazırlanıyor</div>
                        @endif
                    </div>
                    <h2 class="mt-4 line-clamp-2 text-xl font-bold text-white sm:text-2xl">{{ $nextVideo->title }}</h2>
                    <p id="autoplay-countdown" class="mt-3 text-sm text-zinc-300"></p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <button type="button" data-player-replay class="rounded-xl border border-zinc-600 px-4 py-2 font-semibold text-white transition hover:border-white">Tekrar oynat</button>
                        <a href="{{ route('videos.show', $nextVideo) }}" class="rounded-xl bg-red-600 px-4 py-2 font-semibold text-white transition hover:bg-red-500">Şimdi izle</a>
                    </div>
                @else
                    <h2 class="mt-3 text-2xl font-bold text-white">Video tamamlandı</h2>
                    <button type="button" data-player-replay class="mt-6 rounded-xl bg-red-600 px-4 py-2 font-semibold text-white transition hover:bg-red-500">Tekrar oynat</button>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 rounded-2xl border border-gray-800 bg-gray-950 p-3 shadow-[0_16px_40px_-28px_rgba(0,0,0,0.9)] backdrop-blur-xl sm:p-4">
        <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <div class="flex h-11 items-center gap-2 px-1 text-sm font-semibold text-zinc-200">
                    <x-heroicon-o-adjustments-horizontal class="h-5 w-5 text-red-400" />
                    <span>Oynatma Ayarları</span>
                </div>

                <x-watch.player-setting label="Kalite" icon="heroicon-o-adjustments-horizontal">
                    <button type="button" data-player-dropdown-trigger data-player-quality-trigger aria-haspopup="listbox" aria-expanded="false" class="inline-flex h-9 max-w-28 items-center gap-1.5 bg-transparent py-2 text-sm font-semibold text-zinc-100 outline-none">
                        <span data-player-quality-label class="truncate">{{ $sources[0]['label'] ?? 'Orijinal' }}</span>
                        <x-heroicon-m-chevron-down class="h-3.5 w-3.5 shrink-0 text-zinc-500" />
                    </button>
                    <div data-player-dropdown-menu role="listbox" aria-label="Video kalitesi" class="absolute bottom-[calc(100%+0.6rem)] right-0 z-30 hidden min-w-32 overflow-hidden rounded-xl border border-gray-800 bg-gray-950 p-1 shadow-2xl shadow-black/60">
                        @foreach ($sources as $source)
                            <button type="button" role="option" data-player-quality-option data-value="{{ $source['url'] }}" data-label="{{ $source['label'] }}" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-200 transition hover:bg-gray-900 hover:text-white">{{ $source['label'] }}</button>
                        @endforeach
                    </div>
                </x-watch.player-setting>

                <x-watch.player-setting label="Hız" icon="heroicon-o-bolt">
                    <button type="button" data-player-dropdown-trigger data-player-speed-trigger aria-haspopup="listbox" aria-expanded="false" class="inline-flex h-9 w-16 items-center gap-1.5 bg-transparent py-2 text-sm font-semibold text-zinc-100 outline-none">
                        <span data-player-speed-label>1x</span>
                        <x-heroicon-m-chevron-down class="h-3.5 w-3.5 shrink-0 text-zinc-500" />
                    </button>
                    <div data-player-dropdown-menu role="listbox" aria-label="Oynatma hızı" class="absolute bottom-[calc(100%+0.6rem)] right-0 z-30 hidden min-w-24 overflow-hidden rounded-xl border border-gray-800 bg-gray-950 p-1 shadow-2xl shadow-black/60">
                        @foreach ([0.25, 0.5, 0.75, 1, 1.25, 1.5, 2] as $speed)
                            <button type="button" role="option" data-player-speed-option data-value="{{ $speed }}" class="flex w-full items-center rounded-lg px-3 py-2 text-left text-sm font-medium text-zinc-200 transition hover:bg-gray-900 hover:text-white">{{ $speed }}x</button>
                        @endforeach
                    </div>
                </x-watch.player-setting>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center 2xl:justify-end">
                <x-watch.player-control data-player-mini aria-pressed="false">
                    <x-heroicon-o-arrows-pointing-in class="h-5 w-5" />
                    <span>Mini Oynatıcı</span>
                </x-watch.player-control>

                <x-watch.player-control data-player-pip aria-label="Resim içinde resim">
                    <x-heroicon-o-rectangle-stack class="h-5 w-5" />
                    <span>PiP</span>
                </x-watch.player-control>

                <x-watch.player-control data-player-cinema aria-pressed="false">
                    <x-heroicon-o-arrows-pointing-out class="h-5 w-5" />
                    <span data-player-cinema-label>Sinema Modu</span>
                </x-watch.player-control>

                <label class="group flex h-10 cursor-pointer items-center justify-between gap-4 rounded-xl border border-gray-800 bg-gray-950 px-3 text-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-500 hover:bg-gray-900 hover:shadow-lg hover:shadow-black/20">
                    <span class="font-semibold text-zinc-200">Otomatik Sonraki Video</span>
                    <span class="relative inline-flex h-6 w-11 shrink-0 items-center">
                        <input type="checkbox" data-player-autoplay class="peer sr-only" role="switch" aria-label="Otomatik sonraki video">
                        <span class="absolute inset-0 rounded-full bg-zinc-700 transition-colors duration-200 peer-checked:bg-red-600"></span>
                        <span class="relative h-5 w-5 translate-x-0.5 rounded-full bg-zinc-200 shadow-sm transition-transform duration-200 peer-checked:translate-x-5 peer-checked:bg-white"></span>
                    </span>
                </label>
            </div>
        </div>

        @if ($video->captions->isNotEmpty() || $chapters->isNotEmpty())
            <div class="mt-3 flex flex-col gap-3 border-t border-gray-800 pt-3 sm:flex-row sm:flex-wrap sm:items-center">
                @if ($video->captions->isNotEmpty())
                    <x-watch.player-control data-player-captions aria-pressed="false" class="h-10 px-3">
                        <x-heroicon-o-language class="h-4 w-4" />
                        <span>Altyazı</span>
                    </x-watch.player-control>
                @endif

                @if ($chapters->isNotEmpty())
                    <div class="flex h-10 min-w-0 items-center rounded-xl border border-gray-800 bg-gray-950 p-1 text-zinc-200">
                        <button type="button" data-player-previous-chapter class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-900 hover:text-red-400" title="Önceki bölüm" aria-label="Önceki bölüm"><x-heroicon-o-backward class="h-4 w-4" /></button>
                        <select data-player-chapter aria-label="Video bölümü" style="appearance: none; -webkit-appearance: none;" class="min-w-0 max-w-56 border-0 bg-transparent px-2 text-sm font-medium outline-none focus:ring-0">
                            @foreach ($chapters as $chapter)
                                <option class="bg-gray-950" value="{{ $chapter['start'] }}">{{ $chapter['formatted'] }} · {{ $chapter['title'] }}</option>
                            @endforeach
                        </select>
                        <button type="button" data-player-next-chapter class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition hover:bg-gray-900 hover:text-red-400" title="Sonraki bölüm" aria-label="Sonraki bölüm"><x-heroicon-o-forward class="h-4 w-4" /></button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <p class="mt-3 text-xs text-zinc-500">Kısayollar: Boşluk oynat/duraklat, ←/→ 5 sn sar, ↑/↓ ses, M sessiz, C altyazı, F tam ekran, N/P bölüm.</p>
    <p data-player-status class="sr-only" aria-live="polite"></p>
</section>
