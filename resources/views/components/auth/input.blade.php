@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
])

@php
    $inputId = $attributes->get('id', $name);
    $errorId = $inputId.'-error';
    $hasError = $errors->has($name);
@endphp

<div>
    <div class="mb-2 flex items-center justify-between gap-3">
        <label for="{{ $inputId }}" class="text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ $label }}</label>
        @if ($hint)
            <span class="text-xs text-zinc-500">{{ $hint }}</span>
        @endif
    </div>
    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @if ($hasError) aria-invalid="true" aria-describedby="{{ $errorId }}" @endif
        {{ $attributes->except(['id', 'class'])->merge([
            'class' => 'block w-full rounded-xl border bg-zinc-50 px-4 py-3 text-sm text-zinc-950 outline-none transition placeholder:text-zinc-400 focus:border-red-500 focus:bg-white focus:ring-4 focus:ring-red-500/10 dark:bg-white/[0.045] dark:text-white dark:placeholder:text-zinc-600 dark:focus:bg-white/[0.07] '.($hasError ? 'border-red-500/80' : 'border-zinc-200 dark:border-white/10'),
        ]) }}>
    @if ($hasError)
        <p id="{{ $errorId }}" class="mt-2 text-xs font-medium text-red-500" role="alert">{{ $errors->first($name) }}</p>
    @endif
</div>
