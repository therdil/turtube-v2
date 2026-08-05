@extends('layouts.turtube')

@section('title', 'Kategori Yönetimi')

@section('content')
<div class="mx-auto max-w-5xl space-y-8">
    <div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Yönetim</p><h1 class="mt-2 text-4xl font-bold text-white">Kategoriler</h1><p class="mt-3 text-gray-400">Keşfet ve içerik filtrelerinde kullanılan kategorileri yönetin.</p></div>

    <x-ui.card class="p-6">
        <h2 class="text-lg font-bold text-white">Yeni kategori</h2>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
            @csrf
            <input name="name" required maxlength="100" value="{{ old('name') }}" placeholder="Kategori adı" class="min-w-0 flex-1 rounded-xl border border-gray-700 bg-gray-950 px-4 py-3 text-white">
            <button class="rounded-xl bg-red-600 px-5 py-3 font-semibold text-white hover:bg-red-500">Ekle</button>
        </form>
        @error('name')<p class="mt-3 text-sm text-red-400">{{ $message }}</p>@enderror
    </x-ui.card>

    <div class="space-y-3">
        @forelse ($categories as $category)
            <x-ui.card class="p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                        @csrf
                        @method('PUT')
                        <input name="name" required maxlength="100" value="{{ $category->name }}" class="min-w-0 flex-1 rounded-lg border border-gray-700 bg-gray-950 px-3 py-2 text-white">
                        <span class="text-sm text-gray-500">/{{ $category->slug }}</span>
                        <span class="text-sm text-gray-400">{{ number_format($category->videos_count) }} video</span>
                        <button class="rounded-lg border border-gray-700 px-3 py-2 text-sm text-white hover:border-red-500">Kaydet</button>
                    </form>
                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Bu kategoriyi silmek istiyor musunuz?')">
                        @csrf
                        @method('DELETE')
                        <button @disabled($category->videos_count > 0) class="rounded-lg px-3 py-2 text-sm {{ $category->videos_count > 0 ? 'cursor-not-allowed text-gray-600' : 'text-red-300 hover:bg-red-500/10' }}">Sil</button>
                    </form>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card class="p-10 text-center text-gray-400">Henüz kategori yok.</x-ui.card>
        @endforelse
    </div>
</div>
@endsection
