@props([
    'padding' => 'p-6',
])

<div
    {{ $attributes->merge([
        'class' => "turtube-surface {$padding}"
    ]) }}>

    {{ $slot }}

</div>
