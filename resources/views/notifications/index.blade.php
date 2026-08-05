@extends('layouts.turtube')

@section('title', 'Bildirimler')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Hesabın</p><h1 class="mt-2 text-4xl font-bold text-white">Bildirimler</h1></div>
        <div class="flex flex-wrap gap-3">
            @if (auth()->user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="rounded-xl border border-gray-700 px-4 py-2 text-sm font-medium text-gray-200 transition hover:border-red-500 hover:text-white">Tümünü okundu işaretle</button></form>
            @endif
            @if ($notifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.clear') }}" onsubmit="return confirm('Tüm bildirimleri temizlemek istiyor musunuz?')">@csrf @method('DELETE')<button class="rounded-xl border border-gray-700 px-4 py-2 text-sm font-medium text-gray-300 transition hover:border-red-500 hover:text-white">Tümünü temizle</button></form>
            @endif
        </div>
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            <a href="{{ route('notifications.visit', $notification->id) }}" class="block rounded-2xl border p-5 transition {{ $notification->read_at ? 'border-gray-800 bg-gray-900/60' : 'border-red-500/50 bg-red-950/30 hover:border-red-400' }}">
                <div class="flex items-start gap-4"><div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-600/20 text-red-300">{{ data_get($notification->data, 'kind') === 'comment' ? '💬' : '👤' }}</div><div class="min-w-0 flex-1"><h2 class="font-semibold text-white">{{ data_get($notification->data, 'title', 'Bildirim') }}</h2><p class="mt-1 text-sm text-gray-300">{{ data_get($notification->data, 'message') }}</p><p class="mt-2 text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</p></div>@if (! $notification->read_at)<span class="mt-2 h-2.5 w-2.5 rounded-full bg-red-500"></span>@endif</div>
            </a>
        @empty
            <x-ui.card class="p-12 text-center"><div class="text-5xl">🔔</div><h2 class="mt-4 text-2xl font-bold text-white">Henüz bildirimin yok</h2><p class="mt-3 text-gray-400">Yorumlar ve yeni aboneler burada görünecek.</p></x-ui.card>
        @endforelse
    </div>
    <div class="mt-8">{{ $notifications->links() }}</div>
</div>
@endsection
