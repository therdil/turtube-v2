@extends('layouts.turtube')

@section('title', 'Kullanıcı Yönetimi')

@section('content')
<div class="mx-auto max-w-7xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Yönetim</p><h1 class="mt-2 text-4xl font-bold text-white">Kullanıcılar</h1><p class="mt-2 text-gray-400">Hesap, rozet, Premium, yasak ve aktivite yönetimi.</p></div>
        <form method="GET"><input name="q" value="{{ request('q') }}" maxlength="100" placeholder="Ad veya e-posta ara..." class="rounded-xl border border-gray-700 bg-gray-900 px-4 py-2 text-white focus:border-red-500 focus:outline-none"></form>
    </div>
    <div class="overflow-x-auto rounded-2xl border border-gray-800 bg-gray-900">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-gray-950 text-xs uppercase tracking-wider text-gray-500"><tr><th class="px-5 py-4">Kullanıcı</th><th class="px-5 py-4">Aktivite</th><th class="px-5 py-4">Durum</th><th class="px-5 py-4">Premium</th><th class="px-5 py-4 text-right">İşlemler</th></tr></thead>
            <tbody class="divide-y divide-gray-800">
                @forelse ($users as $user)
                    <tr class="align-top">
                        <td class="px-5 py-4"><p class="font-semibold text-white">{{ $user->name }} @if($user->is_verified)<span class="ml-1 text-sky-400">✓</span>@endif</p><p class="mt-1 text-gray-400">{{ $user->email }}</p></td>
                        <td class="px-5 py-4 text-gray-300"><p>{{ $user->videos_count }} video · {{ $user->comments_count }} yorum</p><p class="mt-1 text-xs text-gray-500">{{ $user->video_reports_count }} bildirim</p></td>
                        <td class="px-5 py-4"><div class="flex flex-wrap gap-2"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $user->is_admin ? 'bg-red-600/20 text-red-300' : 'bg-gray-800 text-gray-300' }}">{{ $user->is_admin ? 'Yönetici' : 'Kullanıcı' }}</span>@if($user->banned_at)<span class="rounded-full bg-rose-500/20 px-3 py-1 text-xs font-semibold text-rose-300">Banlı</span>@endif</div>@if($user->ban_reason)<p class="mt-2 max-w-48 text-xs text-rose-300">{{ $user->ban_reason }}</p>@endif</td>
                        <td class="px-5 py-4"><p class="{{ $user->hasPremiumAccess() ? 'text-amber-300' : 'text-gray-500' }}">{{ $user->hasPremiumAccess() ? $user->premium_until->format('d.m.Y').' tarihine kadar' : 'Aktif değil' }}</p></td>
                        <td class="px-5 py-4"><div class="flex flex-wrap justify-end gap-2">
                            <a href="{{ route('admin.users.show', $user) }}" class="rounded-lg border border-gray-700 px-3 py-2 text-xs text-white hover:border-red-500">Aktivite</a>
                            @if (! $user->is(auth()->user()))<form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}">@csrf<button class="rounded-lg border border-gray-700 px-3 py-2 text-xs text-white hover:border-red-500">Yetki</button></form>@endif
                            <form method="POST" action="{{ route('admin.users.premium', $user) }}" class="flex gap-1">@csrf @method('PATCH')<select name="duration" class="rounded-lg border border-gray-700 bg-gray-950 px-2 py-2 text-xs text-white"><option value="1">+1 ay</option><option value="3">+3 ay</option><option value="12">+12 ay</option><option value="revoke">Kaldır</option></select><button class="rounded-lg border border-amber-500/60 px-3 py-2 text-xs text-amber-200 hover:bg-amber-500 hover:text-gray-950">Premium</button></form>
                            <form method="POST" action="{{ route('admin.users.verified', $user) }}">@csrf @method('PATCH')<button class="rounded-lg border px-3 py-2 text-xs {{ $user->is_verified ? 'border-sky-400 text-sky-200' : 'border-gray-700 text-gray-300 hover:border-sky-400' }}">{{ $user->is_verified ? 'Rozeti kaldır' : 'Doğrula' }}</button></form>
                            @if (! $user->is(auth()->user()) && ! $user->is_admin)<form method="POST" action="{{ route('admin.users.ban', $user) }}" class="flex gap-1">@csrf @method('PATCH')@if(!$user->banned_at)<input name="ban_reason" maxlength="500" placeholder="Ban nedeni" class="w-28 rounded-lg border border-gray-700 bg-gray-950 px-2 py-2 text-xs text-white">@endif<button class="rounded-lg border px-3 py-2 text-xs {{ $user->banned_at ? 'border-emerald-500 text-emerald-300' : 'border-rose-500 text-rose-200' }}">{{ $user->banned_at ? 'Banı kaldır' : 'Banla' }}</button></form>@endif
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-10 text-center text-gray-400">Kullanıcı bulunamadı.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-8">{{ $users->links() }}</div>
</div>
@endsection
