@props([
    'class' => '',
])

@php
    $baseClass = 'text-sm text-muted-foreground';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<p class="{{ $finalClass }}">
    {{ $slot }}
</p>
