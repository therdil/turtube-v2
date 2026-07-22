<a href="{{ route('videos.show', $video) }}"
   class="block bg-gray-900 rounded-xl overflow-hidden shadow-lg hover:shadow-2xl hover:scale-[1.02] transition duration-300">

    <div class="relative h-52 bg-black rounded-t-xl overflow-hidden">

    <img
        src="{{ asset('storage/' . $video->thumbnail) }}"
        alt="Thumbnail"
        class="w-full h-full object-cover">

        @if($video->duration)
            <span class="absolute bottom-2 right-2 bg-black/80 px-2 py-1 text-xs rounded">
                {{ $video->duration }}
            </span>
        @endif

    </div>

    <div class="p-4">

        <h3 class="font-bold text-lg line-clamp-2 min-h-[56px]">
            {{ $video->title }}
        </h3>

        <p class="text-gray-400 text-sm mt-2">
            {{ $video->channel_name }}
        </p>

        <p class="text-gray-500 text-xs mt-1">
            👁 {{ number_format($video->views) }} görüntülenme
        </p>

    </div>

</a>