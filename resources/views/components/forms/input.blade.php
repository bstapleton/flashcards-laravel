@props([
    'name',
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'class' => '',
    'error' => null,
])

@php
    $inputId = $id ?? $name;
    $inputClass = 'shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 border rounded-md p-2 ' . $class;
    if ($error) {
        $inputClass .= ' border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500';
    }
@endphp

<input 
    type="{{ $type }}" 
    name="{{ $name }}" 
    id="{{ $inputId }}" 
    value="{{ old($name, $value) }}" 
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    class="{{ $inputClass }}"
>

@if ($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
@endif
