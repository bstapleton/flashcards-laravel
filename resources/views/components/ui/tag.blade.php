@props([
    'colour' => null,
    'class' => '',
])

@php
    $baseClass = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium';
    
    $colourVariants = [
        'ORANGE' => 'bg-orange-100 text-orange-800',
        'YELLOW' => 'bg-yellow-100 text-yellow-800',
        'GREEN' => 'bg-green-100 text-green-800',
        'TEAL' => 'bg-teal-100 text-teal-800',
        'CYAN' => 'bg-cyan-100 text-cyan-800',
        'BLUE' => 'bg-blue-100 text-blue-800',
        'INDIGO' => 'bg-indigo-100 text-indigo-800',
        'PURPLE' => 'bg-purple-100 text-purple-800',
        'PINK' => 'bg-pink-100 text-pink-800',
        'RED' => 'bg-red-100 text-red-800',
    ];
    
    // Handle both enum object and string
    $colourName = is_object($colour) ? $colour->name : $colour;
    $variantClass = $colourVariants[$colourName] ?? 'bg-gray-100 text-gray-800';
    $finalClass = $baseClass . ' ' . $variantClass . ' ' . $class;
@endphp

<span class="{{ $finalClass }}">
    {{ $slot }}
</span>
