@extends('layouts.app')

@section('title', 'Create True/False Flashcard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('flashcards.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                    ← Back to Flashcards
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Create True/False Flashcard</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Add a new true/false statement to your collection
                </p>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('flashcards.store-statement') }}" method="POST" class="space-y-6" id="flashcard-form">
                    @csrf

                    <div class="px-6 py-4">
                        <!-- Question Text -->
                        <div class="mb-6">
                            <label for="text" class="block text-sm font-medium text-gray-700">
                                Statement <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                name="text"
                                rows="3"
                                placeholder="Enter your statement here..."
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >{{ old('text') }}</textarea>
                            @error('text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Explanation (Optional) -->
                        <div class="mb-6">
                            <label for="explanation" class="block text-sm font-medium text-gray-700">
                                Explanation (Optional)
                            </label>
                            <textarea
                                name="explanation"
                                rows="2"
                                placeholder="Provide additional details or background..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >{{ old('explanation') }}</textarea>
                        </div>

                        <!-- True/False Answer -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                This statement is... <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="is_true" value="1" required class="mr-2">
                                    <span class="text-sm text-gray-700">True</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="is_true" value="0" required class="mr-2">
                                    <span class="text-sm text-gray-700">False</span>
                                </label>
                            </div>
                            @error('is_true')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subjects (Optional) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Subjects (Optional)
                            </label>
                            <div class="tag-cloud" id="tag-cloud">
                                @if(count($tags) > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($tags as $tag)
                                            <button type="button"
                                                    class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border-2 border-gray-300 text-gray-600 bg-gray-50 hover:border-gray-400 transition-colors duration-200"
                                                    data-tag-id="{{ $tag->id }}"
                                                    data-tag-classes="{{ $tag->getColorClasses() }}"
                                                    onclick="toggleTag(this)">
                                                <span class="tag-symbol mr-1">+</span>
                                                <span class="tag-name">{{ $tag->name }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500">No subjects available. <a href="{{ route('subjects.create') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">Create some subjects</a> to organize your flashcards.</p>
                                @endif
                            </div>
                            <!-- Hidden input to store selected subject IDs -->
                            <div id="selected-subjects-container"></div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                        <div class="space-x-3">
                            <button type="button"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    onclick="submitDraft()">
                                Save as Draft
                            </button>
                            <x-ui.button variant="secondary">
                                <a href="{{ route('flashcards.index') }}" class="block w-full h-full text-inherit no-underline">
                                    Cancel
                                </a>
                            </x-ui.button>
                        </div>
                        <x-ui.button type="submit">
                            Create True/False Flashcard
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tag cloud functionality
const selectedTags = new Set();

function toggleTag(element) {
    const tagId = element.dataset.tagId;
    const tagClasses = element.dataset.tagClasses;
    const symbol = element.querySelector('.tag-symbol');

    if (selectedTags.has(tagId)) {
        // Remove tag
        selectedTags.delete(tagId);
        symbol.textContent = '+';

        // Remove colour classes and restore default grey classes
        tagClasses.split(' ').forEach(cls => element.classList.remove(cls));
        element.classList.add('border-gray-300', 'text-gray-600', 'bg-gray-50');
        element.classList.remove('ring-2', 'ring-offset-2');
    } else {
        // Add tag
        selectedTags.add(tagId);
        symbol.textContent = '-';

        // Remove default grey classes and apply colour classes
        element.classList.remove('border-gray-300', 'text-gray-600', 'bg-gray-50');
        tagClasses.split(' ').forEach(cls => element.classList.add(cls));
        element.classList.add('ring-2', 'ring-offset-2');
    }

    // Update hidden inputs
    const container = document.getElementById('selected-subjects-container');
    container.innerHTML = '';

    Array.from(selectedTags).forEach(tagId => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'subjects[]';
        input.value = tagId;
        container.appendChild(input);
    });
}

function submitDraft() {
    const form = document.getElementById('flashcard-form');
    const originalAction = form.action;

    // Change form action to draft route
    form.action = '{{ route("flashcards.store-statement-draft") }}';

    // Submit the form
    form.submit();

    // Restore original action (in case submission fails)
    form.action = originalAction;
}
</script>
@endpush
@endsection
