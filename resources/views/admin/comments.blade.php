@extends('layouts.turtube')

@section('title', 'Yorum Moderasyonu')

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Moderasyon</p><h1 class="mt-2 text-4xl font-bold text-white">Yorumlar</h1></div><form method="GET"><input name="q" maxlength="100" value="{{ request('q') }}" placeholder="Yorum, video veya kullanıcı ara..." class="rounded-xl border border-gray-700 bg-gray-900 px-4 py-2 text-white"></form></div>
    <div class="space-y-3">
        @forelse ($comments as $comment)
            <x-ui.card class="p-5"><div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"><div class="min-w-0"><p class="text-sm text-gray-400"><span class="font-semibold text-white">{{ $comment->user->name }}</span> · <a class="hover:text-red-400" href="{{ route('videos.show', $comment->video) }}">{{ $comment->video->title }}</a> · {{ $comment->created_at->diffForHumans() }}</p><p class="mt-3 whitespace-pre-line text-gray-200">{{ $comment->comment }}</p></div><form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Bu yorumu kaldırmak istiyor musunuz?')">@csrf @method('DELETE')<button class="rounded-lg border border-red-500/50 px-3 py-2 text-sm text-red-200 hover:bg-red-500 hover:text-white">Kaldır</button></form></div></x-ui.card>
        @empty
            <x-ui.card class="p-12 text-center text-gray-400">Yorum bulunamadı.</x-ui.card>
        @endforelse
    </div>
    <div class="mt-8">{{ $comments->links() }}</div>
</div>
@endsection
