@props([
    'class' => '',
])

@php
    $baseClass = 'mb-1 font-semibold leading-none tracking-tight';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<h5 class="{{ $finalClass }}">
    {{ $slot }}
</h5>
