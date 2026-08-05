@props(['title', 'subtitle' => null])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-zinc-800 bg-zinc-900 p-6']) }}>
    <div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl font-bold text-white">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-sm text-zinc-500">{{ $subtitle }}</p>
        @endif
    </div>

    {{ $slot }}
</section>
