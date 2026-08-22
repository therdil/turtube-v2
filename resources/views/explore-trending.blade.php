@extends('layouts.turtube')

@section('title', 'Trend Videolar - TurTube')
@section('meta_description', 'TurTube topluluğunun en çok izlediği trend videoları keşfet.')
@section('og_title', 'Trend Videolar')
@section('og_description', 'TurTube topluluğunun en çok izlediği trend videoları keşfet.')

@section('content')

<div class="mx-auto max-w-[1800px] space-y-8">
    <section data-theme-hero class="overflow-hidden rounded-3xl border border-red-500/20 bg-gradient-to-br from-red-950 via-gray-900 to-gray-950 p-8 sm:p-10">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-400">TurTube keşfet</p>
        <h1 class="mt-3 text-4xl font-bold tracking-tight text-white sm:text-5xl">Trend videolar</h1>
        <p class="mt-4 max-w-2xl text-base leading-7 text-gray-300">
            Topluluğun en çok izlediği videoları tek bir yerde keşfet.
        </p>
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
            <h2 class="text-2xl font-bold text-white">Henüz trend video yok</h2>
            <p class="mt-3 text-gray-400">Yeni videolar yayınlandıkça burada görünecek.</p>
        </x-ui.card>
    @endif
</div>

@endsection
