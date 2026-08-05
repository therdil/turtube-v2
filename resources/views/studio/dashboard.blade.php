@extends('studio.layouts.app')

@section('title', 'Creator Studio')

@section('content')
@php
    $maxDailyViews = max(1, $dailyChart->max('views'));
    $maxBreakdown = fn ($items) => max(1, $items->max('views'));
    $formatDuration = fn (int $seconds) => $seconds >= 3600 ? intdiv($seconds, 3600).' sa '.intdiv($seconds % 3600, 60).' dk' : intdiv($seconds, 60).' dk '.($seconds % 60).' sn';
@endphp
<div class="mx-auto max-w-7xl space-y-8" data-live-summary="{{ route('studio.dashboard.summary') }}">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Creator Studio</p><h1 class="mt-2 text-4xl font-bold text-white">Kanal özeti</h1><p class="mt-3 text-gray-400">Performansın otomatik olarak güncellenir.</p></div>
        <a href="{{ route('videos.create') }}" class="w-fit rounded-xl bg-red-600 px-6 py-3 font-semibold text-white hover:bg-red-700">+ Video yükle</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach (['24h' => 'Son 24 saat', '7d' => 'Son 7 gün', '30d' => 'Son 30 gün'] as $key => $label)
            <div class="rounded-2xl border border-gray-800 bg-gradient-to-br from-gray-900 to-gray-950 p-5"><p class="text-sm text-gray-400">{{ $label }} görüntülenme</p><p data-live-stat="{{ $key }}" class="mt-2 text-3xl font-bold text-white">{{ number_format($periods[$key]) }}</p></div>
        @endforeach
        <div class="rounded-2xl border border-gray-800 bg-gradient-to-br from-gray-900 to-gray-950 p-5"><p class="text-sm text-gray-400">Son 48 saat görüntülenme</p><p data-live-stat="48h" class="mt-2 text-3xl font-bold text-white">{{ number_format($periods['48h']) }}</p></div>
        <div class="rounded-2xl border border-gray-800 bg-gradient-to-br from-gray-900 to-gray-950 p-5"><p class="text-sm text-gray-400">Son 28 gün görüntülenme</p><p data-live-stat="28d" class="mt-2 text-3xl font-bold text-white">{{ number_format($periods['28d']) }}</p></div>
        <div class="rounded-2xl border border-red-500/20 bg-gradient-to-br from-red-500/10 to-gray-950 p-5">
            <div class="flex items-center justify-between gap-3"><p class="text-sm text-gray-300">Son 60 dakika</p><span class="inline-flex h-2.5 w-2.5 animate-pulse rounded-full bg-red-500"></span></div>
            <p data-live-stat="live" class="mt-2 text-3xl font-bold text-white">{{ number_format($periods['live']) }}</p>
            <p class="mt-1 text-xs text-gray-500">Gerçek zamanlı görüntülenme</p>
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-studio.stat-card title="Toplam video" :value="$stats['videos']" icon="🎬" />
        <x-studio.stat-card title="Toplam görüntülenme" :value="$stats['views']" icon="👁" />
        <x-studio.stat-card title="Toplam beğeni" :value="$stats['likes']" icon="❤" />
        <x-studio.stat-card title="Toplam yorum" :value="$stats['comments']" icon="💬" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-5"><p class="text-sm text-gray-400">Son 28 gün izlenme süresi</p><p class="mt-2 text-2xl font-bold text-white">{{ $formatDuration($stats['watchTime']) }}</p></section>
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-5"><p class="text-sm text-gray-400">Ort. izlenme süresi</p><p class="mt-2 text-2xl font-bold text-white">{{ $formatDuration($stats['averageWatchTime']) }}</p></section>
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-5"><p class="text-sm text-gray-400">Ort. izlenme yüzdesi</p><p class="mt-2 text-2xl font-bold text-white">{{ $stats['viewPercentage'] }}%</p></section>
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-5"><p class="text-sm text-gray-400">Gösterim tıklama oranı</p><p class="mt-2 text-2xl font-bold text-white">{{ $stats['ctr'] === null ? '—' : $stats['ctr'].'%' }}</p><p class="mt-1 text-xs text-gray-500">{{ $stats['ctr'] === null ? 'Gösterim verisi toplanıyor' : 'Ana sayfa gösterimlerinden' }}</p></section>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-6 xl:col-span-2"><div class="flex items-center justify-between gap-4"><div><h2 class="text-xl font-bold text-white">Günlük görüntülenme</h2><p class="mt-1 text-sm text-gray-400">Son 14 günün performansı</p></div><a href="{{ route('studio.analytics.index', ['period' => 28]) }}" class="text-sm font-semibold text-red-400 hover:text-red-300">Detaylı analiz →</a></div><div class="mt-6 flex h-52 items-end gap-2 overflow-x-auto pb-1">@foreach ($dailyChart as $index => $point)@php($height = max(3, ($point['views'] / $maxDailyViews) * 100))<div class="group flex h-full min-w-8 flex-1 flex-col justify-end gap-2"><div class="relative flex flex-1 items-end"><span class="pointer-events-none absolute -top-8 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-zinc-800 px-2 py-1 text-xs text-white shadow-xl group-hover:block">{{ number_format($point['views']) }} izlenme</span><div class="w-full rounded-t-lg bg-gradient-to-t from-red-700 to-red-400 transition-all duration-300 group-hover:from-red-600 group-hover:to-red-300" style="height: {{ $height }}%"></div></div><span class="text-center text-[10px] text-gray-500">{{ $index % 2 === 0 ? $point['label'] : '' }}</span></div>@endforeach</div></section>
        <section class="rounded-2xl border border-gray-800 bg-gray-900"><div class="flex items-center justify-between border-b border-gray-800 px-5 py-4"><h2 class="font-bold text-white">En başarılı videolar</h2><a href="{{ route('studio.analytics.index') }}" class="text-xs font-semibold text-red-400 hover:text-red-300">Tümü →</a></div><div class="divide-y divide-gray-800">@forelse($topVideos as $video)<a href="{{ route('videos.show', $video) }}" class="flex items-center justify-between gap-3 p-4 transition hover:bg-gray-800/50"><p class="min-w-0 flex-1 truncate text-sm font-medium text-zinc-200">{{ $video->title }}</p><span class="shrink-0 text-xs text-zinc-500">{{ number_format($video->views) }}</span></a>@empty<p class="p-5 text-sm text-zinc-500">Performans verisi oluştuğunda burada görünür.</p>@endforelse</div></section>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-6 xl:col-span-2"><div class="flex items-center justify-between gap-4"><div><h2 class="text-xl font-bold text-white">En hızlı büyüyen video</h2><p class="mt-1 text-sm text-gray-400">Son 7 günün önceki 7 güne göre farkı</p></div><a href="{{ route('studio.analytics.index') }}" class="text-sm font-semibold text-red-400 hover:text-red-300">Analytics →</a></div>
            @if ($fastestGrowingVideo)<div class="mt-5 flex flex-col gap-5 sm:flex-row sm:items-center">@if($fastestGrowingVideo->thumbnail)<img src="{{ $fastestGrowingVideo->thumbnail_url }}" alt="{{ $fastestGrowingVideo->title }}" class="aspect-video w-full max-w-52 rounded-xl object-cover">@endif<div><h3 class="text-lg font-bold text-white">{{ $fastestGrowingVideo->title }}</h3><p class="mt-2 text-emerald-300">+{{ number_format($fastestGrowth) }} görüntülenme artışı</p><a href="{{ route('videos.edit', $fastestGrowingVideo) }}" class="mt-4 inline-block rounded-lg bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Videoyu düzenle</a></div></div>@else<div class="mt-5 rounded-xl border border-dashed border-gray-700 p-6 text-sm text-gray-400">Büyüme karşılaştırması, yeni izlenme verileri oluştukça burada görünür.</div>@endif
        </section>
        <aside class="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-6"><h2 class="text-xl font-bold text-white">Akıllı öneriler</h2><div class="mt-4 space-y-3">@foreach($suggestions as $suggestion)<p class="rounded-xl bg-gray-950/70 p-3 text-sm leading-6 text-gray-300">{{ $suggestion }}</p>@endforeach</div></aside>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        @foreach (['Trafik kaynakları' => $trafficSources, 'Ülke dağılımı' => $countries] as $title => $items)
            <section class="rounded-2xl border border-gray-800 bg-gray-900 p-6"><div class="flex items-center justify-between"><div><h2 class="text-xl font-bold text-white">{{ $title }}</h2><p class="mt-1 text-sm text-gray-400">Son 28 gündeki görüntülenmeler</p></div><a href="{{ route('studio.analytics.index', ['period' => 28]) }}" class="text-sm font-semibold text-red-400 hover:text-red-300">Detay →</a></div><div class="mt-5 space-y-4">@forelse($items as $item)<div><div class="mb-2 flex items-center justify-between gap-3 text-sm"><span class="truncate text-zinc-200">{{ $item->label }}</span><span class="shrink-0 text-zinc-400">{{ number_format($item->views) }}</span></div><div class="h-2 overflow-hidden rounded-full bg-zinc-800"><div class="h-full rounded-full bg-red-500" style="width: {{ max(4, ($item->views / $maxBreakdown($items)) * 100) }}%"></div></div></div>@empty<p class="rounded-xl border border-dashed border-zinc-700 p-5 text-sm text-zinc-500">Görüntülenme verisi oluştuğunda dağılım burada görünür.</p>@endforelse</div></section>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <section class="rounded-2xl border border-gray-800 bg-gray-900"><div class="border-b border-gray-800 px-6 py-4"><h2 class="font-bold text-white">Son yorumlar</h2></div><div class="divide-y divide-gray-800">@forelse($recentComments as $comment)<div class="p-5"><div class="flex items-center justify-between gap-3"><p class="font-semibold text-white">{{ $comment->user->name }}</p><span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span></div><p class="mt-2 line-clamp-2 text-sm text-gray-300">{{ $comment->comment }}</p><a href="{{ route('videos.show', $comment->video) }}" class="mt-2 inline-block text-xs font-semibold text-red-400 hover:text-red-300">{{ $comment->video->title }}</a></div>@empty<p class="p-8 text-center text-sm text-gray-400">Henüz yorum bulunmuyor.</p>@endforelse</div></section>
        <section class="rounded-2xl border border-gray-800 bg-gray-900"><div class="border-b border-gray-800 px-6 py-4"><h2 class="font-bold text-white">Son aboneler</h2></div><div class="divide-y divide-gray-800">@forelse($recentSubscribers as $subscription)<div class="flex items-center gap-3 p-5"><div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-600 font-bold text-white">{{ strtoupper(substr($subscription->subscriber->name, 0, 1)) }}</div><div><p class="font-semibold text-white">{{ $subscription->subscriber->name }}</p><p class="text-xs text-gray-500">{{ $subscription->created_at->diffForHumans() }}</p></div></div>@empty<p class="p-8 text-center text-sm text-gray-400">Henüz abone bulunmuyor.</p>@endforelse</div></section>
    </div>

    <section class="rounded-2xl border border-gray-800 bg-gray-900"><div class="flex items-center justify-between border-b border-gray-800 px-6 py-4"><h2 class="font-bold text-white">Son yüklenen videolar</h2><a href="{{ route('studio.videos.index') }}" class="text-sm font-semibold text-red-400 hover:text-red-300">Tüm içerikler →</a></div><div class="divide-y divide-gray-800">@forelse($latestVideos as $video)<div class="flex items-center justify-between gap-4 p-5"><div class="min-w-0"><p class="truncate font-semibold text-white">{{ $video->title }}</p><p class="mt-1 text-sm text-gray-400">{{ number_format($video->views) }} görüntülenme · {{ $video->created_at->diffForHumans() }}</p></div><a href="{{ route('videos.edit', $video) }}" class="rounded-lg bg-gray-800 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700">Düzenle</a></div>@empty<p class="p-8 text-center text-sm text-gray-400">Henüz video yüklemedin.</p>@endforelse</div></section>
</div>
@endsection

@push('scripts')
<script>
const dashboard = document.querySelector('[data-live-summary]');
if (dashboard) {
    const refreshSummary = async () => {
        try {
            const response = await fetch(dashboard.dataset.liveSummary, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            Object.entries(data.periods).forEach(([key, value]) => {
                const node = document.querySelector(`[data-live-stat="${key}"]`);
                if (node) node.textContent = Number(value).toLocaleString('tr-TR');
            });
        } catch {}
    };
    window.setInterval(refreshSummary, 30000);
}
</script>
@endpush
