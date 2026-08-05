@extends('layouts.turtube')

@section('title', $user->name.' · Kullanıcı aktiviteleri')

@section('content')
<div class="mx-auto max-w-5xl">
    <div class="mb-8 flex items-start justify-between gap-4"><div><a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-red-400 hover:text-red-300">← Kullanıcılara dön</a><h1 class="mt-3 text-4xl font-bold text-white">{{ $user->name }}</h1><p class="mt-2 text-gray-400">{{ $user->email }} · {{ $user->created_at->format('d.m.Y') }} tarihinde katıldı</p></div>@if($user->banned_at)<span class="rounded-full bg-rose-500/20 px-4 py-2 text-sm font-semibold text-rose-200">Banlı hesap</span>@endif</div>
    <div class="grid gap-4 sm:grid-cols-3"><x-ui.card class="p-5"><p class="text-sm text-gray-400">Videolar</p><p class="mt-2 text-3xl font-bold text-white">{{ $user->videos_count }}</p></x-ui.card><x-ui.card class="p-5"><p class="text-sm text-gray-400">Yorumlar</p><p class="mt-2 text-3xl font-bold text-white">{{ $user->comments_count }}</p></x-ui.card><x-ui.card class="p-5"><p class="text-sm text-gray-400">Gönderilen raporlar</p><p class="mt-2 text-3xl font-bold text-white">{{ $user->video_reports_count }}</p></x-ui.card></div>
    <x-ui.card class="mt-7 overflow-hidden"><div class="border-b border-gray-800 px-6 py-5"><h2 class="text-xl font-bold text-white">Son aktiviteler</h2></div><div class="divide-y divide-gray-800">@forelse($activities as $activity)<div class="flex items-center justify-between gap-5 px-6 py-4"><div><p class="text-sm font-semibold text-red-300">{{ $activity['type'] }}</p>@if($activity['url'])<a href="{{ $activity['url'] }}" class="mt-1 block text-white hover:text-red-400">{{ $activity['description'] }}</a>@else<p class="mt-1 text-white">{{ $activity['description'] }}</p>@endif</div><time class="shrink-0 text-sm text-gray-500">{{ $activity['created_at']->diffForHumans() }}</time></div>@empty<p class="p-8 text-center text-gray-400">Kaydedilmiş aktivite bulunmuyor.</p>@endforelse</div></x-ui.card>
</div>
@endsection
