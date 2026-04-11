@props([
    'href' => '#',
    'active' => false,
    'class' => '',
])

@php
    $baseClass = 'inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200';
    $activeClass = 'border-indigo-500 text-gray-900';
    $inactiveClass = 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
    
    $stateClass = $active ? $activeClass : $inactiveClass;
    $finalClass = $baseClass . ' ' . $stateClass . ' ' . $class;
@endphp

<a href="{{ $href }}" class="{{ $finalClass }}">
    {{ $slot }}
</a>
