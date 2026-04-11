@props([
    'title' => '',
    'tagline' => '',
])

<div>
    <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
    <p class="mt-1 text-sm text-gray-600">
        {{ $tagline }}
    </p>
</div>
