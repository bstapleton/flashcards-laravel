@props([
    'variant' => 'default',
    'class' => '',
])

@php
    $baseClass = 'relative w-full rounded-lg border p-4';
    
    $variants = [
        'default' => 'bg-background text-foreground',
        'destructive' => 'border-destructive/50 text-destructive dark:border-destructive [&>svg]:text-destructive',
        'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-800 [&>svg]:text-yellow-600',
        'success' => 'border-green-200 bg-green-50 text-green-800 [&>svg]:text-green-600',
        'info' => 'border-blue-200 bg-blue-50 text-blue-800 [&>svg]:text-blue-600',
    ];
    
    $variantClass = $variants[$variant] ?? $variants['default'];
    $finalClass = $baseClass . ' ' . $variantClass . ' ' . $class;
@endphp

<div class="{{ $finalClass }}" role="alert">
    {{ $slot }}
</div>
