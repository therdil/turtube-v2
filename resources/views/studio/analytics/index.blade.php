@extends('studio.layouts.app')

@section('title', 'Analytics')

@section('content')
@php
    $formatDuration = fn (int $seconds) => $seconds >= 3600 ? intdiv($seconds, 3600).' sa '.intdiv($seconds % 3600, 60).' dk' : intdiv($seconds, 60).' dk '.($seconds % 60).' sn';
    $maxViews = max(1, $chart->max('views'));
    $maxBreakdown = fn ($items) => max(1, $items->max('views'));
@endphp
<div class="mx-auto max-w-7xl space-y-8" data-analytics-summary="{{ route('studio.dashboard.summary') }}">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"><div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Creator Studio</p><h1 class="mt-2 text-4xl font-bold text-white">Analytics</h1><p class="mt-3 text-gray-400">İzleyicilerinin kanalı nasıl keşfettiğini ve içeriklerini nasıl izlediğini takip et.</p></div><a href="{{ route('studio.videos.index') }}" class="w-fit rounded-xl border border-gray-700 px-4 py-2 text-sm font-medium text-gray-200 hover:border-red-500 hover:text-white">İçeriklere git</a></div>

    <div class="flex flex-wrap gap-2"><span class="self-center text-sm text-gray-500">Dönem:</span>@foreach ([7, 30, 365] as $period)<a href="{{ route('studio.analytics.index', ['period' => $period, 'group' => $group]) }}" class="rounded-xl px-3 py-2 text-sm font-semibold {{ $daysInPeriod === $period ? 'bg-red-600 text-white' : 'border border-gray-700 text-gray-300 hover:border-red-500 hover:text-white' }}">Son {{ $period }} gün</a>@endforeach<span class="ml-3 self-center text-sm text-gray-500">Grupla:</span>@foreach (['day' => 'Günlük', 'week' => 'Haftalık', 'month' => 'Aylık'] as $value => $label)<a href="{{ route('studio.analytics.index', ['period' => $daysInPeriod, 'group' => $value]) }}" class="rounded-xl px-3 py-2 text-sm font-semibold {{ $group === $value ? 'bg-gray-700 text-white' : 'border border-gray-700 text-gray-300 hover:border-gray-500 hover:text-white' }}">{{ $label }}</a>@endforeach</div>

    <a href="{{ route('studio.analytics.index', ['period' => 28, 'group' => $group]) }}" class="inline-flex rounded-xl px-3 py-2 text-sm font-semibold {{ $daysInPeriod === 28 ? 'bg-red-600 text-white' : 'border border-gray-700 text-gray-300 hover:border-red-500 hover:text-white' }}">Son 28 gün özeti</a>

    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <x-studio.stat-card title="Dönem görüntülenmesi" :value="$stats['views']" icon="👁" />
        <x-studio.stat-card title="Toplam izlenme süresi" :value="$formatDuration($stats['watchTime'])" icon="⏱" />
        <x-studio.stat-card title="Ort. izlenme süresi" :value="$formatDuration($stats['averageWatchTime'])" icon="📈" />
        <x-studio.stat-card title="Ort. izlenme yüzdesi" :value="$stats['viewPercentage'].'%'" icon="🎯" />
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-5"><p class="text-sm text-gray-400">Gösterim tıklama oranı</p><p class="mt-2 text-3xl font-bold text-white">{{ $stats['ctr'] === null ? '—' : $stats['ctr'].'%' }}</p><p class="mt-1 text-xs text-gray-500">{{ $stats['ctr'] === null ? 'Gösterim verisi toplanıyor' : 'Ana sayfa gösterimlerinden' }}</p></section>
        <section class="rounded-2xl border border-red-500/20 bg-gradient-to-br from-red-500/10 to-gray-950 p-5">
            <div class="flex items-center justify-between gap-3"><p class="text-sm text-gray-300">Son 60 dakika</p><span class="h-2.5 w-2.5 animate-pulse rounded-full bg-red-500"></span></div>
            <p data-live-realtime class="mt-2 text-3xl font-bold text-white">{{ number_format($realtimeViews) }}</p>
            <p class="mt-1 text-xs text-gray-500">Gerçek zamanlı görüntülenme</p>
        </section>
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-5"><p class="text-sm text-gray-400">Dönemde yeni abone</p><p class="mt-2 text-3xl font-bold text-white">+{{ number_format($subscriberChange) }}</p><p class="mt-1 text-xs {{ $subscriberChange >= $previousSubscriberChange ? 'text-emerald-400' : 'text-amber-300' }}">Önceki döneme göre {{ $subscriberChange >= $previousSubscriberChange ? 'güçlü' : 'takip edilmeli' }}</p></section>
        <section class="rounded-2xl border border-gray-800 bg-gray-900 p-5"><p class="text-sm text-gray-400">İzleyici bağlılığı</p><p class="mt-2 text-3xl font-bold text-white">{{ $stats['viewPercentage'] }}%</p><p class="mt-1 text-xs text-gray-500">Ortalama izlenme yüzdesi</p></section>
    </div>

    <section class="rounded-2xl border border-gray-800 bg-gray-900 p-6"><div class="flex items-center justify-between"><div><h2 class="text-xl font-bold text-white">Görüntülenme grafiği</h2><p class="mt-1 text-sm text-gray-400">{{ ['day' => 'Günlük', 'week' => 'Haftalık', 'month' => 'Aylık'][$group] }} görünüm</p></div><p class="text-sm text-gray-400">{{ number_format($stats['views']) }} görüntülenme</p></div>
        @if($chart->sum('views') > 0)<div class="mt-6 h-72 overflow-x-auto"><div class="flex h-full min-w-[640px] items-end gap-2">@foreach($chart as $index => $point)@php($height = max(3, ($point['views'] / $maxViews) * 100))<div class="group flex h-full min-w-8 flex-1 flex-col justify-end gap-2"><div class="relative flex flex-1 items-end"><span class="pointer-events-none absolute -top-8 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-gray-800 px-2 py-1 text-xs text-white group-hover:block">{{ number_format($point['views']) }} izlenme · {{ $formatDuration($point['watch_time']) }}</span><div class="w-full rounded-t bg-gradient-to-t from-red-700 to-red-400" style="height: {{ $height }}%"></div></div><span class="text-center text-[10px] text-gray-500">{{ $index % max(1, intdiv(max(1, $chart->count()), 8)) === 0 ? $point['label'] : '' }}</span></div>@endforeach</div></div>@else<div class="mt-6 rounded-xl border border-dashed border-gray-700 p-12 text-center text-gray-400">Bu dönem için görüntülenme verisi henüz oluşmadı.</div>@endif
    </section>

    <div class="grid gap-6 xl:grid-cols-3">
        @foreach (['trafficSources' => ['Trafik kaynakları', $trafficSources], 'devices' => ['Cihazlar', $devices], 'countries' => ['Ülkeler', $countries]] as [$title, $items])
            <section class="rounded-2xl border border-gray-800 bg-gray-900 p-6"><h2 class="text-xl font-bold text-white">{{ $title }}</h2><div class="mt-5 space-y-4">@forelse($items as $item)<div><div class="mb-2 flex items-center justify-between gap-3 text-sm"><span class="truncate text-gray-200">{{ $item->label }}</span><span class="shrink-0 text-gray-400">{{ number_format($item->views) }}</span></div><div class="h-2 overflow-hidden rounded-full bg-gray-800"><div class="h-full rounded-full bg-red-500" style="width: {{ max(4, ($item->views / $maxBreakdown($items)) * 100) }}%"></div></div></div>@empty<p class="text-sm leading-6 text-gray-400">Detaylı {{ strtolower($title) }} verisi, migration sonrasında yeni görüntülenmelerden oluşur.</p>@endforelse</div></section>
        @endforeach
    </div>

    <section class="rounded-2xl border border-gray-800 bg-gray-900"><div class="border-b border-gray-800 px-6 py-4"><h2 class="text-xl font-bold text-white">En iyi performans gösteren videolar</h2></div><div class="divide-y divide-gray-800">@forelse($topVideos as $video)<div class="flex items-center gap-4 p-5">@if($video->thumbnail)<img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="h-14 w-24 rounded-lg object-cover">@endif<div class="min-w-0 flex-1"><a href="{{ route('videos.show', $video) }}" class="line-clamp-1 font-semibold text-white hover:text-red-400">{{ $video->title }}</a><p class="mt-1 text-sm text-gray-500">{{ $video->created_at->format('d.m.Y') }}</p></div><p class="text-right text-lg font-bold text-white">{{ number_format($video->views) }}<span class="ml-1 text-xs font-normal text-gray-500">izlenme</span></p></div>@empty<p class="p-8 text-center text-gray-400">Henüz performans verisi yok.</p>@endforelse</div></section>
</div>
@endsection

@push('scripts')
<script>
const analyticsSummary = document.querySelector('[data-analytics-summary]');
if (analyticsSummary) {
    const refreshRealtime = async () => {
        try {
            const response = await fetch(analyticsSummary.dataset.analyticsSummary, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const node = document.querySelector('[data-live-realtime]');
            if (node) node.textContent = Number((await response.json()).periods.live || 0).toLocaleString('tr-TR');
        } catch {}
    };
    window.setInterval(refreshRealtime, 30000);
}
</script>
@endpush
