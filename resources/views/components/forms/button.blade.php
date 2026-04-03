@props([
    'type' => 'button',
    'variant' => 'primary',
    'class' => '',
])

@php
    $baseClass = 'inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';
    
    switch ($variant) {
        case 'primary':
            $buttonClass = $baseClass . ' border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500';
            break;
        case 'secondary':
            $buttonClass = $baseClass . ' border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-indigo-500';
            break;
        case 'danger':
            $buttonClass = $baseClass . ' border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500';
            break;
        default:
            $buttonClass = $baseClass . ' border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500';
    }
    
    $finalClass = $buttonClass . ' ' . $class;
@endphp

@if ($type === 'submit')
    <button type="submit" class="{{ $finalClass }}">
        {{ $slot }}
    </button>
@else
    <button type="{{ $type }}" class="{{ $finalClass }}">
        {{ $slot }}
    </button>
@endif
