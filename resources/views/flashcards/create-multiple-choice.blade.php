@extends('layouts.app')

@section('title', 'Create Multiple Choice Flashcard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('flashcards.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                    ← Back to Flashcards
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Create Multiple Choice Flashcard</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Add a new multiple choice question to your collection
                </p>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('api.flashcards.store-multiple-choice') }}" method="POST" class="space-y-6">
                    @csrf

                    <div class="px-6 py-4">
                        <!-- Question Text -->
                        <div class="mb-6">
                            <label for="text" class="block text-sm font-medium text-gray-700">
                                Question <span class="text-red-500">*</span>
                            </label>
                            <textarea id="text" name="text" rows="3" required
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                      placeholder="Enter your question here...">{{ old('text') }}</textarea>
                            @error('text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Explanation (Optional) -->
                        <div class="mb-6">
                            <label for="explanation" class="block text-sm font-medium text-gray-700">
                                Explanation (Optional)
                            </label>
                            <textarea id="explanation" name="explanation" rows="2"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                      placeholder="Provide an explanation for the correct answer...">{{ old('explanation') }}</textarea>
                        </div>

                        <!-- Multiple Choice Answers -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Answer Options <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2" id="answers_container">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="answers[0][is_correct]" class="mr-2">
                                    <input type="text" name="answers[0][text]" placeholder="Answer option 1"
                                           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="answers[1][is_correct]" class="mr-2">
                                    <input type="text" name="answers[1][text]" placeholder="Answer option 2"
                                           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="answers[2][is_correct]" class="mr-2">
                                    <input type="text" name="answers[2][text]" placeholder="Answer option 3"
                                           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="answers[3][is_correct]" class="mr-2">
                                    <input type="text" name="answers[3][text]" placeholder="Answer option 4"
                                           class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                            <div class="mt-3 flex justify-between items-center">
                                <p class="text-sm text-gray-500">
                                    Mark all correct answers. At least one option must be correct.
                                </p>
                                <button type="button" onclick="addAnswerOption()"
                                        class="px-3 py-1 text-sm border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    + Add Option
                                </button>
                            </div>
                        </div>

                        <!-- Tags (Optional) -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Tags (Optional)
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
                                    <p class="text-sm text-gray-500">No tags available. Create some tags to organize your flashcards.</p>
                                @endif
                            </div>
                            <!-- Hidden input to store selected tag IDs -->
                            <input type="hidden" name="tags[]" id="selected-tags" value="">
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <a href="{{ route('flashcards.index') }}"
                           class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Create Multiple Choice Flashcard
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let answerCount = 4;

function addAnswerOption() {
    answerCount++;
    const container = document.getElementById('answers_container');
    const newOption = document.createElement('div');
    newOption.className = 'flex items-center space-x-2';
    newOption.innerHTML = `
        <input type="checkbox" name="answers[${answerCount - 1}][is_correct]" class="mr-2">
        <input type="text" name="answers[${answerCount - 1}][text]" placeholder="Answer option ${answerCount}"
               class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
    `;
    container.appendChild(newOption);
}

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
    
    // Update hidden input
    document.getElementById('selected-tags').value = Array.from(selectedTags).join(',');
}
</script>
@endpush
@endsection
