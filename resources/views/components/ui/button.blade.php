@props([
    'variant' => 'primary',
    'type' => 'button',
])

@php

$classes = match ($variant) {

    'primary' =>
        'bg-red-600 hover:bg-red-700 text-white',

    'secondary' =>
        'bg-gray-800 hover:bg-gray-700 text-white border border-gray-700',

    'ghost' =>
        'hover:bg-gray-800 text-gray-300',

    default =>
        'bg-red-600 hover:bg-red-700 text-white',

};

@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' =>
        "inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium transition duration-200 {$classes}"
    ]) }}>

    {{ $slot }}

</button>