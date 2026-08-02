@extends('studio.layouts.app')

@section('title', 'Kanal')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

    {{-- Form --}}
    <div class="xl:col-span-2">

        <div class="rounded-2xl border border-gray-800 bg-gray-900 p-8">

            <h1 class="text-3xl font-bold mb-8">

                📺 Kanal

            </h1>

            @if(session('success'))

                <div class="mb-6 rounded-xl bg-green-600/20 border border-green-500 p-4">

                    {{ session('success') }}

                </div>

            @endif

            <form
                action="{{ route('studio.channel.update') }}"
                method="POST"
                enctype="multipart/form-data"
                class="space-y-8">

                @csrf
                @method('PUT')

                {{-- Kanal Adı --}}
                <div>

                    <label class="block mb-2 font-semibold">

                        Kanal Adı

                    </label>

                    <input
                        type="text"
                        name="channel_name"
                        value="{{ old('channel_name', $user->channel_name ?? $user->name) }}"
                        class="w-full rounded-xl bg-gray-800 border border-gray-700 px-4 py-3">

                </div>

                {{-- Açıklama --}}
                <div>

                    <label class="block mb-2 font-semibold">

                        Kanal Açıklaması

                    </label>

                    <textarea
                        name="channel_description"
                        rows="6"
                        class="w-full rounded-xl bg-gray-800 border border-gray-700 px-4 py-3">{{ old('channel_description', $user->channel_description) }}</textarea>

                </div>

                {{-- Profil --}}
                <div>

                    <label class="block mb-2 font-semibold">

                        Profil Fotoğrafı

                    </label>

                    <input
                        type="file"
                        name="avatar"
                        class="block w-full text-sm">

                </div>

                {{-- Banner --}}
                <div>

                    <label class="block mb-2 font-semibold">

                        Banner

                    </label>

                    <input
                        type="file"
                        name="banner"
                        class="block w-full text-sm">

                </div>

                <button
                    class="rounded-xl bg-red-600 px-8 py-3 font-semibold hover:bg-red-700">

                    💾 Kaydet

                </button>

            </form>

        </div>

    </div>

    {{-- Önizleme --}}
    <div>

        <div class="rounded-2xl border border-gray-800 bg-gray-900 overflow-hidden">

            <div class="h-36 bg-gradient-to-r from-red-600 to-red-900">

                @if($user->banner)

                    <img
                        src="{{ asset('storage/'.$user->banner) }}"
                        class="h-full w-full object-cover">

                @endif

            </div>

            <div class="p-6">

                <div class="-mt-16 mb-4">

                    @if($user->avatar)

                        <img
                            src="{{ asset('storage/'.$user->avatar) }}"
                            class="h-24 w-24 rounded-full border-4 border-gray-900 object-cover">

                    @else

                        <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-gray-900 bg-red-600 text-3xl font-bold">

                            {{ strtoupper(substr($user->name,0,1)) }}

                        </div>

                    @endif

                </div>

                <h2 class="text-2xl font-bold">

                    {{ $user->channel_name ?: $user->name }}

                </h2>

                <p class="mt-4 text-sm text-gray-400">

                    {{ $user->channel_description ?: 'Henüz kanal açıklaması eklenmedi.' }}

                </p>

            </div>

        </div>

    </div>

</div>

@endsection