@extends('layouts.turtube')

@section('title', 'Video Yükle')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="bg-gray-900 rounded-2xl shadow-xl border border-gray-800 overflow-hidden">

        <div class="border-b border-gray-800 px-8 py-6">

            <h1 class="text-3xl font-bold">
                📤 Video Yükle
            </h1>

            <p class="text-gray-400 mt-2">
                Videonu yükle ve TurTube topluluğuyla paylaş.
            </p>

        </div>

        <div class="p-8">

            @if ($errors->any())

                <div class="bg-red-600/20 border border-red-500 text-red-300 rounded-xl p-4 mb-8">

                    <ul class="space-y-1">

                        @foreach ($errors->all() as $error)

                            <li>• {{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <form
                method="POST"
                action="{{ route('videos.store') }}"
                enctype="multipart/form-data">

                @csrf

                <div class="space-y-7">

                    <div>

                        <label class="block mb-2 font-semibold">

                            Başlık

                        </label>

                        <input
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            class="w-full rounded-xl bg-gray-800 border border-gray-700 focus:border-red-500 focus:ring-red-500 text-white"
                            required>

                    </div>

                    <div>

                        <label class="block mb-2 font-semibold">

                            Açıklama

                        </label>

                        <textarea
                            rows="6"
                            name="description"
                            class="w-full rounded-xl bg-gray-800 border border-gray-700 focus:border-red-500 focus:ring-red-500 text-white">{{ old('description') }}</textarea>

                    </div>

                    <div>

                        <label class="block mb-3 font-semibold">

                            Video Dosyası

                        </label>

                        <input
                            type="file"
                            name="video"
                            accept="video/mp4"
                            class="block w-full text-sm text-gray-300
                                   file:mr-5
                                   file:px-5
                                   file:py-3
                                   file:rounded-xl
                                   file:border-0
                                   file:bg-red-600
                                   file:text-white
                                   hover:file:bg-red-700"
                            required>

                    </div>

                    <div class="pt-3">

                        <button
                            type="submit"
                            class="bg-red-600 hover:bg-red-700 transition px-8 py-3 rounded-xl font-semibold">

                            🚀 Videoyu Yükle

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection