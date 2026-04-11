@props([
    'class' => '',
])

@php
    $baseClass = 'p-6 pt-0';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<div class="{{ $finalClass }}">
    {{ $slot }}
</div>
