@props([
    'padding' => 'p-6',
])

<div
    {{ $attributes->merge([
        'class' => "rounded-2xl border border-gray-800 bg-gray-900/80 backdrop-blur-sm {$padding}"
    ]) }}>

    {{ $slot }}

</div>