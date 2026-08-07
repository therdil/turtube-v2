@props(['title', 'description' => null, 'href' => null, 'action' => 'Tümünü gör'])

<section {{ $attributes->class('space-y-4 sm:space-y-5') }}>
    <div class="flex flex-col gap-2.5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h2 class="text-xl font-bold tracking-tight text-white sm:text-2xl">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-zinc-400">{{ $description }}</p>
            @endif
        </div>
        @if ($href)
            <a href="{{ $href }}" class="turtube-text-link turtube-focus w-fit px-1 py-1 text-sm font-semibold">{{ $action }} <span aria-hidden="true">→</span></a>
        @endif
    </div>

    {{ $slot }}
</section>
