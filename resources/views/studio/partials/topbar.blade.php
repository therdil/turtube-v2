<header class="flex h-16 items-center justify-between border-b border-gray-800 bg-[#161616] px-8">

    <div>

        <h2 class="text-xl font-bold">

            Creator Studio

        </h2>

    </div>

    <div class="flex items-center gap-6">

        <a
            href="{{ route('home') }}"
            class="flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white">

            <x-heroicon-o-home class="h-5 w-5"/>

            Ana Sayfa

        </a>

        <a href="{{ route('videos.create') }}"
           class="rounded-lg bg-red-600 px-5 py-2 font-semibold hover:bg-red-700">

            + Video Yükle

        </a>

    </div>

</header>