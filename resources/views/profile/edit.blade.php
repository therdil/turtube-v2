@extends('layouts.turtube')

@section('title', 'Profil Ayarları')

@section('content')

<div class="mx-auto max-w-5xl space-y-8">

    {{-- Sayfa başlığı --}}
    <section class="relative overflow-hidden rounded-3xl border border-zinc-800 bg-gradient-to-br from-zinc-900 via-zinc-950 to-red-950/30 p-6 shadow-2xl sm:p-8">
        <div class="relative">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-400">
                Hesap
            </p>

            <h1 class="mt-2 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Profil Ayarları
            </h1>

            <p class="mt-3 max-w-2xl text-zinc-400">
                Profil bilgilerinizi, şifrenizi ve hesap güvenliğinizi buradan yönetin.
            </p>
        </div>

        <div class="pointer-events-none absolute -right-16 -top-24 h-64 w-64 rounded-full bg-red-600/20 blur-3xl"></div>
    </section>

    {{-- Profil bilgileri --}}
    <section class="rounded-3xl border border-zinc-800 bg-zinc-900/80 p-6 shadow-xl sm:p-8">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-white">
                Profil Bilgileri
            </h2>

            <p class="mt-1 text-sm text-zinc-400">
                Adınızı ve e-posta adresinizi güncelleyin.
            </p>
        </div>

        @include('profile.partials.update-profile-information-form')
    </section>

    {{-- Şifre --}}
    <section class="rounded-3xl border border-zinc-800 bg-zinc-900/80 p-6 shadow-xl sm:p-8">
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-white">
                Şifre ve Güvenlik
            </h2>

            <p class="mt-1 text-sm text-zinc-400">
                Hesabınızı korumak için güçlü ve benzersiz bir şifre kullanın.
            </p>
        </div>

        @include('profile.partials.update-password-form')
    </section>

    {{-- Hesap silme --}}
    <section class="rounded-3xl border border-red-900/50 bg-zinc-900/80 p-6 shadow-xl sm:p-8">
        <div class="mb-6">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-red-400">
                Tehlikeli Bölge
            </p>

            <h2 class="mt-2 text-xl font-semibold text-white">
                Hesap Silme
            </h2>

            <p class="mt-1 max-w-2xl text-sm text-zinc-400">
                Hesabınızı silmeden önce hesap silme süreci ve verileriniz hakkında ayrıntılı bilgi edinin.
            </p>
        </div>

        <div class="mb-6">
            <a
                href="{{ route('account.delete') }}"
                class="inline-flex items-center rounded-xl border border-red-500/40 bg-red-500/10 px-4 py-2.5 text-sm font-semibold text-red-300 transition hover:border-red-400 hover:bg-red-500/20 hover:text-white"
            >
                Hesap Silme Sayfası
            </a>
        </div>

        @include('profile.partials.delete-user-form')
    </section>

</div>

@endsection