@extends('layouts.turtube')

@section('title', 'Videoyu Düzenle')

@section('content')

<div class="max-w-3xl mx-auto">

    <h1 class="text-3xl font-bold mb-8">
        ✏️ Videoyu Düzenle
    </h1>

    <form
        action="{{ route('videos.update', $video) }}"
        method="POST"
        class="space-y-6">

        @csrf
        @method('PUT')

        <!-- Başlık -->
        <div>

            <label class="block mb-2 font-semibold">
                Başlık
            </label>

            <input
                type="text"
                name="title"
                value="{{ old('title', $video->title) }}"
                class="w-full rounded-lg bg-gray-800 border border-gray-700 px-4 py-3 text-white">

        </div>

        <!-- Açıklama -->
        <div>

            <label class="block mb-2 font-semibold">
                Açıklama
            </label>

            <textarea
                name="description"
                rows="8"
                class="w-full rounded-lg bg-gray-800 border border-gray-700 px-4 py-3 text-white">{{ old('description', $video->description) }}</textarea>

        </div>

        <div class="flex gap-4">

            <button
                type="submit"
                class="bg-red-600 hover:bg-red-700 px-6 py-3 rounded-lg font-semibold transition">

                💾 Kaydet

            </button>

            <a
                href="{{ route('videos.mine') }}"
                class="bg-gray-700 hover:bg-gray-600 px-6 py-3 rounded-lg transition">

                İptal

            </a>

        </div>

    </form>

</div>

@endsection