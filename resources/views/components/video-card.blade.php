<a href="{{ route('videos.show', $video) }}"
   class="block bg-gray-900 rounded-xl overflow-hidden shadow-lg hover:scale-105 transition duration-300">

    <div class="relative">

        <img
    src="{{ asset('storage/' . $video->thumbnail) }}"
    class="w-full h-40 object-cover"
    alt="Thumbnail">

        <span class="absolute bottom-2 right-2 bg-black px-2 py-1 text-xs rounded">
            {{ $video->duration }}
        </span>

    </div>

    <div class="p-4">

        <h3 class="font-bold text-lg">
            {{ $video->title }}
        </h3>

        <p class="text-gray-400 text-sm mt-1">
            {{ $video->channel_name }}
        </p>

        <p class="text-gray-500 text-xs mt-2">
            👁 {{ number_format($video->views) }} görüntülenme
        </p>

    </div>

</a>