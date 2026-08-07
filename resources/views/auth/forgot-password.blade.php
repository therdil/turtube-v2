<x-guest-layout>
    <a href="{{ route('login') }}" class="mb-6 inline-flex items-center gap-1.5 rounded-lg text-sm font-semibold text-zinc-500 transition hover:text-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m15 18-6-6 6-6" /></svg>
        Girise don
    </a>
    <div class="mb-7">
        <p class="text-sm font-semibold text-red-500">Sifre yenileme</p>
        <h2 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">E-postani kontrol et</h2>
        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">Hesabina bagli e-posta adresini gir; sana guvenli bir sifre yenileme baglantisi gonderecegiz.</p>
    </div>

    <x-auth-session-status class="mb-5 rounded-xl border border-emerald-500/25 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-300" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <x-auth.input label="E-posta adresi" name="email" type="email" :value="old('email')" required autofocus autocomplete="username" placeholder="ornek@eposta.com" />
        <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-950/25 transition duration-200 hover:-translate-y-0.5 hover:bg-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
            Yenileme baglantisi gonder
        </button>
    </form>
</x-guest-layout>
