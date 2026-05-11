@props([
    'class' => '',
])

@php
    $baseClass = 'rounded-lg border bg-card text-card-foreground shadow-sm';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<div class="{{ $finalClass }}">
    {{ $slot }}
</div>
