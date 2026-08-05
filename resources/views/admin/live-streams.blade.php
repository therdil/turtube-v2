@extends('layouts.turtube')

@section('title', 'Canlı Yayın Yönetimi')

@section('content')
<div class="mx-auto max-w-6xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"><div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Yönetim</p><h1 class="mt-2 text-4xl font-bold text-white">Canlı yayınlar</h1></div><form method="GET"><select name="status" onchange="this.form.submit()" class="rounded-xl border border-gray-700 bg-gray-900 px-4 py-2 text-white"><option value="">Tüm durumlar</option>@foreach (['scheduled' => 'Planlandı', 'live' => 'Canlı', 'ended' => 'Sona erdi'] as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></form></div>
    <div class="space-y-3">
        @forelse ($streams as $stream)
            <x-ui.card class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"><div><a href="{{ route('live.show', $stream) }}" class="font-bold text-white hover:text-red-400">{{ $stream->title }}</a><p class="mt-1 text-sm text-gray-400">{{ $stream->user->name }} · {{ $stream->scheduled_at?->format('d.m.Y H:i') ?? 'Hemen' }}</p></div><div class="flex items-center gap-3"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $stream->status === 'live' ? 'bg-red-600 text-white' : 'bg-gray-800 text-gray-300' }}">{{ strtoupper($stream->status) }}</span>@if ($stream->status !== 'ended')<form method="POST" action="{{ route('admin.live.end', $stream) }}">@csrf @method('PATCH')<button class="rounded-lg border border-red-500/60 px-3 py-2 text-sm text-red-200 hover:bg-red-500 hover:text-white">Yayını sonlandır</button></form>@endif</div></x-ui.card>
        @empty
            <x-ui.card class="p-12 text-center text-gray-400">Canlı yayın bulunamadı.</x-ui.card>
        @endforelse
    </div>
    <div class="mt-8">{{ $streams->links() }}</div>
</div>
@endsection
