@props([
    'name',
    'id' => null,
    'rows' => 3,
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

<textarea 
    name="{{ $name }}" 
    id="{{ $inputId }}" 
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    class="{{ $inputClass }}"
>{{ old($name, $value) }}</textarea>

@if ($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
@endif
