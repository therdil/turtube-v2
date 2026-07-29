@extends('layouts.turtube')

@section('title', 'Kanallar')

@section('content')

<div class="mx-auto max-w-[1800px] space-y-8">
    <section>
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-400">TurTube topluluğu</p>
        <h1 class="mt-2 text-4xl font-bold tracking-tight text-white">Kanalları keşfet</h1>
        <p class="mt-3 text-gray-400">İlgi alanına uygun içerik üreticilerini bul ve kanallarına göz at.</p>
    </section>

    @if ($channels->isNotEmpty())
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach ($channels as $channel)
                <a href="{{ route('channels.show', $channel) }}" class="group rounded-2xl border border-gray-800 bg-gray-900 p-6 transition hover:-translate-y-1 hover:border-red-500/70 hover:shadow-xl hover:shadow-red-950/30">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-red-500 to-red-800 text-2xl font-bold text-white">
                        {{ strtoupper(substr($channel->name, 0, 1)) }}
                    </div>

                    <h2 class="mt-5 truncate text-xl font-bold text-white transition group-hover:text-red-400">{{ $channel->name }}</h2>

                    <p class="mt-2 text-sm text-gray-400">
                        {{ number_format($channel->subscribers_count) }} abone
                        <span class="px-1">·</span>
                        {{ number_format($channel->videos_count) }} video
                    </p>

                    <p class="mt-4 text-xs font-medium text-gray-500">
                        {{ number_format($channel->videos_sum_views ?? 0) }} görüntülenme
                    </p>
                </a>
            @endforeach
        </div>

        <div>{{ $channels->links() }}</div>
    @else
        <x-ui.card class="p-12 text-center">
            <h2 class="text-2xl font-bold text-white">Henüz kanal yok</h2>
            <p class="mt-3 text-gray-400">Yeni üyeler içerik üretmeye başladığında burada yer alacak.</p>
        </x-ui.card>
    @endif
</div>

@endsection
