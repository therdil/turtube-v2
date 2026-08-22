@extends('layouts.turtube')

@section('title', $query !== '' ? $query.' · Arama' : 'Arama')
@section('meta_robots', 'noindex,follow')

@section('content')
<div class="mx-auto max-w-[1800px] space-y-7 px-4 py-6 sm:px-6">
    <section class="rounded-3xl border border-zinc-800 bg-gradient-to-br from-zinc-900 to-zinc-950 p-5 shadow-xl sm:p-7">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-400">TurTube arama</p>
        <h1 class="mt-2 text-3xl font-bold text-white sm:text-4xl">İçerikleri keşfet</h1>
        <form action="{{ route('search') }}" method="GET" class="mt-5 flex flex-col gap-3 sm:flex-row">
            <label class="relative flex-1"><span class="sr-only">Video ara</span><x-heroicon-o-magnifying-glass class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-zinc-500" /><input type="search" name="q" value="{{ $query }}" maxlength="100" placeholder="Video, kanal veya konu ara..." class="w-full rounded-xl border border-zinc-700 bg-zinc-950 px-11 py-3 text-white placeholder:text-zinc-500 focus:border-red-500 focus:outline-none"></label>
            <button class="rounded-xl bg-red-600 px-6 py-3 font-semibold text-white transition hover:bg-red-500">Ara</button>
        </form>
    </section>

    @if ($recentSearches->isNotEmpty() || $trendingSearches->isNotEmpty())
        <div class="grid gap-4 xl:grid-cols-2">
            @if ($recentSearches->isNotEmpty())
                <section class="rounded-2xl border border-zinc-800 bg-zinc-900/80 p-5"><h2 class="font-bold text-white">Son aramalar</h2><div class="mt-4 flex flex-wrap gap-2">@foreach ($recentSearches as $search)<a href="{{ route('search', ['q' => $search->query]) }}" class="rounded-full border border-zinc-700 bg-zinc-950 px-3 py-1.5 text-sm text-zinc-300 transition hover:border-red-500 hover:text-white">{{ $search->query }}</a>@endforeach</div></section>
            @endif
            @if ($trendingSearches->isNotEmpty())
                <section class="rounded-2xl border border-red-500/15 bg-red-500/5 p-5"><h2 class="font-bold text-white">Trend aramalar</h2><div class="mt-4 flex flex-wrap gap-2">@foreach ($trendingSearches as $search)<a href="{{ route('search', ['q' => $search->query]) }}" class="rounded-full bg-zinc-950 px-3 py-1.5 text-sm font-medium text-zinc-200 transition hover:bg-red-600 hover:text-white">{{ $search->query }} <span class="ml-1 text-xs text-zinc-500">{{ number_format($search->searches) }}</span></a>@endforeach</div></section>
            @endif
        </div>
    @endif

    <form action="{{ route('search') }}" method="GET" class="rounded-2xl border border-zinc-800 bg-zinc-900 p-4 sm:p-5">
        <input type="hidden" name="q" value="{{ $query }}">
        <div class="flex items-center justify-between gap-4"><div><h2 class="font-bold text-white">Filtreler</h2><p class="mt-1 text-sm text-zinc-500">Sonuçları ihtiyacına göre daralt.</p></div><a href="{{ route('search', ['q' => $query]) }}" class="text-sm font-semibold text-zinc-400 hover:text-white">Temizle</a></div>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
            <label class="block text-sm text-zinc-400"><span class="mb-2 block">Kategori</span><select name="category_id" class="w-full rounded-xl border-zinc-700 bg-zinc-950 text-sm text-white focus:border-red-500"><option value="">Tümü</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? null) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label class="block text-sm text-zinc-400"><span class="mb-2 block">Süre</span><select name="duration" class="w-full rounded-xl border-zinc-700 bg-zinc-950 text-sm text-white focus:border-red-500"><option value="any">Tümü</option><option value="short" @selected(($filters['duration'] ?? null) === 'short')>4 dk altı</option><option value="medium" @selected(($filters['duration'] ?? null) === 'medium')>4–20 dk</option><option value="long" @selected(($filters['duration'] ?? null) === 'long')>20 dk üstü</option></select></label>
            <label class="block text-sm text-zinc-400"><span class="mb-2 block">İçerik</span><select name="shorts" class="w-full rounded-xl border-zinc-700 bg-zinc-950 text-sm text-white focus:border-red-500"><option value="any">Tümü</option><option value="yes" @selected(($filters['shorts'] ?? null) === 'yes')>Shorts</option><option value="no" @selected(($filters['shorts'] ?? null) === 'no')>Standart video</option></select></label>
            <label class="block text-sm text-zinc-400"><span class="mb-2 block">Premium</span><select name="premium" class="w-full rounded-xl border-zinc-700 bg-zinc-950 text-sm text-white focus:border-red-500"><option value="any">Tümü</option><option value="yes" @selected(($filters['premium'] ?? null) === 'yes')>Premium</option><option value="no" @selected(($filters['premium'] ?? null) === 'no')>Standart</option></select></label>
            <label class="block text-sm text-zinc-400"><span class="mb-2 block">Tarih</span><select name="date" class="w-full rounded-xl border-zinc-700 bg-zinc-950 text-sm text-white focus:border-red-500"><option value="any">Tümü</option><option value="today" @selected(($filters['date'] ?? null) === 'today')>Bugün</option><option value="week" @selected(($filters['date'] ?? null) === 'week')>Bu hafta</option><option value="month" @selected(($filters['date'] ?? null) === 'month')>Bu ay</option><option value="year" @selected(($filters['date'] ?? null) === 'year')>Bu yıl</option></select></label>
            <label class="block text-sm text-zinc-400"><span class="mb-2 block">Sıralama</span><select name="sort" class="w-full rounded-xl border-zinc-700 bg-zinc-950 text-sm text-white focus:border-red-500"><option value="relevance" @selected(($filters['sort'] ?? null) === 'relevance')>Alaka düzeyi</option><option value="newest" @selected(($filters['sort'] ?? null) === 'newest')>En yeni</option><option value="views" @selected(($filters['sort'] ?? null) === 'views')>En çok izlenen</option></select></label>
            <label class="flex h-[42px] cursor-pointer items-center gap-3 self-end rounded-xl border border-zinc-700 bg-zinc-950 px-3 text-sm text-zinc-300"><input name="hd" value="1" type="checkbox" @checked($filters['hd'] ?? false) class="rounded border-zinc-600 bg-zinc-900 text-red-600 focus:ring-red-500"> HD</label>
        </div>
        <button class="mt-5 rounded-xl bg-zinc-800 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600">Filtreleri uygula</button>
    </form>

    <section>
        <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"><div><h2 class="text-2xl font-bold text-white">Arama sonuçları</h2>@if($query !== '')<p class="mt-1 text-sm text-zinc-400"><span class="font-semibold text-white">“{{ $query }}”</span> için {{ number_format($videos->total()) }} sonuç bulundu.</p>@else<p class="mt-1 text-sm text-zinc-400">Aramak istediğin bir konu yaz veya trend aramalardan seç.</p>@endif</div></div>
        @if ($videos->count())
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">@foreach($videos as $video)<x-video-card :video="$video" />@endforeach</div>
            <div class="mt-10">{{ $videos->onEachSide(1)->links() }}</div>
        @else
            <div class="rounded-2xl border border-dashed border-zinc-700 bg-zinc-900 p-12 text-center"><x-heroicon-o-magnifying-glass class="mx-auto h-12 w-12 text-zinc-600" /><h3 class="mt-4 text-xl font-bold text-white">Sonuç bulunamadı</h3><p class="mt-2 text-zinc-400">Daha genel bir ifade kullanmayı veya filtreleri temizlemeyi dene.</p></div>
        @endif
    </section>
</div>
@endsection
