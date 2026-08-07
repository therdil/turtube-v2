<x-guest-layout>
    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-500/10 text-red-500">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6.5 12 12l8-5.5M5.5 5h13A1.5 1.5 0 0 1 20 6.5v11a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 4 17.5v-11A1.5 1.5 0 0 1 5.5 5Z" /></svg>
    </div>
    <div class="mb-7 mt-6">
        <p class="text-sm font-semibold text-red-500">Son bir adim</p>
        <h2 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">E-postani dogrula</h2>
        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">TurTube deneyimini etkinlestirmek icin e-posta adresine gonderdigimiz baglantiya tikla. Mesaj gelmediyse yeni bir baglanti isteyebilirsin.</p>
    </div>

    @if (session('status') === 'verification-link-sent')
        <div class="mb-5 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-700 dark:text-emerald-300" role="status">Yeni dogrulama baglantisi e-posta adresine gonderildi.</div>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-950/25 transition duration-200 hover:-translate-y-0.5 hover:bg-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
            Dogrulama e-postasini tekrar gonder
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-3">
        @csrf
        <button type="submit" class="w-full rounded-xl border border-zinc-200 bg-white px-4 py-3 text-sm font-semibold text-zinc-700 transition hover:border-red-500/40 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:border-white/10 dark:bg-white/[0.03] dark:text-zinc-300 dark:hover:border-red-500/50 dark:hover:text-red-300 dark:focus-visible:ring-offset-zinc-950">Farkli hesapla giris yap</button>
    </form>
</x-guest-layout>
