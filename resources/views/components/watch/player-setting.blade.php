@props(['label', 'icon'])

<label class="group flex h-10 items-center gap-2 rounded-xl border border-gray-800 bg-gray-950 px-3 text-sm text-zinc-400 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-500 hover:bg-gray-900 hover:shadow-lg hover:shadow-black/20">
    <x-dynamic-component :component="$icon" class="h-4 w-4 shrink-0 text-zinc-500 transition-colors group-hover:text-red-400" />
    <span class="hidden font-medium sm:inline">{{ $label }}</span>
    <div class="relative min-w-0">
        {{ $slot }}
    </div>
</label>
