@extends('layouts.turtube')

@section('title', 'Daha Sonra İzle')

@section('content')

<div class="mx-auto max-w-[1800px] px-6 py-8">

    <div class="grid gap-8 xl:grid-cols-12">

        {{-- Sol Panel --}}
        <div class="xl:col-span-3">

            <div class="sticky top-24 overflow-hidden rounded-2xl border border-gray-800 bg-gray-900">

                <div class="bg-gradient-to-br from-red-600 via-red-700 to-red-900 p-8">

                    <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-white/10 text-5xl backdrop-blur">
                        💾
                    </div>

                    <h1 class="text-3xl font-bold text-white">
                        Daha Sonra İzle
                    </h1>

                    <p class="mt-3 text-red-100">
                        Kaydettiğin videolar burada saklanır.
                    </p>

                </div>

                <div class="space-y-4 p-6">

                    <div class="flex items-center justify-between">

                        <span class="text-gray-400">
                            Toplam Video
                        </span>

                        <span class="rounded-lg bg-red-600 px-3 py-1 font-semibold text-white">
                            {{ $videos->count() }}
                        </span>

                    </div>

                    <div class="border-t border-gray-800"></div>

                    <p class="text-sm leading-7 text-gray-400">
                        Buraya eklediğin videolar silinmez.
                        İstediğin zaman tekrar izleyebilirsin.
                    </p>

                </div>

            </div>

        </div>

        {{-- Sağ Panel --}}
        <div class="xl:col-span-9">

            @if($videos->count())

                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-3">

                    @foreach($videos as $video)

                        <x-video-card :video="$video"/>

                    @endforeach

                </div>

            @else

                <div class="flex min-h-[500px] items-center justify-center rounded-2xl border border-dashed border-gray-700 bg-gray-900">

                    <div class="max-w-md text-center">

                        <div class="mb-6 text-7xl">
                            📺
                        </div>

                        <h2 class="text-3xl font-bold text-white">
                            Listen boş
                        </h2>

                        <p class="mt-4 leading-7 text-gray-400">

                            Henüz Daha Sonra İzle listene video eklemedin.

                            Beğendiğin videoları kaydederek
                            burada istediğin zaman tekrar izleyebilirsin.

                        </p>

                        <a
                            href="{{ route('home') }}"
                            class="mt-8 inline-flex items-center rounded-xl bg-red-600 px-6 py-3 font-semibold text-white transition hover:bg-red-700">

                            Videoları Keşfet

                        </a>

                    </div>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection