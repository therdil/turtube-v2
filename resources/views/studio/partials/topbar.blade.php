<header class="flex h-16 items-center justify-between border-b border-gray-800 bg-[#161616] px-8">

    <div>

        <h2 class="text-xl font-bold">

            Creator Studio

        </h2>

    </div>

    <div class="flex items-center gap-6">

        <a href="{{ route('home') }}"
           class="text-sm text-gray-400 hover:text-white">

            Siteye Dön

        </a>

        <a href="{{ route('videos.create') }}"
           class="rounded-lg bg-red-600 px-5 py-2 font-semibold hover:bg-red-700">

            + Video Yükle

        </a>

    </div>

</header>