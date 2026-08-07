<header class="flex h-16 min-w-0 items-center justify-between gap-3 border-b border-gray-800 bg-white px-4 dark:bg-[#161616] sm:px-8">
    <div class="min-w-0">
        <h2 class="hidden truncate text-xl font-bold text-gray-900 dark:text-white sm:block">Creator Studio</h2>
        <h2 class="truncate text-base font-bold text-gray-900 dark:text-white sm:hidden">Studio</h2>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-6">
        <x-theme-selector />

        <a
            href="{{ route('home') }}"
            class="flex items-center gap-2 rounded-lg p-2 text-sm font-medium text-gray-300 transition hover:bg-gray-800 hover:text-white sm:px-4 sm:py-2"
            aria-label="Ana Sayfa">
            <x-heroicon-o-home class="h-5 w-5" />
            <span class="hidden sm:inline">Ana Sayfa</span>
        </a>

        <a
            href="{{ route('videos.create') }}"
            class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700 sm:px-5">
            <span class="sm:hidden">+ Yükle</span><span class="hidden sm:inline">+ Video Yükle</span>
        </a>
    </div>
</header>
