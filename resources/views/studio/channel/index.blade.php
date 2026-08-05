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

                <div class="rounded-2xl border border-gray-800 bg-gray-950/60 p-5">
                    <h2 class="text-lg font-bold text-white">Sosyal bağlantılar</h2>
                    <p class="mt-1 text-sm text-gray-400">Dolu alanlar kanalındaki Hakkında sekmesinde görünür.</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach (['website' => 'Web sitesi', 'instagram' => 'Instagram', 'x' => 'X / Twitter', 'facebook' => 'Facebook', 'youtube' => 'YouTube'] as $key => $label)
                            <label class="block"><span class="mb-2 block text-sm font-medium text-gray-300">{{ $label }}</span><input type="url" name="{{ $key }}" value="{{ old($key, data_get($user->social_links, $key)) }}" placeholder="https://..." class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white focus:border-red-500 focus:outline-none"></label>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-800 bg-gray-950/60 p-5">
                    <div>
                        <h2 class="text-lg font-bold text-white">Kanal keşfi ve varsayılan yükleme ayarları</h2>
                        <p class="mt-1 text-sm text-gray-400">Etiketler kanalının keşfedilmesine yardımcı olur. Varsayılanlar, yeni video yükleme ekranını otomatik doldurur.</p>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="mb-2 block text-sm font-medium text-gray-300">Kanal etiketleri</span>
                            <input type="text" name="channel_tags_text" value="{{ old('channel_tags_text', implode(', ', $user->channel_tags ?? [])) }}" maxlength="500" placeholder="oyun, teknoloji, eğitim" class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white focus:border-red-500 focus:outline-none">
                            <span class="mt-2 block text-xs text-gray-500">Virgülle ayırarak en fazla 15 etiket ekleyin.</span>
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="mb-2 block text-sm font-medium text-gray-300">SEO anahtar kelimeleri</span>
                            <input type="text" name="seo_keywords_text" value="{{ old('seo_keywords_text', implode(', ', $user->seo_keywords ?? [])) }}" maxlength="1000" placeholder="Türkçe video, oyun videoları, teknoloji inceleme" class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white focus:border-red-500 focus:outline-none">
                            <span class="mt-2 block text-xs text-gray-500">Kanal sayfasının meta anahtar kelimelerinde kullanılır; en fazla 30 ifade ekleyin.</span>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-300">Kanal dili</span>
                            <select name="channel_language" class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white focus:border-red-500 focus:outline-none">
                                @foreach (['tr' => 'Türkçe', 'en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français', 'es' => 'Español', 'ar' => 'العربية'] as $code => $language)
                                    <option value="{{ $code }}" @selected(old('channel_language', $user->channel_language ?: 'tr') === $code)>{{ $language }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-300">Varsayılan görünürlük</span>
                            <select name="default_video_status" class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white focus:border-red-500 focus:outline-none">
                                @foreach (['public' => 'Herkese açık', 'unlisted' => 'Liste dışı', 'private' => 'Gizli', 'draft' => 'Taslak'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('default_video_status', $user->default_video_status ?: 'public') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="mb-2 block text-sm font-medium text-gray-300">Varsayılan video açıklaması</span>
                            <textarea name="default_video_description" rows="5" maxlength="5000" placeholder="Her yeni videoya eklenecek açıklama metni..." class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white focus:border-red-500 focus:outline-none">{{ old('default_video_description', $user->default_video_description) }}</textarea>
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-gray-300">Varsayılan lisans</span>
                            <select name="default_video_license" class="w-full rounded-xl border border-gray-700 bg-gray-800 px-4 py-3 text-white focus:border-red-500 focus:outline-none">
                                <option value="standard" @selected(old('default_video_license', $user->default_video_license ?: 'standard') === 'standard')>Standart TurTube lisansı</option>
                                <option value="creative_commons" @selected(old('default_video_license', $user->default_video_license ?: 'standard') === 'creative_commons')>Creative Commons</option>
                            </select>
                            <span class="mt-2 block text-xs text-gray-500">Bu seçim, her yeni video için başlangıç lisansı olur.</span>
                        </label>
                    </div>
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
