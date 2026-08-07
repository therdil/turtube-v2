<div class="relative" data-theme-menu>
    <button
        type="button"
        data-theme-menu-toggle
        aria-expanded="false"
        aria-controls="theme-menu-options"
        aria-haspopup="menu"
        aria-label="Tema tercihini değiştir"
        class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-700 bg-gray-900 text-gray-300 transition hover:border-gray-600 hover:bg-gray-800 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
        <svg data-theme-icon="dark" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5 8.5 8.5 0 1 0 20.5 15.5Z" /></svg>
        <svg data-theme-icon="light" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4" /><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" /></svg>
        <svg data-theme-icon="system" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="2" /><path d="M8 20h8M12 16v4" /></svg>
    </button>

    <div
        id="theme-menu-options"
        data-theme-menu-panel
        hidden
        class="absolute right-0 z-[70] mt-3 w-52 overflow-hidden rounded-2xl border border-gray-700 bg-gray-900 p-2 shadow-2xl shadow-black/30"
        role="menu"
        aria-label="Tema seçenekleri">
        @foreach ([
            ['dark', 'Koyu tema', 'Ay ışığı'],
            ['light', 'Açık tema', 'Yüksek görünürlük'],
            ['system', 'Sistem teması', 'Cihaz tercihini kullan'],
        ] as [$value, $label, $description])
            <button
                type="button"
                data-theme-option="{{ $value }}"
                aria-checked="false"
                class="flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-left transition hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
                role="menuitemradio">
                <span><span class="block text-sm font-semibold text-white">{{ $label }}</span><span class="mt-0.5 block text-xs text-gray-500">{{ $description }}</span></span>
                <svg class="h-4 w-4 shrink-0 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
            </button>
        @endforeach
    </div>
</div>
