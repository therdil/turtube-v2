<aside class="w-64 bg-gray-900 min-h-screen p-6 border-r border-gray-800">

    <ul class="space-y-4">

        <li>
            <a href="{{ url('/') }}"
               class="block hover:text-red-500 transition">
                🏠 Ana Sayfa
            </a>
        </li>

        @auth

            <li>
                <a href="{{ route('videos.create') }}"
                   class="block hover:text-red-500 transition">
                    ⬆️ Video Yükle
                </a>
            </li>

            <li>
                <a href="{{ route('videos.mine') }}"
                   class="block hover:text-red-500 transition">
                    🎥 Videolarım
                </a>
            </li>

        @endauth

        <li>
            <a href="#"
               class="block hover:text-red-500 transition">
                🔥 Trendler
            </a>
        </li>

        <li>
            <a href="#"
               class="block hover:text-red-500 transition">
                📺 Kanallar
            </a>
        </li>

        <li>
            <a href="#"
               class="block hover:text-red-500 transition">
                ❤️ Beğenilenler
            </a>
        </li>

    </ul>

</aside>