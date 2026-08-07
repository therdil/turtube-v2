<x-guest-layout>
    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-500">
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="5" y="10" width="14" height="10" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3" /></svg>
    </div>
    <div class="mb-7 mt-6">
        <p class="text-sm font-semibold text-amber-600 dark:text-amber-400">Guvenlik kontrolu</p>
        <h2 class="mt-2 text-3xl font-bold tracking-tight text-zinc-950 dark:text-white">Sifreni onayla</h2>
        <p class="mt-2 text-sm leading-6 text-zinc-600 dark:text-zinc-400">Bu hassas islemi tamamlamadan once kimligini dogrulamamiz gerekiyor.</p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf
        <x-auth.input label="Mevcut sifre" name="password" type="password" required autofocus autocomplete="current-password" placeholder="Sifreni gir" />
        <button type="submit" class="flex w-full items-center justify-center rounded-xl bg-red-600 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-950/25 transition duration-200 hover:-translate-y-0.5 hover:bg-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-950">Onayla ve devam et</button>
    </form>
</x-guest-layout>
