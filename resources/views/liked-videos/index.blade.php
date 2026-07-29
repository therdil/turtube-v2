@extends('layouts.turtube')

@section('title', 'Beğenilenler')

@section('content')

<div class="mx-auto max-w-[1800px] px-6 py-8">

    <div class="grid gap-8 xl:grid-cols-12">

        <div class="xl:col-span-3">
            <div class="sticky top-24 overflow-hidden rounded-2xl border border-gray-800 bg-gray-900">

                <div class="bg-gradient-to-br from-rose-600 via-red-700 to-red-950 p-8">
                    <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-white/10 text-4xl backdrop-blur">
                        ♥
                    </div>

                    <h1 class="text-3xl font-bold text-white">Beğenilenler</h1>

                    <p class="mt-3 text-red-100">
                        Beğendiğin videolara dilediğin zaman buradan ulaşabilirsin.
                    </p>
                </div>

                <div class="flex items-center justify-between p-6">
                    <span class="text-gray-400">Toplam Video</span>
                    <span class="rounded-lg bg-red-600 px-3 py-1 font-semibold text-white">{{ $videos->count() }}</span>
                </div>

            </div>
        </div>

        <div class="xl:col-span-9">
            @if ($videos->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-3">
                    @foreach ($videos as $video)
                        <x-video-card :video="$video" />
                    @endforeach
                </div>
            @else
                <div class="flex min-h-[500px] items-center justify-center rounded-2xl border border-dashed border-gray-700 bg-gray-900">
                    <div class="max-w-md text-center">
                        <div class="mb-6 text-6xl text-red-500">♥</div>
                        <h2 class="text-3xl font-bold text-white">Henüz beğeni yok</h2>
                        <p class="mt-4 leading-7 text-gray-400">
                            Hoşuna giden videoları beğenerek bu listede saklayabilirsin.
                        </p>
                        <a href="{{ route('home') }}" class="mt-8 inline-flex items-center rounded-xl bg-red-600 px-6 py-3 font-semibold text-white transition hover:bg-red-700">
                            Videoları keşfet
                        </a>
                    </div>
                </div>
            @endif
        </div>

    </div>

</div>

@endsection
