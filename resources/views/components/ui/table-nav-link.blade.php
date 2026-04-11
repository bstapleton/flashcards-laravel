@props([
    'route' => '',
    'href' => '#',
    'mobileHidden' => false,
    'count' => 0,
])

@php
    $baseClass = 'py-2 px-1 border-b-2';
    $activeClass = 'border-indigo-500 font-medium text-sm text-indigo-600';
    $inactiveClass = 'border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300';

    $stateClass = request()->routeIs($route) ? $activeClass : $inactiveClass;
    $mobileClass = $mobileHidden ? 'hidden md:inline-block' : '';
    $finalClass = $mobileClass . ' ' . $baseClass . ' ' . $stateClass;
@endphp

<a href="{{ route($route) }}" class="{{ $finalClass }}">
    {{ $slot }}@if (request()->routeIs($route)) ({{ $count }}) @endif
</a>
