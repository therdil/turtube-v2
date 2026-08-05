@extends('layouts.turtube')

@section('title', 'Favorilerim')

@section('content')
<div class="mx-auto max-w-[1800px] px-6 py-8">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-400">Kişisel liste</p><h1 class="mt-2 text-3xl font-bold text-white">Favorilerim</h1><p class="mt-2 text-gray-400">Beğendiğin ve saklamak istediğin videolar.</p></div>
        <span class="rounded-full bg-amber-400/15 px-4 py-2 font-semibold text-amber-200">{{ $videos->count() }} video</span>
    </div>
    @if ($videos->isNotEmpty())
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">@foreach ($videos as $video)<x-video-card :video="$video" />@endforeach</div>
    @else
        <div class="rounded-2xl border border-dashed border-gray-700 bg-gray-900 p-16 text-center"><div class="text-5xl">☆</div><h2 class="mt-4 text-2xl font-bold text-white">Favori listen boş</h2><p class="mt-2 text-gray-400">Video sayfasındaki yıldız düğmesiyle buraya içerik ekleyebilirsin.</p><a href="{{ route('home') }}" class="mt-6 inline-flex rounded-xl bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-500">Videoları keşfet</a></div>
    @endif
</div>
@endsection
