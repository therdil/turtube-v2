<!DOCTYPE html>
<html lang="tr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

    @php
        $defaultRobots = request()->routeIs(
            'videos.create',
            'videos.mine',
            'videos.edit',
            'favorites.*',
            'liked-videos.*',
            'watch-later.*',
            'subscriptions.*',
            'notifications.*',
            'history.*',
            'playlists.index',
            'playlists.create',
            'profile.*',
            'live.create',
            'studio.*',
            'admin.*',
        ) ? 'noindex,follow' : 'index,follow';
        $defaultTitle = 'TurTube';
        $defaultDescription = 'TurTube ile videoları keşfet, paylaş ve sevdiğin kanalları takip et.';
        $metaTitle = trim($__env->yieldContent('title', $defaultTitle));
        $metaDescription = trim($__env->yieldContent('meta_description', $defaultDescription));
        $metaKeywords = trim($__env->yieldContent('meta_keywords'));
        $metaRobots = trim($__env->yieldContent('meta_robots', $defaultRobots));
        $metaOgType = trim($__env->yieldContent('og_type', 'website'));
        $metaOgTitle = trim($__env->yieldContent('og_title', $metaTitle));
        $metaOgDescription = trim($__env->yieldContent('og_description', $metaDescription));
        $metaImage = trim($__env->yieldContent('og_image'));
        $metaTwitterTitle = trim($__env->yieldContent('twitter_title', $metaOgTitle));
        $metaTwitterDescription = trim($__env->yieldContent('twitter_description', $metaOgDescription));
        $metaTwitterImage = trim($__env->yieldContent('twitter_image', $metaImage));
        $isIndexablePage = ! \Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($metaRobots), 'noindex');
    @endphp

    <title>{{ $metaTitle }}</title>

    <x-brand.favicon />

    <meta name="description" content="{{ $metaDescription }}">
    @if ($metaKeywords !== '')
        <meta name="keywords" content="{{ $metaKeywords }}">
    @endif
    <meta name="robots" content="{{ $metaRobots }}">
    <link rel="canonical" href="{{ url()->current() }}">
    @if ($isIndexablePage)
        <meta property="og:site_name" content="TurTube">
        <meta property="og:type" content="{{ $metaOgType }}">
        <meta property="og:title" content="{{ $metaOgTitle }}">
        <meta property="og:description" content="{{ $metaOgDescription }}">
        <meta property="og:url" content="{{ url()->current() }}">
        @if ($metaImage !== '')
            <meta property="og:image" content="{{ $metaImage }}">
        @endif
        <meta name="twitter:card" content="{{ $metaTwitterImage !== '' ? 'summary_large_image' : 'summary' }}">
        <meta name="twitter:title" content="{{ $metaTwitterTitle }}">
        <meta name="twitter:description" content="{{ $metaTwitterDescription }}">
        @if ($metaTwitterImage !== '')
            <meta name="twitter:image" content="{{ $metaTwitterImage }}">
        @endif
    @endif

    @stack('head')

    <x-theme-preload />

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body
    class="min-h-screen overflow-x-hidden bg-gray-950 text-gray-100 antialiased"
    data-theme-endpoint="{{ auth()->check() && \Illuminate\Support\Facades\Route::has('profile.theme') ? url('/profile/theme') : '' }}"
    x-data="turtubeShell()"
    @keydown.escape.window="searchOpen = false">

    <div class="flex min-h-screen flex-col">

        {{-- Header --}}
        @include('partials.header')

        @if (($siteSettings->announcement_enabled ?? false) && filled($siteSettings->announcement ?? null))
            <div class="border-b border-amber-500/30 bg-amber-500/10 px-6 py-3 text-center text-sm text-amber-100">{{ $siteSettings->announcement }}</div>
        @endif

        @if (filled($siteSettings->banner ?? null))
            <img src="{{ asset('storage/'.$siteSettings->banner) }}" alt="{{ $siteSettings->site_name }} duyuru bannerı" class="max-h-56 w-full object-cover">
        @endif

        <div
            data-sidebar-overlay
            class="fixed inset-0 z-40 hidden bg-black/70 lg:hidden"></div>

        <div
            x-cloak
            x-show="searchOpen"
            x-transition.opacity
            class="fixed inset-x-0 top-16 z-40 border-b border-gray-800 bg-gray-950 p-4 shadow-2xl md:hidden">
            <form action="{{ route('search') }}" method="GET" class="relative">
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Video ara..."
                    autofocus
                    class="w-full rounded-xl border border-gray-700 bg-gray-900 py-3 pl-4 pr-20 text-white placeholder:text-gray-500 focus:border-red-500 focus:outline-none">
                <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white">Ara</button>
            </form>
        </div>

        <div class="flex min-h-0 flex-1">

            {{-- Sidebar --}}
            @include('partials.sidebar')

            {{-- İçerik --}}
            <main
                class="turtube-page-content min-w-0 flex-1 p-5 sm:p-6 lg:p-8">

                @include('partials.flash')

                @include('partials.categories')

                @yield('content')

            </main>

        </div>

    </div>

    {{-- Sayfaya özel JavaScript --}}
    @stack('scripts')

</body>

</html>
