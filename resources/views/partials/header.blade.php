<header class="h-16 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-6">

    <!-- Logo -->
    <a href="{{ url('/') }}" class="text-2xl font-bold text-red-500 hover:text-red-400 transition">
        🎬 TurTube
    </a>

    <!-- Arama -->
    <input
        type="text"
        placeholder="Video ara..."
        class="w-96 rounded-lg bg-gray-800 border border-gray-700 px-4 py-2 focus:outline-none focus:border-red-500">

    <!-- Kullanıcı -->
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

            <a href="{{ route('videos.mine') }}"
               class="px-4 py-2 bg-gray-700 rounded-lg hover:bg-gray-600 transition">
                👤 {{ auth()->user()->name }}
            </a>

        @endauth

    </div>

</header>