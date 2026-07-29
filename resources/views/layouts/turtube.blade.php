<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'TurTube')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-gray-100 antialiased">

    <div class="min-h-screen flex flex-col">

        {{-- Header --}}
        @include('partials.header')

        <div class="flex flex-1">

            {{-- Sidebar --}}
            @include('partials.sidebar')

            <main class="flex-1 overflow-y-auto p-6 lg:p-8">

                @include('partials.flash')

                {{-- Kategori Menüsü --}}
                @include('partials.categories')

                {{-- Sayfa İçeriği --}}
                @yield('content')

            </main>

        </div>

    </div>

</body>

</html>
