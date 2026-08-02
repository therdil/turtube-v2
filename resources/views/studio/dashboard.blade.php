@extends('studio.layouts.app')

@section('title', 'Creator Studio')

@section('content')

<div class="mx-auto max-w-7xl space-y-8">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>

            <h1 class="text-4xl font-bold text-white">
                Creator Studio
            </h1>

            <p class="mt-2 text-gray-400">
                Kanalını buradan yönetebilir, videolarını takip edebilir ve performansını inceleyebilirsin.
            </p>

        </div>

        <a
            href="{{ route('videos.create') }}"
            class="rounded-xl bg-red-600 px-6 py-3 font-semibold text-white transition hover:bg-red-700">

            + Video Yükle

        </a>

    </div>

    {{-- Stats --}}
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-6">

            <p class="text-sm text-gray-400">
                🎥 Toplam Video
            </p>

            <h2 class="mt-3 text-4xl font-bold text-white">
                {{ number_format($stats['videos']) }}
            </h2>

        </div>

        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-6">

            <p class="text-sm text-gray-400">
                👁 Toplam Görüntülenme
            </p>

            <h2 class="mt-3 text-4xl font-bold text-white">
                {{ number_format($stats['views']) }}
            </h2>

        </div>

        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-6">

            <p class="text-sm text-gray-400">
                ❤️ Toplam Beğeni
            </p>

            <h2 class="mt-3 text-4xl font-bold text-white">
                {{ number_format($stats['likes']) }}
            </h2>

        </div>

        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-6">

            <p class="text-sm text-gray-400">
                💬 Toplam Yorum
            </p>

            <h2 class="mt-3 text-4xl font-bold text-white">
                {{ number_format($stats['comments']) }}
            </h2>

        </div>

    </div>

    {{-- En Çok İzlenen Videolar --}}
    <div class="rounded-2xl border border-gray-800 bg-gray-900 overflow-hidden">

        <div class="border-b border-gray-800 px-6 py-4">

            <h2 class="text-xl font-semibold text-white">

                🔥 En Çok İzlenen Videolar

            </h2>

        </div>

        @if($topVideos->isEmpty())

            <div class="p-10 text-center text-gray-500">

                Henüz görüntülenen video bulunmuyor.

            </div>

        @else

            <div class="divide-y divide-gray-800">

                @foreach($topVideos as $video)

                    <div class="flex items-center justify-between px-6 py-4">

                        <div class="flex items-center gap-4">

                            @if($video->thumbnail)

                                <img
                                    src="{{ $video->thumbnail_url }}"
                                    class="w-32 aspect-video rounded-lg object-cover">

                            @else

                                <div class="w-32 aspect-video rounded-lg bg-gray-800 flex items-center justify-center text-3xl">

                                    🎬

                                </div>

                            @endif

                            <div>

                                <h3 class="font-semibold text-white">

                                    {{ $video->title }}

                                </h3>

                                <p class="text-sm text-gray-400">

                                    {{ $video->created_at->format('d.m.Y') }}

                                </p>

                            </div>

                        </div>

                        <div class="text-right">

                            <p class="text-2xl font-bold text-white">

                                {{ number_format($video->views) }}

                            </p>

                            <p class="text-sm text-gray-400">

                                görüntülenme

                            </p>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>

    {{-- Son Videolar --}}
    <div class="rounded-2xl border border-gray-800 bg-gray-900 overflow-hidden">

        <div class="border-b border-gray-800 px-6 py-4">

            <h2 class="text-xl font-semibold text-white">

                🆕 Son Yüklenen Videolar

            </h2>

        </div>

        @if($latestVideos->isEmpty())

            <div class="p-12 text-center">

                <p class="text-gray-400">

                    Henüz video yüklemediniz.

                </p>

                <a
                    href="{{ route('videos.create') }}"
                    class="mt-6 inline-flex rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">

                    İlk Videonu Yükle

                </a>

            </div>

        @else

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-gray-950">

                        <tr class="text-left text-xs uppercase tracking-wider text-gray-500">

                            <th class="px-6 py-4">Video</th>

                            <th class="px-6 py-4">Görüntülenme</th>

                            <th class="px-6 py-4">Tarih</th>

                            <th class="px-6 py-4 text-right">İşlem</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($latestVideos as $video)

                            <tr class="border-t border-gray-800 hover:bg-gray-800/40 transition">

                                <td class="px-6 py-4">

                                    <div class="flex items-center gap-4">

                                        <img
                                            src="{{ $video->thumbnail_url }}"
                                            class="h-16 w-28 rounded-lg object-cover">

                                        <div>

                                            <h3 class="font-semibold text-white">

                                                {{ $video->title }}

                                            </h3>

                                            <p class="mt-1 text-sm text-gray-400">

                                                {{ $video->category?->name }}

                                            </p>

                                        </div>

                                    </div>

                                </td>

                                <td class="px-6 py-4 text-gray-300">

                                    {{ number_format($video->views) }}

                                </td>

                                <td class="px-6 py-4 text-gray-400">

                                    {{ $video->created_at->diffForHumans() }}

                                </td>

                                <td class="px-6 py-4 text-right">

                                    <a
                                        href="{{ route('videos.edit',$video) }}"
                                        class="rounded-lg bg-gray-800 px-4 py-2 text-sm text-white hover:bg-red-600 transition">

                                        Düzenle

                                    </a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</div>

@endsection