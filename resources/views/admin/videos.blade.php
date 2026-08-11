@extends('layouts.turtube')

@section('title', 'Video Yönetimi')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Moderasyon</p>
            <h1 class="mt-2 text-4xl font-bold text-white">Videolar</h1>
            <p class="mt-2 text-gray-400">Toplu silme, öne çıkarma, yaş sınırı ve telif yönetimi.</p>
        </div>
        <form method="GET">
            <input name="q" value="{{ request('q') }}" maxlength="100" placeholder="Video ara..." class="rounded-xl border border-gray-700 bg-gray-900 px-4 py-2 text-white focus:border-red-500 focus:outline-none">
        </form>
    </div>

    <form id="bulk-video-form" method="POST" action="{{ route('admin.videos.bulk-update') }}" class="mb-5 flex flex-col gap-3 rounded-2xl border border-gray-800 bg-gray-900 p-4 sm:flex-row sm:items-center">
        @csrf
        @method('PATCH')
        <p class="text-sm text-gray-400">Seçilen videolar için:</p>
        <select name="action" class="rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-white">
            <option value="feature">Öne çıkar</option>
            <option value="unfeature">Öne çıkarmayı kaldır</option>
            <option value="age_0">Yaş sınırını kaldır</option>
            <option value="age_13">13+ yaş sınırı</option>
            <option value="age_16">16+ yaş sınırı</option>
            <option value="age_18">18+ yaş sınırı</option>
            <option value="copyright_warning">Telif uyarısı ver</option>
            <option value="copyright_clear">Telif uyarısını kaldır</option>
            <option value="copyright_block">Telif nedeniyle engelle</option>
            <option value="delete">Kalıcı olarak sil</option>
        </select>
        <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Uygula</button>
    </form>

    <div class="space-y-4">
        @forelse ($videos as $video)
            <x-ui.card class="p-5">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div class="flex min-w-0 gap-4">
                        <input form="bulk-video-form" type="checkbox" name="video_ids[]" value="{{ $video->id }}" aria-label="{{ $video->title }} videosunu seç" class="mt-1 rounded border-gray-600 bg-gray-950 text-red-600 focus:ring-red-500">
                        @if ($video->thumbnail)
                            <img src="{{ $video->thumbnail_url }}" alt="" class="h-16 w-28 shrink-0 rounded-xl object-cover">
                        @else
                            <div class="flex h-16 w-28 shrink-0 items-center justify-center rounded-xl bg-zinc-800 text-zinc-500">▶</div>
                        @endif
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('videos.show', $video) }}" class="line-clamp-1 font-bold text-white hover:text-red-400">{{ $video->title }}</a>
                                <span class="rounded-full bg-sky-500/15 px-2 py-1 text-xs font-semibold text-sky-300">{{ $video->is_short ? 'Shorts' : 'Video' }}</span>
                                <span class="rounded-full bg-zinc-700 px-2 py-1 text-xs font-semibold text-zinc-200">{{ $video->category?->name ?? 'Kategorisiz' }}</span>
                                @if ($video->is_featured)
                                    <span class="rounded-full bg-amber-500/15 px-2 py-1 text-xs font-semibold text-amber-300">Öne çıkan</span>
                                @endif
                                @if ($video->age_restriction)
                                    <span class="rounded-full bg-orange-500/15 px-2 py-1 text-xs font-semibold text-orange-300">{{ $video->age_restriction }}+</span>
                                @endif
                                @if ($video->copyright_status !== 'none')
                                    <span class="rounded-full bg-rose-500/15 px-2 py-1 text-xs font-semibold text-rose-300">Telif {{ $video->copyright_status === 'blocked' ? 'engeli' : 'uyarısı' }}</span>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-gray-400">
                                {{ $video->user?->name ?? $video->channel_name ?? 'Bilinmeyen kanal' }} ·
                                {{ number_format($video->views) }} görüntülenme ·
                                {{ $video->created_at->format('d.m.Y') }} ·
                                {{ $video->processing_status === 'ready' ? 'Hazır' : 'İşleniyor' }}
                            </p>
                            @if ($video->copyright_note)
                                <p class="mt-2 text-sm text-rose-200">{{ $video->copyright_note }}</p>
                            @endif
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.videos.moderation', $video) }}" class="grid gap-2 sm:grid-cols-2 xl:w-[460px]">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-white">
                            <option value="public" @selected($video->status === 'public')>Yayında</option>
                            <option value="unlisted" @selected($video->status === 'unlisted')>Liste dışı</option>
                            <option value="private" @selected($video->status === 'private')>Gizli</option>
                            <option value="draft" @selected($video->status === 'draft')>Taslak</option>
                        </select>
                        <select name="age_restriction" class="rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-white">
                            <option value="0" @selected($video->age_restriction === 0)>Yaş sınırı yok</option>
                            <option value="13" @selected($video->age_restriction === 13)>13+</option>
                            <option value="16" @selected($video->age_restriction === 16)>16+</option>
                            <option value="18" @selected($video->age_restriction === 18)>18+</option>
                        </select>
                        <select name="copyright_status" class="rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-white">
                            <option value="none" @selected($video->copyright_status === 'none')>Telif sorunu yok</option>
                            <option value="warning" @selected($video->copyright_status === 'warning')>Telif uyarısı</option>
                            <option value="blocked" @selected($video->copyright_status === 'blocked')>Telif engeli</option>
                        </select>
                        <label class="flex items-center gap-2 rounded-lg border border-gray-700 px-3 py-2 text-sm text-gray-300">
                            <input type="checkbox" name="is_featured" value="1" @checked($video->is_featured) class="rounded border-gray-600 bg-gray-950 text-red-600">
                            Öne çıkar
                        </label>
                        <input name="copyright_note" value="{{ $video->copyright_note }}" maxlength="2000" placeholder="Telif notu (isteğe bağlı)" class="sm:col-span-2 rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-white">
                        <button class="sm:col-span-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Moderasyonu kaydet</button>
                    </form>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card class="p-12 text-center text-gray-400">Video bulunamadı.</x-ui.card>
        @endforelse
    </div>

    <div class="mt-8">{{ $videos->links() }}</div>
</div>
@endsection
