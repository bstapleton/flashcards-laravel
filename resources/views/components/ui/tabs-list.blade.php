@props([
    'class' => '',
])

@php
    $baseClass = 'inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<div class="{{ $finalClass }}">
    {{ $slot }}
</div>
