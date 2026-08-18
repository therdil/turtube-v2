<div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6 transition-all duration-300 hover:border-red-600 hover:shadow-lg hover:shadow-red-600/10">

    <div class="flex items-start justify-between">

        <div>

            <p class="text-sm text-zinc-400">
                {{ $title }}
            </p>

            <h2 class="mt-3 text-4xl font-bold text-white">
                {{ is_numeric($value) ? number_format($value) : $value }}
            </h2>

            @isset($subtitle)

                <p class="mt-2 text-xs text-zinc-500">
                    {{ $subtitle }}
                </p>

            @endisset

        </div>

        <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-red-600/10 text-2xl">

            {{ $icon }}

        </div>

    </div>

</div>