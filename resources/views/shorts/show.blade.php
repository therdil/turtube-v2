@extends('layouts.turtube')

@section('title', $video->title.' · Shorts')
@section('meta_description', \Illuminate\Support\Str::limit($video->description ?: $video->title, 155))
@section('og_type', 'video.other')
@if ($video->thumbnail)
    @section('og_image', $video->thumbnail_url)
@endif

@section('content')
<div class="mx-auto h-[calc(100dvh-8.5rem)] max-w-5xl snap-y snap-mandatory overflow-y-auto overscroll-contain scrollbar-hide" data-shorts-player-feed data-authenticated="{{ auth()->check() ? 'true' : 'false' }}" data-login-url="{{ route('login') }}">
    @foreach ($feed as $short)
        @php
            $isLiked = $likedIds->contains($short->id);
            $isDisliked = $dislikedIds->contains($short->id);
            $isSaved = $savedIds->contains($short->id);
            $isSubscribed = $subscribedChannelIds->contains($short->user_id);
            $isOwner = auth()->id() === $short->user_id;
            $subscribersCount = (int) ($subscriberCounts[$short->user_id] ?? 0);
        @endphp

        <article
            data-short-item
            data-short-id="{{ $short->id }}"
            class="flex min-h-[calc(100dvh-9rem)] snap-start items-center justify-center py-3 sm:py-5"
        >
            <div class="w-full">
                <div class="mx-auto flex w-fit max-w-full items-center justify-center gap-4 lg:gap-6">
                    <div
                        data-short-stage
                        style="--short-height: min(66dvh, 680px)"
                        class="relative aspect-[9/16] h-[var(--short-height)] w-[calc(var(--short-height)*9/16)] max-w-full overflow-hidden rounded-[2rem] border border-zinc-800 bg-black shadow-2xl shadow-black/40"
                    >
                        <video
                            data-short-video
                            class="h-full w-full object-contain"
                            muted
                            playsinline
                            preload="metadata"
                            aria-label="{{ $short->title }} Short videosu"
                        >
                            <source src="{{ $short->video_url }}" type="video/mp4">
                        </video>

                        <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent px-3 pb-3 pt-16">
                            <div class="pointer-events-auto flex items-center gap-2">
                                <button type="button" data-short-play aria-label="Videoyu duraklat" class="flex h-10 w-10 items-center justify-center rounded-xl bg-black/45 text-white backdrop-blur transition hover:bg-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400">
                                    <x-heroicon-o-pause class="h-5 w-5" data-short-pause-icon />
                                    <x-heroicon-o-play class="hidden h-5 w-5" data-short-play-icon />
                                </button>
                                <button type="button" data-short-mute aria-label="Sesi aç" class="flex h-10 w-10 items-center justify-center rounded-xl bg-black/45 text-white backdrop-blur transition hover:bg-zinc-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400">
                                    <x-heroicon-o-speaker-x-mark class="h-5 w-5" data-short-muted-icon />
                                    <x-heroicon-o-speaker-wave class="hidden h-5 w-5" data-short-unmuted-icon />
                                </button>
                                <input data-short-progress type="range" min="0" max="100" value="0" aria-label="Video ilerlemesi" class="h-1 min-w-0 flex-1 accent-red-500">
                                <button type="button" data-short-fullscreen aria-label="Tam ekran" class="flex h-10 w-10 items-center justify-center rounded-xl bg-black/45 text-white backdrop-blur transition hover:bg-zinc-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-400">
                                    <x-heroicon-o-arrows-pointing-out class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <div data-short-paused class="pointer-events-none absolute inset-0 hidden items-center justify-center bg-black/20">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-black/60 text-white backdrop-blur"><x-heroicon-o-play class="ml-1 h-8 w-8" /></span>
                        </div>
                    </div>

                    <div class="hidden w-20 shrink-0 flex-col items-center gap-3 sm:flex">
                        <button type="button" data-short-like data-url="{{ route('videos.like', $short) }}" data-active="{{ $isLiked ? 'true' : 'false' }}" aria-label="Videoyu beğen" class="short-action {{ $isLiked ? 'is-active' : '' }}">
                            <x-heroicon-o-heart class="h-5 w-5" /><span data-short-likes-count>{{ number_format($short->likes_count) }}</span>
                        </button>
                        <button type="button" data-short-dislike data-url="{{ route('videos.dislike', $short) }}" data-active="{{ $isDisliked ? 'true' : 'false' }}" aria-label="Videoyu beğenme" class="short-action {{ $isDisliked ? 'is-active' : '' }}">
                            <x-heroicon-o-hand-thumb-down class="h-5 w-5" /><span data-short-dislikes-count>{{ number_format($short->dislikes_count) }}</span>
                        </button>
                        <button type="button" data-short-comments-open aria-label="Yorumları aç" class="short-action"><x-heroicon-o-chat-bubble-left class="h-5 w-5" /><span data-short-comments-count>{{ number_format($short->comments_count) }}</span></button>
                        <button type="button" data-short-share aria-label="Videoyu paylaş" class="short-action"><x-heroicon-o-share class="h-5 w-5" /><span>Paylaş</span></button>
                        <button type="button" data-short-save data-url="{{ route('watch-later.toggle', $short) }}" data-active="{{ $isSaved ? 'true' : 'false' }}" aria-label="Videoyu kaydet" class="short-action {{ $isSaved ? 'is-active' : '' }}"><x-heroicon-o-bookmark class="h-5 w-5" /><span data-short-save-text>{{ $isSaved ? 'Kayıtlı' : 'Kaydet' }}</span></button>
                        @if (! $isOwner)
                            <button type="button" data-short-report-open aria-label="Videoyu bildir" class="short-action"><x-heroicon-o-flag class="h-5 w-5" /><span>Bildir</span></button>
                        @endif
                    </div>
                </div>

                <div class="mx-auto mt-4 w-full max-w-[calc(min(66dvh,680px)*9/16)] px-1">
                    <div class="flex items-center justify-between gap-3">
                        <a href="{{ route('channels.show', $short->user) }}" class="flex min-w-0 items-center gap-3 rounded-2xl text-left transition hover:text-red-300">
                            @if ($short->user->avatar)
                                <img src="{{ asset('storage/'.$short->user->avatar) }}" alt="" class="h-10 w-10 rounded-full object-cover">
                            @else
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600 text-sm font-bold text-white">{{ strtoupper(substr($short->display_channel_name, 0, 1)) }}</span>
                            @endif
                            <span class="min-w-0"><span class="flex items-center gap-1 truncate font-semibold text-white">{{ $short->display_channel_name }} @if ($short->user->is_verified)<x-heroicon-s-check-badge class="h-4 w-4 shrink-0 text-sky-400" aria-label="Doğrulanmış kanal" />@endif</span><span class="block text-xs text-zinc-400">{{ number_format($subscribersCount) }} abone</span></span>
                        </a>

                        @auth
                            @if (! $isOwner)
                                <button type="button" data-short-subscribe data-url="{{ route('channels.subscribe', $short->user) }}" data-active="{{ $isSubscribed ? 'true' : 'false' }}" class="shrink-0 rounded-xl px-3 py-2 text-sm font-semibold transition {{ $isSubscribed ? 'bg-zinc-800 text-zinc-100 hover:bg-zinc-700' : 'bg-red-600 text-white hover:bg-red-500' }}"><span data-short-subscribe-text>{{ $isSubscribed ? 'Abone olundu' : 'Abone ol' }}</span></button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="shrink-0 rounded-xl bg-red-600 px-3 py-2 text-sm font-semibold text-white transition hover:bg-red-500">Abone ol</a>
                        @endauth
                    </div>

                    <h1 class="mt-3 text-lg font-bold leading-6 text-white">{{ $short->title }}</h1>
                    @if (filled($short->description))
                        <details class="group mt-2 text-sm leading-6 text-zinc-400">
                            <summary class="cursor-pointer list-none font-medium text-zinc-300 marker:hidden"><span class="group-open:hidden">Açıklamayı göster</span><span class="hidden group-open:inline">Daha az göster</span></summary>
                            <p class="whitespace-pre-line">{{ $short->description }}</p>
                        </details>
                    @endif
                </div>

                <div class="mx-auto mt-4 grid w-full max-w-[calc(min(66dvh,680px)*9/16)] grid-cols-3 gap-2 px-1 sm:hidden">
                    <button type="button" data-short-like data-url="{{ route('videos.like', $short) }}" data-active="{{ $isLiked ? 'true' : 'false' }}" aria-label="Videoyu beğen" class="short-action {{ $isLiked ? 'is-active' : '' }}"><x-heroicon-o-heart class="h-5 w-5" /><span data-short-likes-count>{{ number_format($short->likes_count) }}</span></button>
                    <button type="button" data-short-dislike data-url="{{ route('videos.dislike', $short) }}" data-active="{{ $isDisliked ? 'true' : 'false' }}" aria-label="Videoyu beğenme" class="short-action {{ $isDisliked ? 'is-active' : '' }}"><x-heroicon-o-hand-thumb-down class="h-5 w-5" /><span data-short-dislikes-count>{{ number_format($short->dislikes_count) }}</span></button>
                    <button type="button" data-short-comments-open aria-label="Yorumları aç" class="short-action"><x-heroicon-o-chat-bubble-left class="h-5 w-5" /><span data-short-comments-count>{{ number_format($short->comments_count) }}</span></button>
                    <button type="button" data-short-share aria-label="Videoyu paylaş" class="short-action"><x-heroicon-o-share class="h-5 w-5" /><span>Paylaş</span></button>
                    <button type="button" data-short-save data-url="{{ route('watch-later.toggle', $short) }}" data-active="{{ $isSaved ? 'true' : 'false' }}" aria-label="Videoyu kaydet" class="short-action {{ $isSaved ? 'is-active' : '' }}"><x-heroicon-o-bookmark class="h-5 w-5" /><span data-short-save-text>{{ $isSaved ? 'Kayıtlı' : 'Kaydet' }}</span></button>
                    @if (! $isOwner)<button type="button" data-short-report-open aria-label="Videoyu bildir" class="short-action"><x-heroicon-o-flag class="h-5 w-5" /><span>Bildir</span></button>@endif
                </div>
            </div>

            <div data-short-comments-sheet hidden class="shorts-comments-layer fixed inset-0 z-[70] flex items-end p-0 lg:items-stretch lg:justify-end lg:p-6" role="dialog" aria-modal="true" aria-label="Shorts yorumları">
                <button type="button" data-short-sheet-backdrop data-short-sheet-close class="absolute inset-0 cursor-default" aria-label="Yorumları kapat"></button>
                <section data-short-comments-panel class="shorts-comments-panel relative flex h-[58dvh] max-h-[60dvh] w-full flex-col rounded-t-3xl border border-zinc-700 bg-zinc-950 px-5 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-3 shadow-2xl lg:h-auto lg:max-h-none lg:w-[min(24rem,calc(100vw-6rem))] lg:rounded-3xl lg:p-5">
                    <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-zinc-600 lg:hidden" aria-hidden="true"></div>
                    <div class="flex shrink-0 items-center justify-between border-b border-zinc-800 pb-4"><h2 class="font-bold text-white">Yorumlar <span data-short-comments-count>{{ number_format($short->comments_count) }}</span></h2><button type="button" data-short-sheet-close aria-label="Yorumları kapat" class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-800 hover:text-white"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div>
                    <div data-short-comments-list class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain py-4">
                        @forelse ($short->comments as $comment)
                            @php($commentReaction = $commentReactions->get($comment->id))
                            <article class="rounded-2xl bg-zinc-900/80 p-3">
                                <div class="flex gap-3">
                                    <a href="{{ route('channels.show', $comment->user) }}" class="shrink-0" aria-label="{{ $comment->user->name }} kanalına git">
                                        @if ($comment->user->avatar)
                                            <img src="{{ asset('storage/'.$comment->user->avatar) }}" alt="" class="h-9 w-9 rounded-full object-cover">
                                        @else
                                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-zinc-700 text-xs font-bold text-white">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</span>
                                        @endif
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <p class="flex min-w-0 items-center gap-1 text-sm font-semibold text-white">
                                            <a href="{{ route('channels.show', $comment->user) }}" class="truncate transition hover:text-red-300">{{ $comment->user->name }}</a>
                                            @if ($comment->user->is_verified)<x-heroicon-s-check-badge class="h-4 w-4 shrink-0 text-sky-400" aria-label="Doğrulanmış kanal" />@endif
                                            <span class="ml-1 shrink-0 text-xs font-normal text-zinc-500">{{ $comment->created_at->diffForHumans() }}</span>
                                        </p>
                                        <p class="mt-1 break-words whitespace-pre-line text-sm leading-5 text-zinc-300">{{ $comment->comment }}</p>
                                        @auth
                                            <div class="mt-2 flex gap-2">
                                                <button type="button" data-comment-reaction="like" data-url="{{ route('comments.reaction', $comment) }}" aria-label="Yorumu beğen" aria-pressed="{{ $commentReaction === 'like' ? 'true' : 'false' }}" class="rounded-lg px-2 py-1 text-xs text-zinc-400 transition hover:bg-zinc-800 hover:text-white {{ $commentReaction === 'like' ? 'bg-red-600/15 text-red-200' : '' }}">♡ <span data-comment-likes>{{ $comment->likes_count }}</span></button>
                                                <button type="button" data-comment-reaction="dislike" data-url="{{ route('comments.reaction', $comment) }}" aria-label="Yorumu beğenme" aria-pressed="{{ $commentReaction === 'dislike' ? 'true' : 'false' }}" class="rounded-lg px-2 py-1 text-xs text-zinc-400 transition hover:bg-zinc-800 hover:text-white {{ $commentReaction === 'dislike' ? 'bg-red-600/15 text-red-200' : '' }}">👎 <span data-comment-dislikes>{{ $comment->dislikes_count }}</span></button>
                                            </div>
                                        @endauth
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p data-short-comments-empty class="py-8 text-center text-sm text-zinc-500">Henüz yorum yapılmamış. İlk yorumu sen yaz.</p>
                        @endforelse
                    </div>
                    @auth
                        <form data-short-comment-form action="{{ route('comments.store', $short) }}" class="mt-3 flex items-end gap-2 border-t border-zinc-800 pt-4"><textarea data-short-comment-input name="comment" rows="2" maxlength="2000" required placeholder="Yorum yaz..." class="min-h-11 flex-1 resize-none rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white placeholder:text-zinc-500 focus:border-red-500 focus:outline-none"></textarea><button class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-500">Gönder</button></form>
                    @else
                        <a href="{{ route('login') }}" class="mt-3 rounded-xl bg-red-600 px-4 py-3 text-center text-sm font-semibold text-white">Yorum yapmak için giriş yap</a>
                    @endauth
                </section>
            </div>

            @if (! $isOwner)
                <div data-short-report-dialog hidden class="fixed inset-0 z-[80] flex items-center justify-center bg-black/60 p-4" role="dialog" aria-modal="true" aria-label="Shorts videosunu bildir">
                    <button type="button" data-short-report-close class="absolute inset-0 cursor-default" aria-label="Rapor penceresini kapat"></button>
                    <form data-short-report-form action="{{ route('videos.reports.store', $short) }}" class="relative w-full max-w-md rounded-3xl border border-zinc-700 bg-zinc-950 p-5 shadow-2xl">
                        <div class="flex items-center justify-between"><h2 class="text-lg font-bold text-white">Videoyu bildir</h2><button type="button" data-short-report-close class="rounded-xl p-2 text-zinc-400 hover:bg-zinc-800"><x-heroicon-o-x-mark class="h-5 w-5" /></button></div>
                        @auth
                            <label class="mt-5 block text-sm font-medium text-zinc-200">Rapor nedeni<select name="reason" required class="mt-2 w-full rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-2.5 text-white"><option value="">Seçin</option>@foreach (\App\Models\VideoReport::REASONS as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label>
                            <textarea name="details" rows="3" maxlength="2000" placeholder="İsteğe bağlı açıklama" class="mt-3 w-full rounded-xl border border-zinc-700 bg-zinc-900 px-3 py-2 text-sm text-white placeholder:text-zinc-500"></textarea>
                            <button class="mt-4 w-full rounded-xl bg-red-600 px-4 py-3 font-semibold text-white hover:bg-red-500">Raporu gönder</button>
                        @else
                            <p class="mt-4 text-sm leading-6 text-zinc-400">Bir videoyu bildirmek için önce giriş yapmalısın.</p><a href="{{ route('login') }}" class="mt-4 block rounded-xl bg-red-600 px-4 py-3 text-center font-semibold text-white">Giriş yap</a>
                        @endauth
                    </form>
                </div>
            @endif
        </article>
    @endforeach
</div>
@endsection
