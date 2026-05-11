@props([
    'value' => '',
    'class' => '',
])

@php
    $baseClass = 'mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<div x-show="$parent.activeTab === '{{ $value }}'" class="{{ $finalClass }}">
    {{ $slot }}
</div>
