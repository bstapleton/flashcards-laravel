@props([
    'variant' => 'default',
    'size' => 'default',
    'class' => '',
    'type' => 'button',
    'disabled' => false,
    'route' => null,
])

@php
    $baseClasses = 'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50';

    $variants = [
        'default' => 'bg-primary text-primary-foreground hover:bg-primary/90',
        'destructive' => 'bg-destructive text-destructive-foreground hover:bg-destructive/90',
        'outline' => 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'ghost' => 'hover:bg-accent hover:text-accent-foreground',
        'link' => 'text-primary underline-offset-4 hover:underline',
        'indigo' => 'bg-indigo-600 text-white hover:bg-indigo-700',
        'green' => 'bg-green-600 text-white hover:bg-green-700',
        'gray' => 'bg-gray-600 text-white hover:bg-gray-700',
    ];

    $sizes = [
        'default' => 'h-10 px-4 py-2',
        'sm' => 'h-9 rounded-md px-3',
        'lg' => 'h-11 rounded-md px-8',
        'icon' => 'h-10 w-10',
    ];

    $variantClass = $variants[$variant] ?? $variants['default'];
    $sizeClass = $sizes[$size] ?? $sizes['default'];
    $finalClass = $baseClasses . ' ' . $variantClass . ' ' . $sizeClass . ' ' . $class;
@endphp

@if ($type === 'submit')
    <button type="submit" {{ $disabled ? 'disabled' : '' }} class="{{ $finalClass }}">
        {{ $slot }}
    </button>
@elseif ($route)
    <a href="{{ route($route) }}" class="{{ $finalClass }}">
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} class="{{ $finalClass }}">
        {{ $slot }}
    </button>
@endif
