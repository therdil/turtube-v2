<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TurTube Creator Studio')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>

<body class="bg-[#0f0f0f] text-white overflow-hidden">

<div class="flex h-screen">

    @include('studio.partials.sidebar')

    <div class="flex flex-1 flex-col">

        @include('studio.partials.topbar')

        <main class="flex-1 overflow-y-auto p-8">

            @yield('content')

        </main>

    </div>

</div>

</body>

</html>