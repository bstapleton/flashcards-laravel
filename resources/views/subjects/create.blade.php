@extends('layouts.app')

@section('title', 'Create Tag')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Create a Subject</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Subjects help you categorize and organize your flashcards for better study sessions.
                </p>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('subjects.store') }}" method="POST">
                    @csrf
                    <div class="px-6 py-4 space-y-6">
                        @if ($errors->any())
                            <div class="rounded-md bg-red-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Subject Name</label>
                            <div class="mt-1">
                                <x-forms.input
                                    name="name"
                                    placeholder="e.g., Mathematics, Science, History"
                                    required
                                    :error="$errors->first('name')"
                                />
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Choose a descriptive name for your subject.</p>
                        </div>

                        <div>
                            <label for="colour" class="block text-sm font-medium text-gray-700">Color</label>
                            <div class="mt-1">
                                <input type="hidden" name="colour" id="colour" value="{{ old('colour') }}" required>
                                <div class="grid grid-cols-5 gap-3">
                                    @foreach($colours as $colour)
                                        <button type="button"
                                                onclick="selectColour({{ $colour->value }})"
                                                id="colour-{{ $colour->value }}"
                                                class="colour-button relative h-12 rounded-lg border-2 transition-all duration-200
                                                @switch($colour->name)
                                                    @case('ORANGE')
                                                        bg-orange-500 border-orange-300 hover:border-orange-400
                                                        @break
                                                    @case('YELLOW')
                                                        bg-yellow-500 border-yellow-300 hover:border-yellow-400
                                                        @break
                                                    @case('GREEN')
                                                        bg-green-500 border-green-300 hover:border-green-400
                                                        @break
                                                    @case('TEAL')
                                                        bg-teal-500 border-teal-300 hover:border-teal-400
                                                        @break
                                                    @case('CYAN')
                                                        bg-cyan-500 border-cyan-300 hover:border-cyan-400
                                                        @break
                                                    @case('BLUE')
                                                        bg-blue-500 border-blue-300 hover:border-blue-400
                                                        @break
                                                    @case('INDIGO')
                                                        bg-indigo-500 border-indigo-300 hover:border-indigo-400
                                                        @break
                                                    @case('PURPLE')
                                                        bg-purple-500 border-purple-300 hover:border-purple-400
                                                        @break
                                                    @case('PINK')
                                                        bg-pink-500 border-pink-300 hover:border-pink-400
                                                        @break
                                                    @case('RED')
                                                        bg-red-500 border-red-300 hover:border-red-400
                                                        @break
                                                    @default
                                                        bg-gray-500 border-gray-300 hover:border-gray-400
                                                @endswitch
                                                {{ old('colour') == $colour->value ? ' ring-2 ring-offset-2 ring-indigo-500' : '' }}">
                                            <span class="absolute inset-0 flex items-center justify-center text-white font-medium text-sm">
                                                {{ ucfirst(strtolower($colour->name)) }}
                                            </span>
                                            @if(old('colour') == $colour->value)
                                                <span class="absolute top-1 right-1">
                                                    <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </span>
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Choose a colour to help visually distinguish your subjects.</p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-right">
                        <a href="{{ route('subjects.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <x-forms.button type="submit" class="ml-3">
                            Create Subject
                        </x-forms.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function selectColour(value) {
    // Update hidden input
    document.getElementById('colour').value = value;

    // Update button states
    document.querySelectorAll('.colour-button').forEach(button => {
        // Remove ring from all buttons
        button.classList.remove('ring-2', 'ring-offset-2', 'ring-indigo-500');

        // Remove checkmark from all buttons
        const checkmark = button.querySelector('svg');
        if (checkmark) {
            checkmark.remove();
        }
    });

    // Add ring and checkmark to selected button
    const selectedButton = document.getElementById('colour-' + value);
    selectedButton.classList.add('ring-2', 'ring-offset-2', 'ring-indigo-500');

    // Add checkmark
    const checkmark = document.createElement('span');
    checkmark.className = 'absolute top-1 right-1';
    checkmark.innerHTML = '<svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>';
    selectedButton.appendChild(checkmark);
}

// Initialize with default selection if form has old input
document.addEventListener('DOMContentLoaded', function() {
    const currentValue = document.getElementById('colour').value;
    if (currentValue) {
        selectColour(currentValue);
    }
});
</script>
@endsection
