@extends('layouts.app')

@section('title', 'Create Flashcard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Create Flashcard</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Add a new question to your collection
                </p>
            </div>

            <div class="bg-white shadow sm:rounded-lg">
                <form action="{{ route('api.flashcards.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Question Type Selection -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Question Type</label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="question_type" value="true_false" checked
                                       class="mr-2" onchange="toggleQuestionType('true_false')">
                                <span class="text-sm text-gray-700">True/False</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="question_type" value="multiple_choice"
                                       class="mr-2" onchange="toggleQuestionType('multiple_choice')">
                                <span class="text-sm text-gray-700">Multiple Choice</span>
                            </label>
                        </div>
                    </div>

                    <div class="px-6 py-4">
                        <!-- Question Text -->
                        <div class="mb-6">
                            <label for="text" class="block text-sm font-medium text-gray-700">
                                Question <span class="text-red-500">*</span>
                            </label>
                            <x-forms.textarea 
                                name="text" 
                                rows="3"
                                placeholder="Enter your question here..."
                                required
                                :error="$errors->first('text')"
                            />
                            @error('text')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Explanation (Optional) -->
                        <div class="mb-6">
                            <label for="explanation" class="block text-sm font-medium text-gray-700">
                                Explanation (Optional)
                            </label>
                            <x-forms.textarea 
                                name="explanation" 
                                rows="2"
                                placeholder="Provide an explanation for the correct answer..."
                                :error="$errors->first('explanation')"
                            />
                        </div>

                        <!-- True/False Answer -->
                        <div id="true_false_answer" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Is the statement true? <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="is_true" value="1" class="mr-2">
                                    <span class="text-sm text-gray-700">Yes, the statement is true</span>
                                </label>
                            </div>
                        </div>

                        <!-- Multiple Choice Answers -->
                        <div id="multiple_choice_answers" class="mb-6 hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Answer Options <span class="text-red-500">*</span>
                            </label>
                            <div class="space-y-2" id="answers_container">
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="answers[0][is_correct]" class="mr-2">
                                    <x-forms.input 
                                        name="answers[0][text]" 
                                        placeholder="Answer option 1"
                                        class="flex-1"
                                    />
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="answers[1][is_correct]" class="mr-2">
                                    <x-forms.input 
                                        name="answers[1][text]" 
                                        placeholder="Answer option 2"
                                        class="flex-1"
                                    />
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="answers[2][is_correct]" class="mr-2">
                                    <x-forms.input 
                                        name="answers[2][text]" 
                                        placeholder="Answer option 3"
                                        class="flex-1"
                                    />
                                </div>
                                <div class="flex items-center space-x-2">
                                    <input type="checkbox" name="answers[3][is_correct]" class="mr-2">
                                    <x-forms.input 
                                        name="answers[3][text]" 
                                        placeholder="Answer option 4"
                                        class="flex-1"
                                    />
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                Mark all correct answers. At least one option must be correct.
                            </p>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <x-forms.button variant="secondary">
                            Cancel
                        </x-forms.button>
                        <x-forms.button type="submit">
                            Create Flashcard
                        </x-forms.button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleQuestionType(type) {
    const trueFalseDiv = document.getElementById('true_false_answer');
    const multipleChoiceDiv = document.getElementById('multiple_choice_answers');

    if (type === 'true_false') {
        trueFalseDiv.classList.remove('hidden');
        multipleChoiceDiv.classList.add('hidden');
    } else {
        trueFalseDiv.classList.add('hidden');
        multipleChoiceDiv.classList.remove('hidden');
    }
}
</script>
@endpush
@endsection
