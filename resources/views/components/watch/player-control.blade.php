<button
    type="button"
    {{ $attributes->class('inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-gray-800 bg-gray-950 px-4 text-sm font-semibold text-zinc-200 transition-all duration-200 hover:-translate-y-0.5 hover:border-red-500 hover:bg-gray-900 hover:shadow-lg hover:shadow-black/20 focus:outline-none focus:ring-2 focus:ring-red-500/40 data-[active=true]:border-red-500 data-[active=true]:bg-red-600 data-[active=true]:text-white data-[active=true]:shadow-lg data-[active=true]:shadow-red-950/30') }}
>
    {{ $slot }}
</button>
