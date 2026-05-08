@php use App\Enums\Status; @endphp
@extends('layouts.app')

@section('title', 'Edit True/False Flashcard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
                <div class="mb-8">
                    <a href="{{ route('revision.show', $flashcard) }}"
                       class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                        ← Back to Flashcard
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Edit True/False Flashcard</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Update your true/false statement
                    </p>
                </div>

                <div class="bg-white shadow sm:rounded-lg">
                    <form action="{{ route('flashcards.update-statement', $flashcard) }}" method="POST"
                          class="space-y-6" id="flashcard-form">
                        @csrf
                        @method('PATCH')

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
                                >{{ old('text', $flashcard->text) }}</textarea>
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
                                >{{ old('explanation', $flashcard->explanation) }}</textarea>
                            </div>

                            <!-- True/False Answer -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    This statement is... <span class="text-red-500">*</span>
                                </label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="is_true" value="1" required
                                               class="mr-2" {{ old('is_true', $flashcard->is_true) == 1 ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">True</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="is_true" value="0" required
                                               class="mr-2" {{ old('is_true', $flashcard->is_true) == 0 ? 'checked' : '' }}>
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
                                                @if (in_array($tag->id, $selectedTags))
                                                    <button type="button"
                                                            class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border-2 {{ $tag->getColorClasses() }} ring-2 ring-offset-2 hover:border-gray-400 transition-colors duration-200"
                                                            data-tag-id="{{ $tag->id }}"
                                                            data-tag-classes="{{ $tag->getColorClasses() }}"
                                                            onclick="toggleTag(this)">
                                                        <span class="tag-symbol mr-1">-</span>
                                                        <span class="tag-name">{{ $tag->name }}</span>
                                                    </button>
                                                @else
                                                    <button type="button"
                                                            class="tag-item inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border-2 border-gray-300 text-gray-600 bg-gray-50 hover:border-gray-400 transition-colors duration-200"
                                                            data-tag-id="{{ $tag->id }}"
                                                            data-tag-classes="{{ $tag->getColorClasses() }}"
                                                            onclick="toggleTag(this)">
                                                        <span class="tag-symbol mr-1">+</span>
                                                        <span class="tag-name">{{ $tag->name }}</span>
                                                    </button>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-500">No subjects available. <a
                                                href="{{ route('subjects.create') }}"
                                                class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">Create
                                                some subjects</a> to organize your flashcards.</p>
                                    @endif
                                </div>
                                <!-- Hidden input to store selected subject IDs -->
                                <div id="selected-subjects-container"></div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                            <div class="space-x-3">
                                <x-ui.button variant="secondary">
                                    <a href="{{ $flashcard->status === Status::DRAFT ? route('flashcards.drafts') : route('revision.show', $flashcard) }}"
                                       class="block w-full h-full text-inherit no-underline">
                                        Cancel
                                    </a>
                                </x-ui.button>
                            </div>
                            <div class="space-x-3">
                                @if($flashcard->status === Status::DRAFT)
                                    <button type="button"
                                            class="px-4 py-2 border border-green-600 rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                            onclick="submitPublished()">
                                        Update & Publish
                                    </button>
                                @endif
                                <x-ui.button type="submit">
                                    Update Flashcard
                                </x-ui.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Tag cloud functionality
            // Initialize selected tags from flashcard - ensure all are strings for consistent comparison
            const selectedTags = new Set(@json($selectedTags).map(id => String(id)));

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

                const selectedTagsArray = Array.from(selectedTags);

                selectedTagsArray.forEach(tagId => {
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
                form.action = '{{ route("flashcards.update-statement", $flashcard) }}';

                // Submit the form
                form.submit();

                // Restore original action (in case submission fails)
                form.action = originalAction;
            }

            function submitPublished() {
                const form = document.getElementById('flashcard-form');
                const originalAction = form.action;
                const originalMethod = form.method;

                // First update the flashcard, then publish it
                // We'll submit to the update route but with a flag to publish after
                form.action = '{{ route("flashcards.update-statement", $flashcard) }}';

                // Add a hidden input to indicate we want to publish after update
                const publishInput = document.createElement('input');
                publishInput.type = 'hidden';
                publishInput.name = 'publish_after_update';
                publishInput.value = '1';
                form.appendChild(publishInput);

                // Submit the form
                form.submit();

                // Restore original values (in case submission fails)
                form.action = originalAction;
                form.method = originalMethod;
            }

            // Initialize hidden inputs on page load
            document.addEventListener('DOMContentLoaded', function () {
                const container = document.getElementById('selected-subjects-container');
                Array.from(selectedTags).forEach(tagId => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'subjects[]';
                    input.value = tagId;
                    container.appendChild(input);
                });
            });
        </script>
    @endpush
@endsection
