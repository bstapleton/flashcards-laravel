@props([
    'class' => '',
])

@php
    $baseClass = 'w-full';
    $finalClass = $baseClass . ' ' . $class;
@endphp

<div class="{{ $finalClass }}" x-data="{ activeTab: '{{ $defaultTab ?? 'tab-1' }}' }">
    {{ $slot }}
</div>
