@extends('studio.layouts.app')

@section('title', 'İçerikler')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Creator Studio</p>
            <h1 class="mt-2 text-3xl font-bold text-white">İçerikler</h1>
            <p class="mt-2 text-zinc-400">Filtreye uyan {{ number_format($videos->total()) }} video</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('studio.videos.export', request()->query()) }}" class="rounded-xl border border-zinc-700 px-5 py-3 text-sm font-semibold text-zinc-100 transition hover:border-emerald-500 hover:text-white">CSV dışa aktar</a>
            <a href="{{ route('videos.create') }}" class="rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">+ Video yükle</a>
        </div>
    </div>

    <form method="GET" class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            <label class="block xl:col-span-2"><span class="mb-2 block text-sm text-zinc-400">Video ara</span><input type="search" name="search" value="{{ request('search') }}" placeholder="Başlık veya açıklama" class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"></label>
            <label class="block"><span class="mb-2 block text-sm text-zinc-400">Durum</span><select name="status" class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"><option value="all">Tümü</option>@foreach (['public' => 'Yayında', 'unlisted' => 'Liste dışı', 'private' => 'Gizli', 'draft' => 'Taslak'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="block"><span class="mb-2 block text-sm text-zinc-400">Tür</span><select name="type" class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"><option value="">Tümü</option><option value="video" @selected(request('type') === 'video')>Standart video</option><option value="short" @selected(request('type') === 'short')>Shorts</option></select></label>
            <label class="block"><span class="mb-2 block text-sm text-zinc-400">Kategori</span><select name="category_id" class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"><option value="">Tümü</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label class="block"><span class="mb-2 block text-sm text-zinc-400">Yayın dönemi</span><select name="period" class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"><option value="">Tüm zamanlar</option><option value="7" @selected(request('period') === '7')>Son 7 gün</option><option value="30" @selected(request('period') === '30')>Son 30 gün</option><option value="90" @selected(request('period') === '90')>Son 90 gün</option></select></label>
            <label class="block"><span class="mb-2 block text-sm text-zinc-400">İşleme durumu</span><select name="processing" class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"><option value="">Tümü</option>@foreach (['pending' => 'Sırada', 'processing' => 'İşleniyor', 'ready' => 'Hazır', 'failed' => 'Hata'] as $value => $label)<option value="{{ $value }}" @selected(request('processing') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label class="block"><span class="mb-2 block text-sm text-zinc-400">Premium</span><select name="premium" class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"><option value="">Tümü</option><option value="yes" @selected(request('premium') === 'yes')>Premium</option><option value="no" @selected(request('premium') === 'no')>Standart</option></select></label>
            <label class="block"><span class="mb-2 block text-sm text-zinc-400">Thumbnail</span><select name="thumbnail" class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white"><option value="">Tümü</option><option value="yes" @selected(request('thumbnail') === 'yes')>Var</option><option value="no" @selected(request('thumbnail') === 'no')>Eksik</option></select></label>
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <label class="text-sm text-zinc-400">Sıralama <select name="sort" class="ml-2 rounded-lg border border-zinc-700 bg-zinc-800 px-3 py-2 text-white"><option value="latest">En yeni</option><option value="oldest" @selected(request('sort') === 'oldest')>En eski</option><option value="views" @selected(request('sort') === 'views')>En çok izlenen</option><option value="likes" @selected(request('sort') === 'likes')>En çok beğenilen</option><option value="comments" @selected(request('sort') === 'comments')>En çok yorum alan</option></select></label>
            <div class="flex gap-3"><a href="{{ route('studio.videos.index') }}" class="rounded-xl px-4 py-3 text-sm font-medium text-zinc-400 hover:text-white">Sıfırla</a><button class="rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700">Filtrele</button></div>
        </div>
    </form>

    <form id="bulk-video-form" method="POST" action="{{ route('studio.videos.bulk-update') }}" onsubmit="if (this.elements.action.value === 'delete') return confirm('Seçili videolar ve medya dosyaları kalıcı olarak silinecek. Devam etmek istiyor musunuz?');" class="flex flex-col gap-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
        @csrf
        @method('PATCH')
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p class="font-semibold text-white">Toplu işlemler</p><p class="mt-1 text-sm text-zinc-400">Seçili videolarda durum, kategori ve oynatma listesi işlemlerini uygulayın.</p></div><label class="flex cursor-pointer items-center gap-2 text-sm text-zinc-300"><input id="select-all-videos" type="checkbox" class="rounded border-zinc-600 bg-zinc-800 text-red-600"> Bu sayfadakilerin tümünü seç</label></div>
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5"><select name="action" class="rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white"><option value="status">Görünürlüğü değiştir</option><option value="category">Kategoriyi değiştir</option><option value="playlist">Oynatma listesine ekle</option><option value="delete">Kalıcı sil</option></select><select name="status" class="rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white"><option value="public">Yayına al</option><option value="unlisted">Liste dışı yap</option><option value="private">Gizle</option><option value="draft">Taslağa al</option></select><select name="category_id" class="rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white"><option value="">Kategori seç</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select><select name="playlist_id" class="rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-sm text-white"><option value="">Oynatma listesi seç</option>@foreach($playlists as $playlist)<option value="{{ $playlist->id }}">{{ $playlist->name }}</option>@endforeach</select><button class="rounded-xl border border-red-500/60 px-4 py-3 text-sm font-semibold text-red-200 hover:bg-red-500 hover:text-white">Seçilene uygula</button></div>
    </form>

    <div class="space-y-4">
        @forelse($videos as $video)
            <article class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5 transition hover:border-zinc-700">
                <div class="grid gap-5 lg:grid-cols-[24px_220px_1fr]">
                    <input form="bulk-video-form" type="checkbox" name="video_ids[]" value="{{ $video->id }}" class="mt-2 h-4 w-4 rounded border-zinc-600 bg-zinc-800 text-red-600 focus:ring-red-500" aria-label="{{ $video->title }} videosunu seç">
                    @if($video->thumbnail)
                        <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" loading="lazy" class="aspect-video w-full rounded-xl object-cover">
                    @else
                        <div class="flex aspect-video items-center justify-center rounded-xl bg-zinc-800 text-4xl">🎬</div>
                    @endif
                    <div class="min-w-0">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div><h2 class="line-clamp-2 text-xl font-bold text-white">{{ $video->title }}</h2><p class="mt-2 line-clamp-2 text-sm text-zinc-400">{{ \Illuminate\Support\Str::limit($video->description, 180) }}</p></div>
                            <div class="shrink-0 text-left sm:text-right"><p class="text-2xl font-bold text-red-400">{{ $video->creator_score }}/100</p><p class="text-xs uppercase tracking-widest text-zinc-500">Creator Score</p><span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ ['red' => 'bg-red-500/15 text-red-300', 'yellow' => 'bg-amber-500/15 text-amber-200', 'blue' => 'bg-blue-500/15 text-blue-200', 'green' => 'bg-emerald-500/15 text-emerald-200', 'gray' => 'bg-zinc-700 text-zinc-300'][$video->creator_badge['color']] }}">{{ $video->creator_badge['emoji'] }} {{ $video->creator_badge['text'] }}</span></div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                            <span class="rounded-full bg-zinc-800 px-3 py-1 text-zinc-300">{{ number_format($video->views) }} izlenme</span><span class="rounded-full bg-zinc-800 px-3 py-1 text-zinc-300">{{ number_format($video->likes_count) }} beğeni</span><span class="rounded-full bg-zinc-800 px-3 py-1 text-zinc-300">{{ number_format($video->comments_count) }} yorum</span><span class="rounded-full bg-zinc-800 px-3 py-1 text-zinc-300">{{ $video->is_short ? 'Shorts' : 'Video' }}</span><span class="rounded-full px-3 py-1 font-semibold {{ $video->status === 'public' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-200' }}">{{ ['public' => 'Yayında', 'unlisted' => 'Liste dışı', 'private' => 'Gizli', 'draft' => 'Taslak'][$video->status] }}</span>
                        </div>
                        @if (count($video->creator_suggestions))
                            <p class="mt-4 rounded-xl border border-amber-500/20 bg-amber-500/10 p-3 text-sm text-amber-100">{{ $video->creator_suggestions[0] }}</p>
                        @endif
                        <div class="mt-5 flex flex-wrap gap-3"><a href="{{ route('videos.show', $video) }}" class="rounded-xl bg-zinc-700 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-600">İzle</a><a href="{{ route('videos.edit', $video) }}" class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500">Düzenle</a><a href="{{ route('studio.analytics.index') }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Analytics</a></div>
                    </div>
                </div>
            </article>
        @empty
            <x-ui.card class="p-16 text-center"><div class="text-5xl">🎬</div><h2 class="mt-5 text-2xl font-bold text-white">Bu filtrede içerik yok</h2><p class="mt-2 text-zinc-400">Filtreleri sıfırlayın veya yeni bir video yükleyin.</p><a href="{{ route('videos.create') }}" class="mt-6 inline-flex rounded-xl bg-red-600 px-5 py-3 font-semibold text-white">Video yükle</a></x-ui.card>
        @endforelse
    </div>

    @if($videos->hasPages())<div class="pt-4">{{ $videos->links() }}</div>@endif
</div>
@endsection

@push('scripts')
<script>
const selectAllVideos = document.getElementById('select-all-videos');
if (selectAllVideos) {
    selectAllVideos.addEventListener('change', () => {
        document.querySelectorAll('input[name="video_ids[]"]').forEach((checkbox) => {
            checkbox.checked = selectAllVideos.checked;
        });
    });
}
</script>
@endpush
