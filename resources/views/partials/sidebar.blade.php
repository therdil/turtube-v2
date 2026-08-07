<aside
    id="primary-sidebar"
    aria-label="Ana navigasyon"
    class="turtube-sidebar fixed inset-y-0 left-0 z-[60] h-[100dvh] w-64 shrink-0 -translate-x-full overflow-hidden border-r border-gray-800 bg-gray-950 shadow-2xl transition-[transform,width] duration-300 ease-out lg:sticky lg:top-16 lg:z-0 lg:h-[calc(100dvh-4rem)] lg:translate-x-0 lg:shadow-none">

    <div class="turtube-sidebar-scroll flex h-full min-h-0 flex-col overflow-y-auto overscroll-contain p-4 pb-[max(1rem,env(safe-area-inset-bottom))]">

        <div class="mb-5 flex items-center justify-between lg:hidden">
            <span class="text-sm font-semibold uppercase tracking-[0.18em] text-gray-500">Menü</span>
            <button type="button" data-sidebar-close aria-label="Menüyü kapat" class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-800 hover:text-white">
                <x-heroicon-o-x-mark class="h-5 w-5"/>
            </button>
        </div>

        {{-- Ana Menü --}}
        <nav class="space-y-2">

            <a
                href="{{ route('home') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                {{ request()->routeIs('home') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                <x-heroicon-o-home class="h-5 w-5"/>

                <span class="font-medium">
                    Ana Sayfa
                </span>

            </a>

            <a
                href="{{ route('trending') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                {{ request()->routeIs('trending') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                <x-heroicon-o-fire class="h-5 w-5"/>

                <span class="font-medium">
                    Trendler
                </span>

            </a>

            <a
                href="{{ route('channels.index') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                {{ request()->routeIs('channels.index') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                <x-heroicon-o-tv class="h-5 w-5"/>

                <span class="font-medium">
                    Kanallar
                </span>

            </a>

            <a
                href="{{ route('shorts.index') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                {{ request()->routeIs('shorts.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                <x-heroicon-o-bolt class="h-5 w-5"/>

                <span class="font-medium">Shorts</span>

            </a>

            <a
                href="{{ route('live.index') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                {{ request()->routeIs('live.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                <x-heroicon-o-signal class="h-5 w-5"/>

                <span class="font-medium">Canlı Yayın</span>

            </a>

        </nav>

        @auth

            <div class="my-6 border-t border-gray-800"></div>

            {{-- İçerik Yönetimi --}}
            <nav class="space-y-2">

                <a
                    href="{{ route('videos.mine') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('videos.mine') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                    <x-heroicon-o-film class="h-5 w-5"/>

                    <span class="font-medium">
                        Videolarım
                    </span>

                </a>

                <a
                    href="{{ route('videos.create') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('videos.create') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                    <x-heroicon-o-arrow-up-tray class="h-5 w-5"/>

                    <span class="font-medium">
                        Video Yükle
                    </span>

                </a>

            </nav>

        @endauth

        <div class="my-6 border-t border-gray-800"></div>

        {{-- Kişisel --}}
        <nav class="space-y-2">

            @auth

                <a
                    href="{{ route('watch-later.index') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('watch-later.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                    <x-heroicon-o-clock class="h-5 w-5"/>

                    <span class="font-medium">
                        Daha Sonra İzle
                    </span>

                </a>

                <a
                    href="{{ route('history.index') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('history.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                    <x-heroicon-o-clock class="h-5 w-5"/>

                    <span class="font-medium">
                        İzleme Geçmişi
                    </span>

                </a>

                <a
                    href="{{ route('playlists.index') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('playlists.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                    <x-heroicon-o-queue-list class="h-5 w-5"/>

                    <span class="font-medium">
                        Oynatma Listeleri
                    </span>

                </a>

                <a
                    href="{{ route('subscriptions.index') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('subscriptions.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                    <x-heroicon-o-users class="h-5 w-5"/>

                    <span class="font-medium">
                        Abonelikler
                    </span>

                </a>

                <a
                    href="{{ route('liked-videos.index') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('liked-videos.*') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                    <x-heroicon-o-heart class="h-5 w-5"/>

                    <span class="font-medium">
                        Beğenilenler
                    </span>

                </a>

                <a
                    href="{{ route('favorites.index') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('favorites.*') ? 'bg-amber-500 text-gray-950' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                    <x-heroicon-o-star class="h-5 w-5"/>

                    <span class="font-medium">
                        Favorilerim
                    </span>

                </a>

                <a
                    href="{{ route('premium.index') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('premium.*') ? 'bg-amber-500 text-gray-950' : 'text-amber-200 hover:bg-amber-400/10' }}">

                    <x-heroicon-o-sparkles class="h-5 w-5"/>

                    <span class="font-medium">Premium</span>

                </a>

                <a
                    href="{{ route('studio.dashboard') }}"
                    class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                    {{ request()->routeIs('studio.*')
                    ? 'bg-red-600 text-white'
                    : 'hover:bg-gray-800 text-gray-300' }}">

                    <x-heroicon-o-chart-bar class="h-5 w-5"/>

                    <span>Creator Studio</span>

                </a>

                @if (auth()->user()->is_admin)
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3 rounded-xl px-4 py-3 text-amber-200 transition hover:bg-amber-400/10">

                        <x-heroicon-o-shield-check class="h-5 w-5"/>

                        <span>Yönetim Paneli</span>

                    </a>
                @endif
            @endauth

        </nav>

        {{-- Alt Bilgi --}}
        <div class="turtube-sidebar-footer mt-auto rounded-xl border border-gray-800 bg-gray-900 p-4">

            <p class="text-sm font-semibold text-white">
                TurTube
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Created by <span class="font-medium text-red-500">thErdiL</span>
            </p>

        </div>

    </div>

</aside>
