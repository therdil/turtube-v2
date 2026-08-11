@extends('layouts.turtube')

@section('title', 'Video Yükle')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="bg-gray-900 rounded-2xl shadow-xl border border-gray-800 overflow-hidden">

        <div class="border-b border-gray-800 px-8 py-6">

            <h1 class="text-3xl font-bold">
                📤 Video Yükle
            </h1>

            <p class="text-gray-400 mt-2">
                Videonu yükle ve TurTube topluluğuyla paylaş.
            </p>

        </div>

        <div class="p-8">

            @if ($errors->any())

                <div class="bg-red-600/20 border border-red-500 text-red-300 rounded-xl p-4 mb-8">

                    <ul class="space-y-1">

                        @foreach ($errors->all() as $error)

                            <li>• {{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <form
                method="POST"
                action="{{ route('videos.store') }}"
                enctype="multipart/form-data"
                data-upload-form>

                @csrf

                <div class="space-y-7">

                    {{-- Başlık --}}
                    <div>

                        <label class="block mb-2 font-semibold">
                            Başlık
                        </label>

                        <input
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            class="w-full rounded-xl bg-gray-800 border border-gray-700 focus:border-red-500 focus:ring-red-500 text-white"
                            required>

                    </div>

                    {{-- Açıklama --}}
                    <div>

                        <label class="block mb-2 font-semibold">
                            Açıklama
                        </label>

                        <textarea
                            rows="6"
                            name="description"
                            class="w-full rounded-xl bg-gray-800 border border-gray-700 focus:border-red-500 focus:ring-red-500 text-white">{{ old('description', $uploadDefaults['description']) }}</textarea>

                    </div>

                    {{-- Kategori --}}
                    <div>

                        <label class="block mb-2 font-semibold">
                            Kategori
                        </label>

                        <select
                            name="category_id"
                            required
                            class="w-full rounded-xl bg-gray-800 border border-gray-700 focus:border-red-500 focus:ring-red-500 text-white">

                            <option value="">
                                Kategori Seçin
                            </option>

                            @foreach($categories as $category)

                                <option
                                    value="{{ $category->id }}"
                                    @selected(old('category_id') == $category->id)>

                                    {{ $category->name }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    {{-- Yayın Durumu --}}
                    <div>

                        <label class="block mb-2 font-semibold">
                            Yayın Durumu
                        </label>

                        <select
                            name="status"
                            class="w-full rounded-xl bg-gray-800 border border-gray-700 focus:border-red-500 focus:ring-red-500 text-white">

                            <option value="public" @selected(old('status', $uploadDefaults['status']) == 'public')>
                                🌍 Herkese Açık
                            </option>

                            <option value="unlisted" @selected(old('status', $uploadDefaults['status']) == 'unlisted')>
                                🔗 Liste Dışı
                            </option>

                            <option value="private" @selected(old('status', $uploadDefaults['status']) == 'private')>
                                🔒 Gizli
                            </option>

                            <option value="draft" @selected(old('status', $uploadDefaults['status']) == 'draft')>
                                📝 Taslak
                            </option>

                        </select>

                    </div>

                    <div>
                        <label class="block mb-2 font-semibold">Lisans</label>
                        <select name="license" class="w-full rounded-xl bg-gray-800 border border-gray-700 focus:border-red-500 focus:ring-red-500 text-white">
                            <option value="standard" @selected(old('license', $uploadDefaults['license']) === 'standard')>Standart TurTube lisansı</option>
                            <option value="creative_commons" @selected(old('license', $uploadDefaults['license']) === 'creative_commons')>Creative Commons</option>
                        </select>
                        <p class="mt-2 text-sm text-gray-400">Kanal ayarlarından varsayılan lisansı değiştirebilirsiniz.</p>
                    </div>

                    {{-- Video --}}
                    <div>

                        <label class="block mb-3 font-semibold">
                            Video Dosyası
                        </label>

                        <div data-upload-dropzone tabindex="0" class="group relative rounded-2xl border-2 border-dashed border-gray-700 bg-gray-950/50 p-7 text-center transition hover:border-red-500 hover:bg-red-500/5 focus:border-red-500 focus:outline-none">
                            <input id="video-upload-input" type="file" name="video" accept="video/mp4" class="sr-only" required data-upload-input>
                            <x-heroicon-o-arrow-up-tray class="mx-auto h-9 w-9 text-zinc-500 transition group-hover:text-red-400" />
                            <label for="video-upload-input" class="mt-3 block cursor-pointer font-semibold text-white">Videoyu seç veya buraya sürükle</label>
                            <p class="mt-1 text-sm text-gray-400">MP4 · En fazla {{ number_format(config('video.max_upload_kb') / 1024, 0) }} MB</p>
                            <p data-upload-file class="mt-3 hidden text-sm font-medium text-emerald-300"></p>
                        </div>

                        <div data-upload-progress class="mt-4 hidden rounded-xl border border-gray-700 bg-gray-950 p-4" aria-live="polite">
                            <div class="flex items-center justify-between gap-4 text-sm"><span data-upload-status class="font-medium text-zinc-200">Yükleme hazırlanıyor</span><span data-upload-percent class="font-semibold text-white">0%</span></div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-800"><div data-upload-progress-bar class="h-full w-0 rounded-full bg-gradient-to-r from-red-600 to-red-400 transition-[width] duration-150"></div></div>
                            <p class="mt-2 text-xs text-gray-500">Yükleme tamamlandıktan sonra kalite ve önizleme işlemleri arka planda devam eder.</p>
                        </div>

                    </div>

                    <div>
                            <label for="video-tag-input" class="mb-3 block font-semibold">Etiketler</label>
                            <div data-tag-container data-initial-tags='@json(old('tags', []))' class="min-h-28 rounded-2xl border border-gray-700 bg-gray-950/50 p-3 focus-within:border-red-500">
                                <div data-tag-list class="flex flex-wrap gap-2"></div>
                                <input id="video-tag-input" data-tag-input type="text" maxlength="50" placeholder="Etiket yaz, Enter'a bas" class="mt-2 w-full border-0 bg-transparent px-1 py-2 text-sm text-white placeholder:text-gray-500 focus:ring-0">
                            </div>
                            <p class="mt-2 text-xs text-gray-500">En fazla 12 etiket. Arama ve keşfette kullanılabilir.</p>
                    </div>

                    <div class="space-y-3 rounded-xl border border-gray-700 bg-gray-800/60 p-4">
                        <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-200">
                            <input type="checkbox" name="is_short" value="1" @checked(old('is_short')) class="rounded border-gray-600 bg-gray-900 text-red-600 focus:ring-red-500">
                            <span><strong>Shorts olarak yayınla</strong><br><span class="text-gray-400">Dikey ve kısa videolar Shorts akışında öne çıkar.</span></span>
                        </label>

                        <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-200">
                            <input type="checkbox" name="is_premium" value="1" @checked(old('is_premium')) class="rounded border-gray-600 bg-gray-900 text-red-600 focus:ring-red-500">
                            <span><strong>Premium içerik</strong><br><span class="text-gray-400">Yalnızca Premium üyeler izleyebilir.</span></span>
                        </label>
                    </div>

                    <div class="pt-3">

                        <button
                            type="submit"
                            class="bg-red-600 hover:bg-red-700 transition px-8 py-3 rounded-xl font-semibold">

                            🚀 Videoyu Yükle

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
