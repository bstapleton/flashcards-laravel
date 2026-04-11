@props([
    'class' => '',
])

@php
    $baseClass = 'text-sm';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<div class="{{ $finalClass }}">
    {{ $slot }}
</div>
