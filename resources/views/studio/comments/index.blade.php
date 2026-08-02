@extends('studio.layouts.app')

@section('title', 'Yorumlar')

@section('content')

<div class="flex items-center justify-between mb-8">

    <div>

        <h1 class="text-3xl font-bold text-white">
            💬 Yorumlar
        </h1>

        <p class="mt-2 text-zinc-400">
            Videolarına yapılan tüm yorumları buradan yönetebilirsin.
        </p>

    </div>

</div>

<div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900">

    <table class="min-w-full">

        <thead class="bg-zinc-950">

            <tr class="text-left text-xs uppercase tracking-wider text-zinc-400">

                <th class="px-6 py-4">
                    Kullanıcı
                </th>

                <th class="px-6 py-4">
                    Video
                </th>

                <th class="px-6 py-4">
                    Yorum
                </th>

                <th class="px-6 py-4">
                    Tarih
                </th>

                <th class="px-6 py-4 text-right">
                    İşlem
                </th>

            </tr>

        </thead>

        <tbody>

        @forelse($comments as $comment)

            <tr class="border-t border-zinc-800 hover:bg-zinc-800/40 transition">

                <td class="px-6 py-5">

                    <div>

                        <p class="font-semibold text-white">

                            {{ $comment->user->name }}

                        </p>

                    </div>

                </td>

                <td class="px-6 py-5">

                    <a
                        href="{{ route('videos.show', $comment->video) }}"
                        class="text-blue-400 hover:underline">

                        {{ $comment->video->title }}

                    </a>

                </td>

                <td class="px-6 py-5 text-zinc-300">

                    {{ $comment->comment }}

                </td>

                <td class="px-6 py-5 text-zinc-400">

                    {{ $comment->created_at->diffForHumans() }}

                </td>

                <td class="px-6 py-5">

                    <div class="flex justify-end">

                        <form
                            action="{{ route('studio.comments.destroy', $comment) }}"
                            method="POST"
                            onsubmit="return confirm('Bu yorumu silmek istediğinize emin misiniz?')">

                            @csrf
                            @method('DELETE')

                            <button
                                class="rounded-lg bg-red-600 px-4 py-2 text-sm hover:bg-red-700 transition">

                                🗑 Sil

                            </button>

                        </form>

                    </div>

                </td>

            </tr>

        @empty

            <tr>

                <td
                    colspan="5"
                    class="py-16 text-center text-zinc-500">

                    Henüz videolarınıza yorum yapılmamış.

                </td>

            </tr>

        @endforelse

        </tbody>

    </table>

</div>

<div class="mt-8">

    {{ $comments->links() }}

</div>

@endsection