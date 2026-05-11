@props([
    'class' => '',
])

@php
    $baseClass = 'relative w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<nav class="{{ $finalClass }}">
    {{ $slot }}
</nav>
