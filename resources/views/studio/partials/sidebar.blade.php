<aside class="w-64 bg-[#161616] border-r border-gray-800 flex flex-col">

    <div class="p-6 border-b border-gray-800">

        <h1 class="text-2xl font-bold">
            <span class="text-red-600">Tur</span>Tube
        </h1>

        <p class="mt-1 text-xs text-gray-500">
            Creator Studio
        </p>

    </div>

    <nav class="flex-1 px-3 py-4 space-y-2">

        <a
            href="{{ route('studio.dashboard') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 transition
            {{ request()->routeIs('studio.dashboard') ? 'bg-red-600 text-white' : 'hover:bg-gray-800 text-gray-300' }}">

            📊 <span>Dashboard</span>

        </a>

        <a
            href="{{ route('studio.videos.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 transition
            {{ request()->routeIs('studio.videos.*') ? 'bg-red-600 text-white' : 'hover:bg-gray-800 text-gray-300' }}">

            🎥 <span>İçerikler</span>

        </a>

        <a
            href="{{ route('studio.analytics.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 transition
            {{ request()->routeIs('studio.analytics.*') ? 'bg-red-600 text-white' : 'hover:bg-gray-800 text-gray-300' }}">

            📈 <span>Analytics</span>

        </a>

        <a
            href="{{ route('studio.comments.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 transition
            {{ request()->routeIs('studio.comments.*') ? 'bg-red-600 text-white' : 'hover:bg-gray-800 text-gray-300' }}">

            💬 <span>Yorumlar</span>

        </a>

        <a
            href="{{ route('studio.channel.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 transition
            {{ request()->routeIs('studio.channel.*')
            ? 'bg-red-600 text-white'
            : 'hover:bg-gray-800 text-gray-300' }}">

            📺 <span>Kanal</span>

        </a>

        <a
            href="{{ route('subscriptions.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 transition hover:bg-gray-800">

            👥 <span>Aboneler</span>

        </a>

        <a
            href="{{ route('playlists.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 transition hover:bg-gray-800">

            📂 <span>Oynatma Listeleri</span>

        </a>

        <a
            href="{{ route('studio.channel.index') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 transition hover:bg-gray-800">

            ⚙️ <span>Ayarlar</span>

        </a>

    </nav>

    <div class="border-t border-gray-800 p-4">

        <a
            href="{{ route('home') }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-gray-300 hover:bg-gray-800 transition">

            🏠 <span>Ana Sayfa</span>

        </a>

    </div>

</aside>
