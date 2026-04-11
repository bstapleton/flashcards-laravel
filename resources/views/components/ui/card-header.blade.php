@props([
    'class' => '',
])

@php
    $baseClass = 'flex flex-col space-y-1.5 p-6';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<div class="{{ $finalClass }}">
    {{ $slot }}
</div>
