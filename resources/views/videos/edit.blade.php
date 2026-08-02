@extends('layouts.turtube')

@section('title', 'Videoyu Düzenle')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="bg-zinc-900 border border-zinc-800 rounded-2xl overflow-hidden shadow-xl">

        <div class="border-b border-zinc-800 px-8 py-6">

            <h1 class="text-3xl font-bold text-white">
                ✏️ Videoyu Düzenle
            </h1>

            <p class="mt-2 text-zinc-400">
                Videonun bilgilerini güncelle.
            </p>

        </div>

        <form
            action="{{ route('videos.update', $video) }}"
            method="POST">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-8">

                {{-- Sol Taraf --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Başlık --}}
                    <div>

                        <label class="block mb-2 font-semibold text-white">
                            Başlık
                        </label>

                        <input
                            type="text"
                            name="title"
                            value="{{ old('title', $video->title) }}"
                            class="w-full rounded-xl bg-zinc-800 border border-zinc-700 px-4 py-3 text-white focus:border-red-500 focus:ring-red-500">

                    </div>

                    {{-- Açıklama --}}
                    <div>

                        <label class="block mb-2 font-semibold text-white">
                            Açıklama
                        </label>

                        <textarea
                            rows="10"
                            name="description"
                            class="w-full rounded-xl bg-zinc-800 border border-zinc-700 px-4 py-3 text-white focus:border-red-500 focus:ring-red-500">{{ old('description', $video->description) }}</textarea>

                    </div>

                    {{-- Kategori --}}
                    <div>

                        <label class="block mb-2 font-semibold text-white">
                            Kategori
                        </label>

                        <select
                            name="category_id"
                            class="w-full rounded-xl bg-zinc-800 border border-zinc-700 px-4 py-3 text-white">

                            <option value="">
                                Kategori Seçin
                            </option>

                            @foreach($categories as $category)

                                <option
                                    value="{{ $category->id }}"
                                    @selected(old('category_id', $video->category_id) == $category->id)>

                                    {{ $category->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- Yayın Durumu --}}
                    <div>

                        <label class="block mb-2 font-semibold text-white">
                            Yayın Durumu
                        </label>

                        <select
                            name="status"
                            class="w-full rounded-xl bg-zinc-800 border border-zinc-700 px-4 py-3 text-white">

                            <option value="public" @selected(old('status', $video->status) == 'public')>
                                🌍 Herkese Açık
                            </option>

                            <option value="unlisted" @selected(old('status', $video->status) == 'unlisted')>
                                🔗 Liste Dışı
                            </option>

                            <option value="private" @selected(old('status', $video->status) == 'private')>
                                🔒 Gizli
                            </option>

                            <option value="draft" @selected(old('status', $video->status) == 'draft')>
                                📝 Taslak
                            </option>

                        </select>

                    </div>

                </div>

                {{-- Sağ Taraf --}}
                <div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">

                        <h2 class="mb-5 text-lg font-semibold text-white">

                            Önizleme

                        </h2>

                        @if($video->thumbnail)

                            <img
                                src="{{ $video->thumbnail_url }}"
                                class="aspect-video w-full rounded-xl object-cover">

                        @else

                            <div class="flex aspect-video items-center justify-center rounded-xl bg-zinc-800 text-5xl">

                                🎬

                            </div>

                        @endif

                        <div class="mt-5 space-y-2">

                            <p class="font-semibold text-white">

                                {{ $video->title }}

                            </p>

                            <p class="text-sm text-zinc-400">

                                {{ number_format($video->views) }} görüntülenme

                            </p>

                            <p class="text-sm text-zinc-400">

                                {{ $video->created_at->format('d.m.Y H:i') }}

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <div class="flex justify-between border-t border-zinc-800 px-8 py-6">

                <a
                    href="{{ route('videos.mine') }}"
                    class="rounded-xl bg-zinc-700 px-6 py-3 font-medium text-white transition hover:bg-zinc-600">

                    ← Geri Dön

                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-red-600 px-8 py-3 font-semibold text-white transition hover:bg-red-700">

                    💾 Değişiklikleri Kaydet

                </button>

            </div>

        </form>

    </div>

</div>

@endsection