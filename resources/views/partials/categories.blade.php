<div class="mb-6 overflow-x-auto scrollbar-hide">

    <div class="flex min-w-max items-center gap-3">

        {{-- Tümü --}}
        <a
            href="{{ route('home') }}"
            class="{{ request()->routeIs('home')
                ? 'bg-red-600 text-white'
                : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}
                whitespace-nowrap rounded-full px-5 py-2 text-sm font-medium transition"
        >
            Tümü
        </a>

        {{-- Kategoriler --}}
        @foreach($categories as $category)

            <a
                href="{{ route('categories.show', $category) }}"
                class="{{ request()->routeIs('categories.show') && request()->route('category')?->id === $category->id
                    ? 'bg-red-600 text-white'
                    : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}
                    whitespace-nowrap rounded-full px-5 py-2 text-sm font-medium transition"
            >
                {{ $category->name }}
            </a>

        @endforeach

    </div>

</div>