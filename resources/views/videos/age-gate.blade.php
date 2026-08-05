@extends('layouts.turtube')

@section('title', 'Yaş doğrulaması · '.$video->title)
@section('meta_robots', 'noindex,nofollow')

@section('content')
<div class="mx-auto flex min-h-[60vh] max-w-2xl items-center justify-center">
    <x-ui.card class="w-full p-8 text-center sm:p-12">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-orange-500/15 text-2xl text-orange-300">{{ $video->age_restriction }}+</div>
        <h1 class="mt-6 text-3xl font-bold text-white">Yaş sınırlı içerik</h1>
        <p class="mt-4 leading-7 text-gray-400">Bu video {{ $video->age_restriction }} yaş ve üzeri izleyiciler için işaretlenmiştir. Devam ederek gerekli yaşta olduğunuzu onaylarsınız.</p>
        <form method="POST" action="{{ route('videos.age-confirmation', $video) }}" class="mt-8">@csrf<input type="hidden" name="confirmed" value="1"><button class="rounded-xl bg-red-600 px-6 py-3 font-semibold text-white hover:bg-red-500">Yaşımı onaylıyor ve devam ediyorum</button></form>
        <a href="{{ route('home') }}" class="mt-5 inline-block text-sm text-gray-400 hover:text-white">Ana sayfaya dön</a>
    </x-ui.card>
</div>
@endsection
