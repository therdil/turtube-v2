@extends('layouts.turtube')

@section('content')

<div class="mx-auto max-w-2xl">

    <h1 class="mb-8 text-3xl font-bold text-white">

        Yeni Playlist

    </h1>

    <form
        method="POST"
        action="{{ route('playlists.store') }}"
        class="space-y-6 rounded-xl border border-gray-800 bg-gray-900 p-8">

        @csrf

        <div>

            <label class="mb-2 block text-white">

                Playlist Adı

            </label>

            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                required
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 text-white">

        </div>

        <div>

            <label class="mb-2 block text-white">

                Açıklama

            </label>

            <textarea
                name="description"
                rows="4"
                class="w-full rounded-lg border border-gray-700 bg-gray-950 px-4 py-3 text-white">{{ old('description') }}</textarea>

        </div>

        <label class="flex items-center gap-3 text-white">

            <input
                type="checkbox"
                name="is_public"
                value="1">

            Herkese Açık

        </label>

        <button
            class="rounded-lg bg-red-600 px-6 py-3 font-semibold text-white hover:bg-red-700">

            Playlist Oluştur

        </button>

    </form>

</div>

@endsection