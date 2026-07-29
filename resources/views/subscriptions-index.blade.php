@extends('layouts.turtube')

@section('title', 'Abonelikler')

@section('content')

<div class="mx-auto max-w-[1800px] space-y-8">
    <section class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-400">Senin akışın</p>
            <h1 class="mt-2 text-4xl font-bold tracking-tight text-white">Abonelikler</h1>
            <p class="mt-3 text-gray-400">Abone olduğun kanallardan en yeni videolar.</p>
        </div>

        <a href="{{ route('channels.index') }}" class="inline-flex w-fit items-center rounded-xl border border-gray-700 px-4 py-2 text-sm font-medium text-gray-200 transition hover:border-red-500 hover:text-white">
            Kanal keşfet
        </a>
    </section>

    @if ($videos->isNotEmpty())
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
            @foreach ($videos as $video)
                <x-video-card :video="$video" />
            @endforeach
        </div>

        <div>{{ $videos->links() }}</div>
    @else
        <x-ui.card class="p-12 text-center">
            <h2 class="text-2xl font-bold text-white">Akışın henüz boş</h2>
            <p class="mx-auto mt-3 max-w-lg leading-7 text-gray-400">
                Kanallara abone olduğunda yeni videoları burada göreceksin.
            </p>
            <a href="{{ route('channels.index') }}" class="mt-6 inline-flex rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">
                Kanalları keşfet
            </a>
        </x-ui.card>
    @endif
</div>

@endsection
