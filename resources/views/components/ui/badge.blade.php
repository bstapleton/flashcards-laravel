@props([
    'variant' => 'default',
    'class' => '',
])

@php
    $baseClass = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2';
    
    $variants = [
        'default' => 'border-transparent bg-primary text-primary-foreground hover:bg-primary/80',
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'destructive' => 'border-transparent bg-destructive text-destructive-foreground hover:bg-destructive/80',
        'outline' => 'text-foreground',
        'indigo' => 'border-transparent bg-indigo-100 text-indigo-800 hover:bg-indigo-200',
        'green' => 'border-transparent bg-green-100 text-green-800 hover:bg-green-200',
        'yellow' => 'border-transparent bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
        'red' => 'border-transparent bg-red-100 text-red-800 hover:bg-red-200',
        'gray' => 'border-transparent bg-gray-100 text-gray-800 hover:bg-gray-200',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['default'];
    $finalClass = $baseClass . ' ' . $variantClass . ' ' . $class;
@endphp

<div class="{{ $finalClass }}">
    {{ $slot }}
</div>
