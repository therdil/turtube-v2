<aside class="w-64 shrink-0 border-r border-gray-800 bg-gray-950">

    <div class="flex h-full flex-col p-4">

        {{-- Ana Menü --}}
        <nav class="space-y-2">

            <a
                href="{{ url('/') }}"
                class="flex items-center gap-3 rounded-xl px-4 py-3 transition
                {{ request()->is('/') ? 'bg-red-600 text-white' : 'text-gray-300 hover:bg-gray-900 hover:text-white' }}">

                <x-heroicon-o-home class="h-5 w-5"/>

                <span class="font-medium">
                    Ana Sayfa
                </span>

            </a>

            <a
                href="#"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 transition hover:bg-gray-900 hover:text-white">

                <x-heroicon-o-fire class="h-5 w-5"/>

                <span class="font-medium">
                    Trendler
                </span>

            </a>

            <a
                href="#"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 transition hover:bg-gray-900 hover:text-white">

                <x-heroicon-o-tv class="h-5 w-5"/>

                <span class="font-medium">
                    Kanallar
                </span>

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

            <a
                href="#"
                class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 transition hover:bg-gray-900 hover:text-white">

                <x-heroicon-o-heart class="h-5 w-5"/>

                <span class="font-medium">
                    Beğenilenler
                </span>

            </a>

        </nav>

        {{-- Alt Bilgi --}}
        <div class="mt-auto rounded-xl border border-gray-800 bg-gray-900 p-4">

            <p class="text-sm font-semibold text-white">
                TurTube
            </p>

            <p class="mt-1 text-xs text-gray-500">
                Created by <span class="font-medium text-red-500">thErdiL</span>
            </p>

        </div>

    </div>

</aside>