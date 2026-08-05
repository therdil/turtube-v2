@extends('layouts.turtube')

@section('title', 'Videoyu Düzenle')

@section('content')

<div class="max-w-5xl mx-auto">

    <div class="overflow-hidden rounded-2xl border border-zinc-800 bg-zinc-900 shadow-xl">

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
            method="POST"
            enctype="multipart/form-data">

            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-8 p-8 lg:grid-cols-3">

                {{-- SOL TARAF --}}
                <div class="space-y-6 lg:col-span-2">

                    <div>

                        <label class="mb-2 block font-semibold text-white">

                            Başlık

                        </label>

                        <input
                            type="text"
                            name="title"
                            value="{{ old('title', $video->title) }}"
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white focus:border-red-500">

                        @error('title')

                            <p class="mt-2 text-sm text-red-500">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <div>

                        <label class="mb-2 block font-semibold text-white">

                            Açıklama

                        </label>

                        <textarea
                            name="description"
                            rows="10"
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white focus:border-red-500">{{ old('description',$video->description) }}</textarea>

                        @error('description')

                            <p class="mt-2 text-sm text-red-500">

                                {{ $message }}

                            </p>

                        @enderror

                    </div>

                    <div>

                        <label class="mb-2 block font-semibold text-white">

                            Kategori

                        </label>

                        <select
                            name="category_id"
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">

                            <option value="">Kategori Seçin</option>

                            @foreach($categories as $category)

                                <option
                                    value="{{ $category->id }}"
                                    @selected(old('category_id',$video->category_id)==$category->id)>

                                    {{ $category->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div>

                        <label class="mb-2 block font-semibold text-white">

                            Yayın Durumu

                        </label>

                        <select
                            name="status"
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">

                            <option value="public" @selected(old('status',$video->status)=='public')>

                                🌍 Herkese Açık

                            </option>

                            <option value="unlisted" @selected(old('status',$video->status)=='unlisted')>

                                🔗 Liste Dışı

                            </option>

                            <option value="private" @selected(old('status',$video->status)=='private')>

                                🔒 Gizli

                            </option>

                            <option value="draft" @selected(old('status',$video->status)=='draft')>

                                📝 Taslak

                            </option>

                        </select>

                    </div>

                    <div>

                        <label class="mb-2 block font-semibold text-white">

                            Lisans

                        </label>

                        <select
                            name="license"
                            class="w-full rounded-xl border border-zinc-700 bg-zinc-800 px-4 py-3 text-white">

                            <option value="standard" @selected(old('license', $video->license ?: 'standard') === 'standard')>Standart TurTube lisansı</option>

                            <option value="creative_commons" @selected(old('license', $video->license ?: 'standard') === 'creative_commons')>Creative Commons</option>

                        </select>

                    </div>

                </div>

                {{-- SAĞ TARAF --}}
                <div>

                    <div class="rounded-2xl border border-zinc-800 bg-zinc-950 p-5">

                        <h2 class="mb-5 text-lg font-semibold text-white">

                            Thumbnail

                        </h2>

                        @if($video->thumbnail)

                            <img
                                id="thumbnail-preview"
                                src="{{ $video->thumbnail_url }}"
                                class="aspect-video w-full rounded-xl object-cover">

                        @else

                            <div
                                id="thumbnail-preview"
                                class="flex aspect-video items-center justify-center rounded-xl bg-zinc-800 text-5xl">

                                🎬

                            </div>

                        @endif

                        <div class="mt-6">

                            <label class="mb-2 block font-semibold text-white">

                                Yeni Thumbnail

                            </label>

                            <input
                                type="file"
                                name="thumbnail"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                class="block w-full rounded-xl border border-zinc-700 bg-zinc-800 p-3 text-sm text-gray-300">

                            <p class="mt-2 text-xs text-zinc-500">

                                JPG, PNG veya WEBP (Maksimum 5 MB)

                            </p>

                            @error('thumbnail')

                                <p class="mt-2 text-sm text-red-500">

                                    {{ $message }}

                                </p>

                            @enderror

                        </div>

                        <div class="mt-6 border-t border-zinc-800 pt-6">

                            <p class="font-semibold text-white">

                                {{ $video->title }}

                            </p>

                            <p class="mt-2 text-sm text-zinc-400">

                                👁 {{ number_format($video->views) }} görüntülenme

                            </p>

                            <p class="mt-1 text-sm text-zinc-400">

                                📅 {{ $video->created_at->format('d.m.Y H:i') }}

                            </p>

                        </div>

                    </div>

                </div>

            </div>

            <div class="flex justify-between border-t border-zinc-800 px-8 py-6">

                <a
                    href="{{ route('videos.mine') }}"
                    class="rounded-xl bg-zinc-700 px-6 py-3 font-semibold text-white transition hover:bg-zinc-600">

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

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <section class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
            <div class="flex items-start justify-between gap-4"><div><h2 class="text-xl font-bold text-white">Bölümler</h2><p class="mt-2 text-sm text-zinc-400">İzleyicilerin videoda hızlıca gezinmesini sağlayın.</p></div><span class="rounded-full bg-zinc-800 px-3 py-1 text-sm text-zinc-300">{{ $video->chapters->count() }}</span></div>
            <form method="POST" action="{{ route('videos.chapters.store', $video) }}" class="mt-5 grid gap-3 sm:grid-cols-[1fr_110px_auto]">
                @csrf
                <input name="title" required maxlength="120" placeholder="Bölüm başlığı" class="rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2 text-white">
                <input name="start_seconds" required min="0" type="number" placeholder="Saniye" class="rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2 text-white">
                <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Ekle</button>
            </form>
            @error('start_seconds')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            <div class="mt-5 divide-y divide-zinc-800">
                @forelse ($video->chapters as $chapter)
                    <div class="flex items-center justify-between gap-3 py-3"><p class="min-w-0 truncate text-sm text-zinc-200"><span class="mr-3 font-mono text-red-400">{{ $chapter->formatted_start }}</span>{{ $chapter->title }}</p><form method="POST" action="{{ route('videos.chapters.destroy', [$video, $chapter]) }}">@csrf @method('DELETE')<button class="text-sm text-zinc-400 hover:text-red-400">Sil</button></form></div>
                @empty
                    <p class="py-4 text-sm text-zinc-500">Henüz bölüm eklenmedi.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
            <div class="flex items-start justify-between gap-4"><div><h2 class="text-xl font-bold text-white">Altyazılar</h2><p class="mt-2 text-sm text-zinc-400">WebVTT (.vtt) dosyaları video oynatıcısında kullanılabilir.</p></div><span class="rounded-full bg-zinc-800 px-3 py-1 text-sm text-zinc-300">{{ $video->captions->count() }}</span></div>
            <form method="POST" enctype="multipart/form-data" action="{{ route('videos.captions.store', $video) }}" class="mt-5 space-y-3">
                @csrf
                <div class="grid gap-3 sm:grid-cols-2"><input name="label" required maxlength="80" placeholder="Türkçe" class="rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2 text-white"><input name="language" required maxlength="10" placeholder="tr" class="rounded-xl border border-zinc-700 bg-zinc-800 px-3 py-2 text-white"></div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><input type="file" name="caption" accept=".vtt,text/vtt" required class="block min-w-0 text-sm text-zinc-300"><label class="flex items-center gap-2 text-sm text-zinc-400"><input name="is_default" value="1" type="checkbox" class="rounded border-zinc-600 bg-zinc-800 text-red-600"> Varsayılan</label><button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Yükle</button></div>
            </form>
            @error('caption')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
            <div class="mt-5 divide-y divide-zinc-800">
                @forelse ($video->captions as $caption)
                    <div class="flex items-center justify-between gap-3 py-3"><a href="{{ $caption->url }}" target="_blank" class="min-w-0 truncate text-sm text-zinc-200 hover:text-red-400">{{ $caption->label }} <span class="text-zinc-500">({{ $caption->language }})</span>@if ($caption->is_default)<span class="ml-2 text-xs text-amber-300">Varsayılan</span>@endif</a><form method="POST" action="{{ route('videos.captions.destroy', [$video, $caption]) }}">@csrf @method('DELETE')<button class="text-sm text-zinc-400 hover:text-red-400">Sil</button></form></div>
                @empty
                    <p class="py-4 text-sm text-zinc-500">Henüz altyazı eklenmedi.</p>
                @endforelse
            </div>
        </section>
    </div>

</div>

@endsection

@push('scripts')

<script>

const input=document.querySelector('input[name="thumbnail"]');

if(input){

    input.addEventListener('change',function(e){

        const file=e.target.files[0];

        if(!file) return;

        const reader=new FileReader();

        reader.onload=function(ev){

            const preview=document.getElementById('thumbnail-preview');

            preview.outerHTML=
            `<img
                id="thumbnail-preview"
                src="${ev.target.result}"
                class="aspect-video w-full rounded-xl object-cover">`;

        };

        reader.readAsDataURL(file);

    });

}

</script>

@endpush
