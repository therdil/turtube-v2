<header class="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6">

    <!-- Logo -->
    <a href="{{ url('/') }}"
       class="text-2xl font-bold text-red-500 hover:text-red-400 transition">
        🎬 TurTube
    </a>

    <!-- Arama -->
    <input
        type="text"
        placeholder="Video ara..."
        class="w-96 rounded-lg bg-gray-800 border border-gray-700 px-4 py-2 focus:outline-none focus:border-red-500">

    <!-- Sağ Menü -->
    <div class="flex items-center space-x-3">

        @guest

            <a href="{{ route('login') }}"
               class="px-4 py-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition">
                Giriş
            </a>

            <a href="{{ route('register') }}"
               class="px-4 py-2 bg-red-600 rounded-lg hover:bg-red-700 transition">
                Kayıt Ol
            </a>

        @endguest


        @auth

        <div
            x-data="{ open: false }"
            class="relative">

            <button
                @click="open = !open"
                class="flex items-center gap-2 bg-gray-700 hover:bg-gray-600 px-4 py-2 rounded-lg transition">

                👤 {{ auth()->user()->name }}

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24">

                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M19 9l-7 7-7-7"/>

                </svg>

            </button>


            <!-- Dropdown -->

            <div
                x-show="open"
                @click.outside="open = false"
                x-transition
                class="absolute right-0 mt-3 w-64 bg-gray-800 rounded-xl shadow-xl border border-gray-700 overflow-hidden z-50">

                <a
                    href="{{ route('videos.mine') }}"
                    class="block px-5 py-3 hover:bg-gray-700 transition">

                    🎥 Videolarım

                </a>

                <a
                    href="{{ route('videos.create') }}"
                    class="block px-5 py-3 hover:bg-gray-700 transition">

                    ⬆️ Video Yükle

                </a>

                <hr class="border-gray-700">

                <form
                    method="POST"
                    action="{{ route('logout') }}">

                    @csrf

                    <button
                        type="submit"
                        class="w-full text-left px-5 py-3 hover:bg-red-600 transition">

                        🚪 Çıkış Yap

                    </button>

                </form>

            </div>

        </div>

        @endauth

    </div>

</header>