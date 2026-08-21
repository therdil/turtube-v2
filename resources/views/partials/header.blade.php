<header class="turtube-header sticky top-0 z-50 border-b border-gray-800 bg-gray-950/95 backdrop-blur">

    <div class="flex h-16 items-center justify-between gap-2 px-3 sm:px-6">

        {{-- Sol --}}
        <div class="flex min-w-0 items-center gap-2 sm:gap-4">

            <button
                type="button"
                data-sidebar-toggle
                aria-expanded="false"
                aria-label="Menüyü aç"
                aria-controls="primary-sidebar"
                class="turtube-control turtube-focus shrink-0 p-2 text-gray-400 hover:bg-gray-800 hover:text-white">

                <x-heroicon-o-bars-3 class="h-6 w-6"/>

            </button>

            <a
                href="{{ route('home') }}"
                class="flex shrink-0 items-center gap-2">

                @if (filled($siteSettings->logo ?? null))
                    <img src="{{ asset('storage/'.$siteSettings->logo) }}" alt="{{ $siteSettings->site_name }}" class="h-9 w-9 rounded-xl object-contain sm:h-10 sm:w-10">
                @else
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-600 sm:h-10 sm:w-10">
                        <x-heroicon-o-play class="h-6 w-6 text-white"/>
                    </div>
                @endif

                <div class="hidden sm:block">

                    <div class="text-xl font-bold tracking-tight">
                        {{ $siteSettings->site_name ?? 'TurTube' }}
                    </div>

                    <div class="-mt-1 text-xs text-gray-500">
                        Video Platformu
                    </div>

                </div>

            </a>

        </div>

        {{-- Arama --}}
        <div class="mx-4 hidden max-w-2xl flex-1 md:block lg:mx-8">

            <form
                action="{{ route('search') }}"
                method="GET"
                class="relative"
                data-live-search-form>

                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Video ara..."
                    autocomplete="off"
                    data-live-search-input
                    data-suggestions-url="{{ route('search.suggestions') }}"
                    aria-autocomplete="list"
                    aria-expanded="false"
                    class="w-full rounded-xl border border-gray-800 bg-gray-900 py-3 pl-12 pr-14 text-sm text-white placeholder:text-gray-500 focus:border-red-500 focus:outline-none">

                <x-heroicon-o-magnifying-glass
                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-500"/>

                <button
                    type="submit"
                    class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium transition hover:bg-red-700">

                    Ara

                </button>

                <div data-live-search-results class="absolute inset-x-0 top-[calc(100%+0.5rem)] z-50 hidden overflow-hidden rounded-2xl border border-zinc-700 bg-zinc-950 shadow-2xl" role="listbox"></div>

            </form>

        </div>

        {{-- Sağ --}}
        <div class="flex shrink-0 items-center gap-1.5 sm:gap-3">

            <div class="hidden sm:block">
                <x-theme-selector />
            </div>

            <button
                type="button"
                @click="searchOpen = true"
                aria-label="Aramayı aç"
                class="rounded-xl p-2 text-gray-300 transition hover:bg-gray-800 md:hidden">
                <x-heroicon-o-magnifying-glass class="h-6 w-6"/>
            </button>

            @guest

                <a
                    href="{{ route('login') }}"
                    class="rounded-xl border border-gray-700 px-3 py-2 text-sm transition hover:bg-gray-800 sm:px-4">

                    Giriş

                </a>

                <a
                    href="{{ route('register') }}"
                    class="rounded-xl bg-red-600 px-3 py-2 text-sm font-medium transition hover:bg-red-700 sm:px-4">

                    Kayıt<span class="hidden sm:inline"> Ol</span>

                </a>

            @endguest

            @auth

                <a
                    href="{{ route('videos.create') }}"
                    class="flex items-center gap-2 rounded-xl border border-gray-700 px-2 py-2 text-sm transition hover:bg-gray-800 sm:px-4">

                    <x-heroicon-o-plus class="h-5 w-5"/>

                    <span class="hidden sm:inline">Yükle</span>

                </a>

                @php($unreadNotifications = auth()->user()->unreadNotifications()->count())
                <a
                    href="{{ route('notifications.index') }}"
                    aria-label="Bildirimler"
                    class="relative rounded-xl p-2 transition hover:bg-gray-800">

                    <x-heroicon-o-bell class="h-6 w-6"/>

                    @if ($unreadNotifications)
                        <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white">{{ min($unreadNotifications, 9) }}{{ $unreadNotifications > 9 ? '+' : '' }}</span>
                    @endif

                </a>

                <div
                    x-data="{ open:false }"
                    class="relative">

                    <button
                        @click="open=!open"
                        class="flex items-center gap-3 rounded-xl border border-gray-700 bg-gray-900 px-3 py-2 transition hover:border-gray-600">

                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-red-600 font-semibold">

                            {{ strtoupper(substr(auth()->user()->name,0,1)) }}

                        </div>

                        <span class="hidden md:block">

                            {{ auth()->user()->name }}

                        </span>

                        <x-heroicon-o-chevron-down class="h-4 w-4"/>

                    </button>

                    <div
                        x-show="open"
                        @click.outside="open=false"
                        x-transition
                        class="absolute right-0 mt-3 w-64 overflow-hidden rounded-2xl border border-gray-700 bg-gray-900 shadow-2xl">

                        <a
                            href="{{ route('studio.dashboard') }}"
                            class="flex items-center gap-3 px-5 py-3 transition hover:bg-gray-800">

                            <x-heroicon-o-chart-bar class="h-5 w-5"/>

                            Creator Studio

                        </a>
                        
                        <a
                            href="{{ route('videos.mine') }}"
                            class="flex items-center gap-3 px-5 py-3 transition hover:bg-gray-800">

                            <x-heroicon-o-film class="h-5 w-5"/>

                            Videolarım

                        </a>

                        <a
                            href="{{ route('videos.create') }}"
                            class="flex items-center gap-3 px-5 py-3 transition hover:bg-gray-800">

                            <x-heroicon-o-arrow-up-tray class="h-5 w-5"/>

                            Video Yükle

                        </a>

                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex items-center gap-3 px-5 py-3 transition hover:bg-gray-800">

                            <x-heroicon-o-user-circle class="h-5 w-5"/>

                            Profil Ayarları

                        </a>

                        <div class="border-t border-gray-800"></div>

                        <form
                            method="POST"
                            action="{{ route('logout') }}">

                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 px-5 py-3 text-left transition hover:bg-red-600">

                                <x-heroicon-o-arrow-left-on-rectangle class="h-5 w-5"/>

                                Çıkış Yap

                            </button>

                        </form>

                    </div>

                </div>

            @endauth

        </div>

    </div>

</header>
