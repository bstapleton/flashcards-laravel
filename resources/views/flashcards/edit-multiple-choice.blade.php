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
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Answer Options <span class="text-red-500">*</span>
                                    </label>
                                    <button type="button"
                                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            onclick="openAnswerModal()">
                                        + Add Answer
                                    </button>
                                </div>

                                <!-- Answers Display -->
                                <div id="answers_display" class="space-y-3">
                                    <!-- Answers will be dynamically added here -->
                                </div>

                                <!-- Hidden container for form data -->
                                <div id="answers_form_data" class="hidden">
                                    <!-- Form inputs will be dynamically added here -->
                                </div>

                                <div class="mt-3">
                                    <p class="text-sm text-gray-500">
                                        At least one answer option must be marked as correct.
                                    </p>
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

<!-- Answer Modal -->
<x-forms.answer-modal :index="'new'" />

    @push('scripts')
        <script>
            // Initialize answers from existing flashcard
            let answers = @json($flashcard->answers->toArray());
            let answerIndex = 0;

            function openAnswerModal(editIndex = null) {
                const modal = document.getElementById('answer-modal-new');
                const form = document.getElementById('answer-form-new');

                if (editIndex !== null) {
                    // Edit mode
                    const answer = answers[editIndex];
                    document.getElementById('is_correct_new').checked = answer.is_correct;
                    document.getElementById('answer_text_new').value = answer.text;
                    document.getElementById('answer_explanation_new').value = answer.explanation || '';
                    modal.dataset.editIndex = editIndex;
                } else {
                    // Create mode
                    form.reset();
                    delete modal.dataset.editIndex;
                }

                modal.classList.remove('hidden');
            }

            function closeAnswerModal(index = 'new') {
                const modal = document.getElementById('answer-modal-' + index);
                const form = document.getElementById('answer-form-' + index);
                form.reset();
                delete modal.dataset.editIndex;
                modal.classList.add('hidden');
            }

            function saveAnswer(event, index) {
                event.preventDefault();

                const modal = document.getElementById('answer-modal-' + index);
                const editIndex = modal.dataset.editIndex;

                const answer = {
                    is_correct: document.getElementById('is_correct_' + index).checked,
                    text: document.getElementById('answer_text_' + index).value.trim(),
                    explanation: document.getElementById('answer_explanation_' + index).value.trim()
                };

                if (!answer.text) {
                    alert('Answer text is required');
                    return;
                }

                if (editIndex !== undefined) {
                    // Update existing answer
                    answers[editIndex] = answer;
                } else {
                    // Add new answer
                    answers.push(answer);
                }

                updateAnswersDisplay();
                updateFormData();
                closeAnswerModal(index);
            }

            function removeAnswer(index) {
                answers.splice(index, 1);
                updateAnswersDisplay();
                updateFormData();
            }

            function updateAnswersDisplay() {
                const display = document.getElementById('answers_display');

                if (answers.length === 0) {
                    display.innerHTML = '<p class="text-sm text-gray-500 italic">No answers added yet. Click "Add Answer" to create one.</p>';
                    return;
                }

                display.innerHTML = answers.map((answer, index) => `
                    <div class="bg-white border border-gray-200 rounded-lg p-3 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    ${answer.is_correct ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Correct</span>' : ''}
                                    <span class="text-sm font-medium text-gray-900">${escapeHtml(answer.text)}</span>
                                </div>
                                ${answer.explanation ? `<p class="text-xs text-gray-600 mt-1">${escapeHtml(answer.explanation)}</p>` : ''}
                            </div>
                            <div class="flex space-x-2 ml-4">
                                <button type="button" onclick="openAnswerModal(${index})" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</button>
                                <button type="button" onclick="removeAnswer(${index})" class="text-red-600 hover:text-red-800 text-sm">Remove</button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }

            function updateFormData() {
                const formData = document.getElementById('answers_form_data');
                formData.innerHTML = answers.map((answer, index) => `
                    <input type="hidden" name="answers[${index}][text]" value="${escapeHtml(answer.text)}">
                    <input type="hidden" name="answers[${index}][explanation]" value="${escapeHtml(answer.explanation || '')}">
                    <input type="hidden" name="answers[${index}][is_correct]" value="${answer.is_correct ? '1' : '0'}">
                `).join('');
            }

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Tag cloud functionality
            // Initialize selected tags from flashcard - ensure all are strings for consistent comparison
            const selectedTags = new Set(JSON.parse(@json($selectedTags)).map(id => String(id)));

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

            // Initialize display on page load
            document.addEventListener('DOMContentLoaded', function () {
                updateAnswersDisplay();
                updateFormData();

                // Initialize hidden inputs for subjects
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
