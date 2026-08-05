@props(['title', 'description' => null, 'href' => null, 'action' => 'Tümünü gör'])

<section {{ $attributes->class('space-y-5') }}>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold tracking-tight text-white">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-zinc-400">{{ $description }}</p>
            @endif
        </div>
        @if ($href)
            <a href="{{ $href }}" class="w-fit text-sm font-semibold text-red-400 transition hover:text-red-300">{{ $action }} →</a>
        @endif
    </div>

    {{ $slot }}
</section>
