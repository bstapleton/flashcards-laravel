@php use App\Enums\Status; @endphp
@extends('layouts.app')

@section('title', 'Edit Multiple Choice Flashcard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
                <div class="mb-8">
                    <a href="{{ route('revision.show', $flashcard) }}"
                       class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                        ← Back to Flashcard
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Multiple Choice Flashcard</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Update your multiple choice question
                    </p>
                </div>

                <div class="bg-white shadow sm:rounded-lg">
                    <form action="{{ route('flashcards.update-multiple-choice', $flashcard) }}" method="POST"
                          class="space-y-6" id="flashcard-form">
                        @csrf
                        @method('PATCH')

                        <div class="px-6 py-4">
                            <!-- Question Text -->
                            <div class="mb-6">
                                <label for="text" class="block text-sm font-medium text-gray-700">
                                    Question <span class="text-red-500">*</span>
                                </label>
                                <textarea
                                    name="text"
                                    rows="3"
                                    placeholder="Enter your question here..."
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
                                    placeholder="Provide an explanation for the correct answer..."
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                >{{ old('explanation', $flashcard->explanation) }}</textarea>
                            </div>

                            <!-- Multiple Choice Answers -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Answer Options <span class="text-red-500">*</span>
                                </label>
                                <div class="space-y-2" id="answers_container">
                                    @php
                                        $answers = $flashcard->answers->toArray();
                                        $answerCount = count($answers);
                                    @endphp
                                    @foreach($answers as $index => $answer)
                                        <div class="flex items-center space-x-2">
                                            <input type="checkbox" name="answers[{{ $index }}][is_correct]"
                                                   class="mr-2" {{ $answer['is_correct'] ? 'checked' : '' }}>
                                            <input type="text"
                                                   name="answers[{{ $index }}][text]"
                                                   placeholder="Answer option {{ $index + 1 }}"
                                                   value="{{ old('answers.' . $index . '.text', $answer['text']) }}"
                                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                    @endforeach
                                    @if($answerCount < 4)
                                        @for($i = $answerCount; $i < 4; $i++)
                                            <div class="flex items-center space-x-2">
                                                <input type="checkbox" name="answers[{{ $i }}][is_correct]"
                                                       class="mr-2">
                                                <input type="text"
                                                       name="answers[{{ $i }}][text]"
                                                       placeholder="Answer option {{ $i + 1 }}"
                                                       value="{{ old('answers.' . $i . '.text') }}"
                                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            </div>
                                        @endfor
                                    @endif
                                </div>
                                <div class="mt-3 flex justify-between items-center">
                                    <p class="text-sm text-gray-500">
                                        Mark all correct answers. At least one option must be correct.
                                    </p>
                                    <button type="button"
                                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            onclick="addAnswerOption()">
                                        + Add Option
                                    </button>
                                </div>
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
                                <x-forms.button variant="secondary">
                                    <a href="{{ $flashcard->status === Status::DRAFT ? route('flashcards.drafts') : route('revision.show', $flashcard) }}"
                                       class="block w-full h-full text-inherit no-underline">
                                        Cancel
                                    </a>
                                </x-forms.button>
                            </div>
                            <div class="space-x-3">
                                @if($flashcard->status === Status::DRAFT)
                                    <button type="button"
                                            class="px-4 py-2 border border-green-600 rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                            onclick="submitPublished()">
                                        Update & Publish
                                    </button>
                                @endif
                                <x-forms.button type="submit">
                                    Update Flashcard
                                </x-forms.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let answerCount = {{ max(4, $answerCount) }};

            function addAnswerOption() {
                answerCount++;
                const container = document.getElementById('answers_container');
                const newOption = document.createElement('div');
                newOption.className = 'flex items-center space-x-2';
                newOption.innerHTML = `
        <input type="checkbox" name="answers[${answerCount - 1}][is_correct]" class="mr-2">
        <input type="text" name="answers[${answerCount - 1}][text]" placeholder="Answer option ${answerCount}"
               class="flex-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
    `;
                container.appendChild(newOption);
            }

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
                form.action = '{{ route("flashcards.update-multiple-choice", $flashcard) }}';

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
                form.action = '{{ route("flashcards.update-multiple-choice", $flashcard) }}';

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
