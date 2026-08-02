@extends('studio.layouts.app')

@section('title', 'İçerikler')

@section('content')

<div class="flex items-center justify-between mb-8">

    <div>

        <h1 class="text-3xl font-bold text-white">
            İçerikler
        </h1>

        <p class="mt-2 text-zinc-400">
            Toplam {{ $videos->total() }} video
        </p>

    </div>

    <a
        href="{{ route('videos.create') }}"
        class="rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700">

        + Video Yükle

    </a>

</div>

<div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900">

    <table class="min-w-full">

        <thead class="bg-zinc-950">

        <tr class="text-left text-xs uppercase tracking-wider text-zinc-400">

            <th class="px-6 py-4">Video</th>

            <th class="px-4 py-4">Durum</th>

            <th class="px-4 py-4">Kategori</th>

            <th class="px-4 py-4 text-center">👁</th>

            <th class="px-4 py-4 text-center">❤️</th>

            <th class="px-4 py-4 text-center">💬</th>

            <th class="px-4 py-4">Tarih</th>

            <th class="px-6 py-4 text-right">
                İşlemler
            </th>

        </tr>

        </thead>

        <tbody>

        @forelse($videos as $video)

            <tr class="border-t border-zinc-800 hover:bg-zinc-800/40 transition">

                <td class="px-6 py-5">

                    <div class="flex items-center gap-4">

                        @if($video->thumbnail)

                            <img
                                src="{{ $video->thumbnail_url }}"
                                class="w-40 aspect-video rounded-lg object-cover">

                        @else

                            <div class="w-40 aspect-video rounded-lg bg-zinc-800 flex items-center justify-center text-5xl">

                                🎬

                            </div>

                        @endif

                        <div>

                            <h2 class="font-semibold text-white">

                                {{ $video->title }}

                            </h2>

                            <p class="text-sm text-zinc-400 mt-1">

                                {{ \Illuminate\Support\Str::limit($video->description,80) }}

                            </p>

                        </div>

                    </div>

                </td>

                <td class="px-4 py-5">

                    @switch($video->status)

                        @case('public')

                            <span class="rounded-full bg-green-600/20 px-3 py-1 text-xs font-semibold text-green-400">

                                🌍 Yayında

                            </span>

                            @break

                        @case('private')

                            <span class="rounded-full bg-red-600/20 px-3 py-1 text-xs font-semibold text-red-400">

                                🔒 Gizli

                            </span>

                            @break

                        @case('unlisted')

                            <span class="rounded-full bg-blue-600/20 px-3 py-1 text-xs font-semibold text-blue-400">

                                🔗 Liste Dışı

                            </span>

                            @break

                        @default

                            <span class="rounded-full bg-yellow-500/20 px-3 py-1 text-xs font-semibold text-yellow-300">

                                📝 Taslak

                            </span>

                    @endswitch

                </td>

                <td class="px-4 py-5 text-zinc-300">

                    {{ $video->category?->name ?? '-' }}

                </td>

                <td class="px-4 py-5 text-center text-white">

                    {{ number_format($video->views) }}

                </td>

                <td class="px-4 py-5 text-center text-white">

                    {{ number_format($video->likes_count) }}

                </td>

                <td class="px-4 py-5 text-center text-white">

                    {{ number_format($video->comments_count) }}

                </td>

                <td class="px-4 py-5 text-zinc-400">

                    {{ $video->created_at->format('d.m.Y') }}

                </td>

                <td class="px-6 py-5">

                    <div class="flex justify-end gap-2">

                        <a
                            href="{{ route('videos.show',$video) }}"
                            class="rounded-lg bg-zinc-700 px-3 py-2 text-sm hover:bg-zinc-600 transition">

                            İzle

                        </a>

                        <a
                            href="{{ route('videos.edit',$video) }}"
                            class="rounded-lg bg-blue-600 px-3 py-2 text-sm hover:bg-blue-700 transition">

                            Düzenle

                        </a>

                        <form
                            action="{{ route('videos.destroy',$video) }}"
                            method="POST"
                            onsubmit="return confirm('Bu videoyu silmek istediğinize emin misiniz?')">

                            @csrf
                            @method('DELETE')

                            <button
                                class="rounded-lg bg-red-600 px-3 py-2 text-sm hover:bg-red-700 transition">

                                Sil

                            </button>

                        </form>

                    </div>

                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="8"
                    class="py-20 text-center text-zinc-500">

                    Henüz video yüklenmedi.

                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

</div>

<div class="mt-8">

    {{ $videos->links() }}

</div>

@endsection