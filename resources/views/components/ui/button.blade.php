@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php

$classes = match ($variant) {

    'primary' =>
        'border border-red-500 bg-red-600 text-white shadow-lg shadow-red-950/20 hover:bg-red-700',

    'secondary' =>
        'border border-gray-700 bg-gray-800 text-white hover:border-gray-600 hover:bg-gray-700',

    'ghost' =>
        'text-gray-300 hover:bg-gray-800 hover:text-white',

    default =>
        'border border-red-500 bg-red-600 text-white shadow-lg shadow-red-950/20 hover:bg-red-700',

};

@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' =>
        "turtube-control turtube-focus inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold {$classes}"
    ]) }}>

    {{ $slot }}

</button>
