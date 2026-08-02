@extends('studio.layouts.app')

@section('title', 'Analytics')

@section('content')

<div class="mx-auto max-w-7xl space-y-8">

    {{-- Başlık --}}
    <div class="flex items-center justify-between">

        <div>

            <h1 class="text-4xl font-bold text-white">
                📈 Analytics
            </h1>

            <p class="mt-2 text-gray-400">
                Kanal performansının genel görünümü.
            </p>

        </div>

    </div>

    {{-- İstatistik Kartları --}}
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

    {{-- Grafik Alanı --}}
    <div class="rounded-2xl border border-gray-800 bg-gray-900 p-6">

        <div class="flex items-center justify-between mb-6">

            <h2 class="text-xl font-semibold text-white">
                Görüntülenme Grafiği
            </h2>

            <span class="text-sm text-gray-500">
                Yakında gerçek zamanlı veriler
            </span>

        </div>

        <div class="h-80 rounded-xl border border-dashed border-gray-700 flex items-center justify-center">

            <div class="text-center">

                <div class="text-6xl mb-4">
                    📊
                </div>

                <p class="text-gray-400">

                    Grafik sistemi bir sonraki adımda eklenecek.

                </p>

            </div>

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

            <div class="p-16 text-center text-gray-500">

                Henüz görüntülenme verisi bulunmuyor.

            </div>

        @else

            <table class="min-w-full">

                <thead class="bg-gray-950">

                    <tr class="text-left text-xs uppercase tracking-wider text-gray-500">

                        <th class="px-6 py-4">

                            Video

                        </th>

                        <th class="px-6 py-4">

                            Görüntülenme

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($topVideos as $video)

                        <tr class="border-t border-gray-800 hover:bg-gray-800/40 transition">

                            <td class="px-6 py-4">

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

                            </td>

                            <td class="px-6 py-4">

                                <span class="text-xl font-bold text-white">

                                    {{ number_format($video->views) }}

                                </span>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @endif

    </div>

</div>

@endsection