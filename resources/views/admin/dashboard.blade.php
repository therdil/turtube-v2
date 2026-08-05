@extends('layouts.turtube')

@section('title', 'Yonetim Paneli')

@section('content')
@php
    $formatBytes = function (int $bytes): string {
        if ($bytes < 1024) return $bytes.' B';

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;
        foreach ($units as $unit) {
            if ($value < 1024 || $unit === 'TB') return number_format($value, $value >= 10 ? 0 : 1).' '.$unit;
            $value /= 1024;
        }

        return '0 B';
    };
    $maxDailyViews = max(1, $dailyChart->max('views'));
@endphp

<div class="mx-auto max-w-7xl space-y-6 sm:space-y-8">
    <section class="overflow-hidden rounded-3xl border border-zinc-800 bg-gradient-to-br from-zinc-900 via-zinc-900 to-red-950/30 p-5 shadow-2xl shadow-black/20 sm:p-7">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em] text-red-300">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-red-400"></span>
                    Platform yonetimi
                </div>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl">TurTube kontrol merkezi</h1>
                <p class="mt-2 text-sm leading-6 text-zinc-400 sm:text-base">Icerik, guvenlik ve sistem sagligini tek bir ekrandan izleyin.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-zinc-700 bg-zinc-900/70 px-3.5 py-2.5 text-sm font-semibold text-zinc-200 transition hover:-translate-y-0.5 hover:border-red-500/60 hover:bg-zinc-800">Kullanicilar</a>
                <a href="{{ route('admin.videos.index') }}" class="rounded-xl border border-zinc-700 bg-zinc-900/70 px-3.5 py-2.5 text-sm font-semibold text-zinc-200 transition hover:-translate-y-0.5 hover:border-red-500/60 hover:bg-zinc-800">Videolar</a>
                <a href="{{ route('admin.reports.index') }}" class="rounded-xl bg-red-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-950/40 transition hover:-translate-y-0.5 hover:bg-red-500">Raporlari incele</a>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Kullanicilar', 'value' => $stats['users'], 'accent' => 'text-white', 'route' => 'admin.users.index'],
            ['label' => 'Videolar', 'value' => $stats['videos'], 'accent' => 'text-white', 'route' => 'admin.videos.index'],
            ['label' => 'Canli yayinlar', 'value' => $stats['liveStreams'], 'accent' => 'text-red-400', 'route' => 'admin.live.index'],
            ['label' => 'Premium uyeler', 'value' => $stats['premiumUsers'], 'accent' => 'text-amber-300', 'route' => 'admin.users.index'],
            ['label' => 'Acik raporlar', 'value' => $stats['openReports'], 'accent' => 'text-red-400', 'route' => 'admin.reports.index'],
        ] as $stat)
            <a href="{{ route($stat['route']) }}" class="group rounded-2xl border border-zinc-800 bg-zinc-900/90 p-5 shadow-lg shadow-black/10 transition duration-200 hover:-translate-y-1 hover:border-red-500/50 hover:bg-zinc-900">
                <p class="text-sm font-medium text-zinc-400">{{ $stat['label'] }}</p>
                <div class="mt-3 flex items-end justify-between gap-3">
                    <p class="text-3xl font-bold tracking-tight {{ $stat['accent'] }}">{{ number_format($stat['value']) }}</p>
                    <span class="h-8 w-8 rounded-xl border border-zinc-700 bg-zinc-800/80 transition group-hover:border-red-500/40 group-hover:bg-red-500/10"></span>
                </div>
            </a>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <x-ui.card class="overflow-hidden xl:col-span-2">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-bold text-white">Goruntulenme trendi</h2>
                    <p class="mt-1 text-sm text-zinc-400">Son 14 gunun gunluk toplam goruntulenmeleri</p>
                </div>
                <a href="{{ route('admin.analytics.index') }}" class="text-sm font-semibold text-red-400 transition hover:text-red-300">Detayli analytics</a>
            </div>
            <div class="flex h-64 items-end gap-1.5 px-4 pb-8 pt-6 sm:gap-2 sm:px-6">
                @foreach ($dailyChart as $point)
                    <div class="group relative flex h-full min-w-0 flex-1 items-end" title="{{ $point['label'] }}: {{ number_format($point['views']) }} goruntulenme">
                        <div class="w-full rounded-t-md bg-gradient-to-t from-red-600 to-red-400/70 transition duration-200 group-hover:from-red-500 group-hover:to-red-300" style="height: {{ max(4, round(($point['views'] / $maxDailyViews) * 100)) }}%"></div>
                        <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 whitespace-nowrap text-[10px] text-zinc-500 {{ $loop->iteration % 2 === 0 ? 'hidden sm:block' : '' }}">{{ $point['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        <x-ui.card class="overflow-hidden">
            <div class="border-b border-zinc-800 px-5 py-4">
                <h2 class="font-bold text-white">Sistem durumu</h2>
                <p class="mt-1 text-sm text-zinc-400">Yayin ortami ve isleyis ozeti</p>
            </div>
            <dl class="divide-y divide-zinc-800 px-5">
                <div class="flex items-center justify-between gap-4 py-3.5"><dt class="text-sm text-zinc-400">Ortam</dt><dd class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-300">{{ $system['environment'] }}</dd></div>
                <div class="flex items-center justify-between gap-4 py-3.5"><dt class="text-sm text-zinc-400">PHP</dt><dd class="text-sm font-semibold text-zinc-200">{{ $system['php_version'] }}</dd></div>
                <div class="flex items-center justify-between gap-4 py-3.5"><dt class="text-sm text-zinc-400">Cache</dt><dd class="text-sm font-semibold text-zinc-200">{{ $system['cache_driver'] }}</dd></div>
                <div class="flex items-center justify-between gap-4 py-3.5"><dt class="text-sm text-zinc-400">Queue</dt><dd class="text-sm font-semibold text-zinc-200">{{ $system['queue_driver'] }}</dd></div>
            </dl>
        </x-ui.card>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
        <x-ui.card class="overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-bold text-white">Operasyon ozeti</h2>
                    <p class="mt-1 text-sm text-zinc-400">Depolama ve arka plan islemleri</p>
                </div>
                <span class="h-2.5 w-2.5 rounded-full {{ $storage['status'] === 'available' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
            </div>
            <div class="grid gap-3 p-5 sm:grid-cols-3 sm:p-6">
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Storage</p>
                    <p class="mt-2 text-xl font-bold text-white">{{ $storage['status'] === 'available' ? $formatBytes($storage['bytes']) : 'Izlenemiyor' }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Bekleyen is</p>
                    <p class="mt-2 text-xl font-bold text-white">{{ is_null($queue['pending']) ? 'Takip disi' : number_format($queue['pending']) }}</p>
                </div>
                <div class="rounded-2xl border border-zinc-800 bg-zinc-950/50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500">Basarisiz is</p>
                    <p class="mt-2 text-xl font-bold {{ ($queue['failed'] ?? 0) > 0 ? 'text-red-400' : 'text-emerald-300' }}">{{ is_null($queue['failed']) ? 'Takip disi' : number_format($queue['failed']) }}</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="font-bold text-white">Son yonetici islemleri</h2>
                    <p class="mt-1 text-sm text-zinc-400">Hassas degisikliklerin denetim kaydi</p>
                </div>
                <span class="rounded-full border border-zinc-700 bg-zinc-800 px-2.5 py-1 text-xs font-semibold text-zinc-300">{{ $latestActivities->count() }}</span>
            </div>
            <div class="divide-y divide-zinc-800">
                @forelse ($latestActivities as $activity)
                    <div class="flex items-start gap-3 px-5 py-3.5 sm:px-6">
                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-400"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-200">{{ $activity->description }}</p>
                            <p class="mt-1 truncate text-xs text-zinc-500">{{ $activity->admin?->name ?? 'Sistem' }} · {{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-center sm:px-6">
                        <p class="text-sm font-medium text-zinc-300">Henuz kayitli islem yok</p>
                        <p class="mt-1 text-xs text-zinc-500">Yeni denetim kayitlari ilk yonetici islemiyle gorunecek.</p>
                    </div>
                @endforelse
            </div>
        </x-ui.card>
    </section>

    <x-ui.card class="overflow-hidden">
        <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-4 sm:px-6">
            <div>
                <h2 class="font-bold text-white">Yeni uyeler</h2>
                <p class="mt-1 text-sm text-zinc-400">Platforma en son katilan kullanicilar</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-red-400 transition hover:text-red-300">Tumunu gor</a>
        </div>
        <div class="divide-y divide-zinc-800">
            @forelse ($latestUsers as $user)
                <div class="flex items-center justify-between gap-4 px-5 py-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-600/90 text-sm font-bold text-white">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
                        <div class="min-w-0"><p class="truncate font-semibold text-white">{{ $user->name }}</p><p class="mt-0.5 truncate text-sm text-zinc-400">{{ $user->email }}</p></div>
                    </div>
                    <span class="shrink-0 text-xs text-zinc-500 sm:text-sm">{{ $user->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-sm text-zinc-400 sm:px-6">Henuz kullanici bulunamadi.</div>
            @endforelse
        </div>
    </x-ui.card>
</div>
@endsection
