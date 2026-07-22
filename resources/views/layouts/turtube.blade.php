<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TurTube')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-950 text-white">

    @include('partials.header')

    <div class="flex">

        @include('partials.sidebar')

        <main class="flex-1 p-8">
            @yield('content')
        </main>

    </div>

</body>

</html>