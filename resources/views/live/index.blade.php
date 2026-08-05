@extends('layouts.turtube')

@section('title', 'Canlı Yayınlar')

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Şimdi yayında</p>
            <h1 class="mt-2 text-4xl font-bold text-white">Canlı yayınlar</h1>
            <p class="mt-3 text-gray-400">Yayınları izle veya kendi canlı yayınını planla.</p>
        </div>
        @auth
            <a href="{{ route('live.create') }}" class="inline-flex w-fit rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">Canlı yayın oluştur</a>
        @endauth
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($streams as $stream)
            <a href="{{ route('live.show', $stream) }}" class="group overflow-hidden rounded-2xl border border-gray-800 bg-gray-900 transition hover:-translate-y-1 hover:border-red-500">
                <div class="relative aspect-video bg-gradient-to-br from-red-950 to-gray-950">
                    @if ($stream->thumbnail)
                        <img src="{{ asset('storage/'.$stream->thumbnail) }}" alt="{{ $stream->title }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-5xl">📡</div>
                    @endif
                    <span class="absolute left-4 top-4 rounded-md px-2.5 py-1 text-xs font-bold {{ $stream->status === 'live' ? 'bg-red-600 text-white' : 'bg-gray-900 text-gray-200' }}">{{ $stream->status === 'live' ? 'CANLI' : 'PLANLANDI' }}</span>
                </div>
                <div class="p-5">
                    <h2 class="line-clamp-2 text-lg font-bold text-white">{{ $stream->title }}</h2>
                    <p class="mt-2 text-sm text-gray-400">{{ $stream->user->channel_name ?: $stream->user->name }}</p>
                    <p class="mt-3 text-xs text-gray-500">{{ $stream->status === 'live' ? number_format($stream->viewer_count).' izleyici' : optional($stream->scheduled_at)->format('d.m.Y H:i') }}</p>
                </div>
            </a>
        @empty
            <x-ui.card class="col-span-full p-12 text-center">
                <div class="text-5xl">📡</div>
                <h2 class="mt-4 text-2xl font-bold text-white">Planlanmış yayın yok</h2>
                <p class="mt-3 text-gray-400">Yeni canlı yayınlar burada görünecek.</p>
            </x-ui.card>
        @endforelse
    </div>
    <div class="mt-8">{{ $streams->links() }}</div>
</div>
@endsection
