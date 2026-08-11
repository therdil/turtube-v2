<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TurTube Creator Studio')</title>
    <x-brand.favicon />

    <x-theme-preload />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body
    class="min-h-[100dvh] overflow-x-hidden bg-gray-50 text-gray-900 dark:bg-[#0f0f0f] dark:text-white"
    data-studio-shell
    data-theme-endpoint="{{ auth()->check() && \Illuminate\Support\Facades\Route::has('profile.theme') ? url('/profile/theme') : '' }}"
    x-data="turtubeShell()">

<div class="flex min-h-[100dvh]">

    @include('studio.partials.sidebar')

    <div class="flex min-w-0 flex-1 flex-col">

        @include('studio.partials.topbar')

        <main class="min-w-0 flex-1 p-4 sm:p-6 lg:p-8">

            @yield('content')

        </main>

    </div>

</div>

</body>

</html>
