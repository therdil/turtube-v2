@extends('layouts.turtube')

@section('title', 'İçerik Raporları')

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Moderasyon</p>
            <h1 class="mt-2 text-4xl font-bold text-white">İçerik raporları</h1>
            <p class="mt-3 text-gray-400">Kullanıcıların bildirdiği videoları inceleyin ve sonucu kaydedin.</p>
        </div>
        <form method="GET">
            <select name="status" onchange="this.form.submit()" class="rounded-xl border border-gray-700 bg-gray-900 px-4 py-2 text-white focus:border-red-500 focus:outline-none">
                <option value="">Tüm durumlar</option>
                @foreach (\App\Models\VideoReport::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($reports as $report)
            <x-ui.card class="p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-300">{{ \App\Models\VideoReport::REASONS[$report->reason] ?? $report->reason }}</span>
                            <span class="rounded-full bg-gray-800 px-3 py-1 text-xs font-semibold text-gray-300">{{ \App\Models\VideoReport::STATUSES[$report->status] }}</span>
                            <span class="text-sm text-gray-500">{{ $report->created_at->diffForHumans() }}</span>
                        </div>
                        <a href="{{ route('videos.show', $report->video) }}" class="mt-4 block text-xl font-bold text-white hover:text-red-400">{{ $report->video->title }}</a>
                        <p class="mt-2 text-sm text-gray-400">Bildiren: {{ $report->reporter->name }} · Kanal: {{ $report->video->user->name }}</p>
                        @if ($report->details)
                            <p class="mt-4 whitespace-pre-line rounded-xl bg-gray-950/70 p-4 text-sm leading-6 text-gray-300">{{ $report->details }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="flex shrink-0 items-center gap-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-sm text-white">
                            @foreach (\App\Models\VideoReport::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected($report->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Kaydet</button>
                    </form>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card class="p-12 text-center text-gray-400">Bu filtreye ait rapor bulunmuyor.</x-ui.card>
        @endforelse
    </div>
    <div class="mt-8">{{ $reports->links() }}</div>
</div>
@endsection
