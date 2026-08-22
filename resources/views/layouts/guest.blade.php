<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="dark light">
    <meta name="robots" content="noindex,follow">
    <title>{{ config('app.name', 'TurTube') }} · Hesap</title>
    <x-brand.favicon />
    <x-theme-preload />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-theme-endpoint="{{ auth()->check() && \Illuminate\Support\Facades\Route::has('profile.theme') ? url('/profile/theme') : '' }}" x-data="turtubeShell()" class="min-h-screen bg-zinc-950 font-sans text-zinc-100 antialiased selection:bg-red-500/30 selection:text-white">
    <div class="absolute right-5 top-5 z-20 sm:right-8 sm:top-8"><x-theme-selector /></div>
    <main class="relative isolate flex min-h-screen overflow-hidden">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            <div class="absolute left-[-12rem] top-[-10rem] h-[30rem] w-[30rem] rounded-full bg-red-600/15 blur-3xl dark:bg-red-600/20"></div>
            <div class="absolute bottom-[-14rem] right-[-12rem] h-[34rem] w-[34rem] rounded-full bg-violet-500/10 blur-3xl dark:bg-violet-500/15"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(113,113,122,0.06)_1px,transparent_1px),linear-gradient(90deg,rgba(113,113,122,0.06)_1px,transparent_1px)] bg-[size:32px_32px] [mask-image:linear-gradient(to_bottom,black,transparent_75%)] dark:bg-[linear-gradient(rgba(255,255,255,0.035)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.035)_1px,transparent_1px)]"></div>
        </div>

        <section class="hidden w-[46%] flex-col justify-between border-r border-zinc-200/80 bg-white/40 p-10 backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.025] lg:flex xl:p-14">
            <a href="{{ url('/') }}" class="group inline-flex w-fit items-center gap-3 rounded-2xl focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-4 dark:focus-visible:ring-offset-[#07090f]" aria-label="TurTube ana sayfasına dön">
                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-red-600 shadow-lg shadow-red-950/30 transition duration-200 group-hover:scale-105 group-hover:bg-red-500">
                    <svg class="ml-0.5 h-5 w-5 fill-white" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.25 5.56A1.5 1.5 0 0 0 6 6.86v10.28a1.5 1.5 0 0 0 2.25 1.3l8.9-5.14a1.5 1.5 0 0 0 0-2.6L8.25 5.56Z" /></svg>
                </span>
                <span class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">Tur<span class="text-red-500">Tube</span></span>
            </a>

            <div class="max-w-lg">
                <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-red-500/20 bg-red-500/10 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-red-600 dark:text-red-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                    Video odakli platform
                </div>
                <h1 class="text-4xl font-bold leading-[1.12] tracking-tight text-zinc-950 dark:text-white xl:text-5xl">Izle, uret ve toplulugunu buyut.</h1>
                <p class="mt-5 max-w-md text-base leading-7 text-zinc-600 dark:text-zinc-400">TurTube, icerik kesfi ile yaratici araclarini tek bir premium deneyimde bulusturur.</p>
            </div>

            <div class="flex items-center gap-3 text-sm text-zinc-500 dark:text-zinc-500">
                <span class="flex h-8 w-8 items-center justify-center rounded-full border border-zinc-200 bg-white/80 dark:border-white/10 dark:bg-white/5">
                    <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
                </span>
                Guvenli hesap deneyimi
            </div>
        </section>

        <section class="flex min-w-0 flex-1 flex-col px-5 py-5 sm:px-8 sm:py-8 lg:px-12 xl:px-20">
            <header class="flex items-center justify-between lg:hidden">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 rounded-xl font-black tracking-tight text-zinc-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 dark:text-white" aria-label="TurTube ana sayfasına dön">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-red-600"><svg class="ml-0.5 h-4 w-4 fill-white" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.25 5.56A1.5 1.5 0 0 0 6 6.86v10.28a1.5 1.5 0 0 0 2.25 1.3l8.9-5.14a1.5 1.5 0 0 0 0-2.6L8.25 5.56Z" /></svg></span>
                    Tur<span class="text-red-500">Tube</span>
                </a>
                <a href="{{ url('/') }}" class="rounded-lg px-2 py-1.5 text-xs font-semibold text-zinc-500 transition hover:text-red-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">Ana sayfa</a>
            </header>

            <div class="flex flex-1 items-center justify-center py-10 sm:py-14">
                <div class="w-full max-w-[29rem]">
                    <div class="rounded-[1.75rem] border border-zinc-200/80 bg-white/80 p-5 shadow-2xl shadow-zinc-950/5 backdrop-blur-xl sm:p-8 dark:border-white/10 dark:bg-zinc-950/65 dark:shadow-black/30">
                        {{ $slot }}
                    </div>
                    <p class="mt-6 text-center text-xs leading-5 text-zinc-500">Devam ederek TurTube kullanim kosullari ve gizlilik ilkelerini kabul etmis olursun.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
