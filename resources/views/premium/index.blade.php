@extends('layouts.turtube')

@section('title', 'TurTube Premium')

@section('content')
<div class="mx-auto max-w-6xl">
    <section data-theme-hero class="overflow-hidden rounded-3xl border border-amber-400/30 bg-gradient-to-br from-amber-950 via-gray-900 to-gray-950 p-8 sm:p-12">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-amber-300">TurTube Premium</p>
        <h1 class="mt-3 max-w-3xl text-4xl font-bold tracking-tight text-white sm:text-5xl">Daha özel içeriklere eriş.</h1>
        <p class="mt-5 max-w-2xl text-lg leading-8 text-gray-300">Premium içerikleri reklamsız deneyim, öncelikli özellikler ve üreticileri destekleme altyapısıyla izle.</p>
        @if ($hasPremium)
            <span class="mt-7 inline-flex rounded-xl bg-amber-400 px-5 py-3 font-semibold text-gray-950">Premium üyeliğin aktif</span>
        @else
            <span class="mt-7 inline-flex rounded-xl border border-amber-300/50 px-5 py-3 font-semibold text-amber-100">Ödeme entegrasyonu yapılandırılmayı bekliyor</span>
        @endif
    </section>

    <div class="mt-8 grid gap-6 md:grid-cols-3">
        <x-ui.card class="p-6"><div class="text-3xl">✨</div><h2 class="mt-4 text-xl font-bold text-white">Özel içerikler</h2><p class="mt-2 text-sm leading-6 text-gray-400">Premium etiketi taşıyan videolara eriş.</p></x-ui.card>
        <x-ui.card class="p-6"><div class="text-3xl">🚀</div><h2 class="mt-4 text-xl font-bold text-white">Öncelikli deneyim</h2><p class="mt-2 text-sm leading-6 text-gray-400">Yeni Premium özelliklerinden önce haberdar ol.</p></x-ui.card>
        <x-ui.card class="p-6"><div class="text-3xl">❤️</div><h2 class="mt-4 text-xl font-bold text-white">Üreticiyi destekle</h2><p class="mt-2 text-sm leading-6 text-gray-400">Favori kanallarının sürdürülebilir üretimine katkı sağla.</p></x-ui.card>
    </div>
</div>
@endsection
