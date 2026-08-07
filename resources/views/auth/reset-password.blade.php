<x-guest-layout>
    <div class="mb-7">
        <p class="text-sm font-semibold text-red-500">Guvenli sifre yenileme</p>
        <h2 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Yeni sifreni belirle</h2>
        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">Hesabini korumak icin daha once kullanmadigin guclu bir sifre sec.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-auth.input label="E-posta adresi" name="email" type="email" :value="old('email', $request->email)" required autofocus autocomplete="username" placeholder="ornek@eposta.com" />
        <x-auth.input label="Yeni sifre" name="password" type="password" hint="En az 8 karakter" required autocomplete="new-password" placeholder="Yeni sifreni gir" />
        <x-auth.input label="Yeni sifre tekrar" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Sifreni tekrar gir" />

        <button type="submit" class="group flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-950/25 transition duration-200 hover:-translate-y-0.5 hover:bg-red-500 hover:shadow-red-950/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">
            Sifreyi guncelle
            <svg class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
        </button>
    </form>
</x-guest-layout>
