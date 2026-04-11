@props([
    'class' => '',
])

@php
    $baseClass = 'text-2xl font-semibold leading-none tracking-tight';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<h3 class="{{ $finalClass }}">
    {{ $slot }}
</h3>
