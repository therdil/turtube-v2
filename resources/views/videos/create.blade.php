<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold">
            Video Yükle
        </h2>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8">

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Hatalar:</strong>

                <ul class="list-disc ml-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('videos.store') }}"
              enctype="multipart/form-data">

            @csrf

            <div class="mb-6">
                <label class="block mb-2 font-semibold">
                    Başlık
                </label>

                <input
                    type="text"
                    name="title"
                    class="w-full rounded border-gray-300"
                >
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold">
                    Açıklama
                </label>

                <textarea
                    name="description"
                    rows="5"
                    class="w-full rounded border-gray-300"
                ></textarea>
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold">
                    Thumbnail
                </label>

                <input
                    type="file"
                    name="thumbnail"
                >
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold">
                    Video
                </label>

                <input
                    type="file"
                    name="video"
                >
            </div>

            <button
                class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700">
                Yükle
            </button>

        </form>
    </div>
</x-app-layout>