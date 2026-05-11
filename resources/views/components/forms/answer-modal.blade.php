@props([
    'index' => 0,
    'answer' => null,
    'mode' => 'create' // 'create' or 'edit'
])

<!-- Answer Modal -->
<div id="answer-modal-{{ $index }}" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeAnswerModal('{{ $index }}')"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            {{ $mode === 'edit' ? 'Edit Answer' : 'Add Answer' }}
                        </h3>
                        <div class="mt-4">
                            <form id="answer-form-{{ $index }}" onsubmit="saveAnswer(event, '{{ $index }}')">
                                <div class="space-y-4">
                                    <!-- Correct Answer Checkbox -->
                                    <div class="flex items-center">
                                        <input type="checkbox"
                                               id="is_correct_{{ $index }}"
                                               name="is_correct"
                                               {{ $answer && $answer['is_correct'] ? 'checked' : '' }}
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <label for="is_correct_{{ $index }}" class="ml-2 text-sm font-medium text-gray-700">
                                            Correct Answer
                                        </label>
                                    </div>

                                    <!-- Answer Text -->
                                    <div>
                                        <label for="answer_text_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            Answer Text <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               id="answer_text_{{ $index }}"
                                               name="text"
                                               value="{{ $answer ? $answer['text'] : '' }}"
                                               placeholder="Enter answer text..."
                                               required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>

                                    <!-- Answer Explanation -->
                                    <div>
                                        <label for="answer_explanation_{{ $index }}" class="block text-sm font-medium text-gray-700 mb-1">
                                            Explanation (Optional)
                                        </label>
                                        <textarea id="answer_explanation_{{ $index }}"
                                                  name="explanation"
                                                  rows="3"
                                                  placeholder="Provide an explanation for this answer..."
                                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ $answer ? ($answer['explanation'] ?? '') : '' }}</textarea>
                                    </div>
                                </div>

                                <!-- Modal Actions -->
                                <div class="mt-6 sm:flex sm:flex-row-reverse">
                                    <button type="submit"
                                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                        {{ $mode === 'edit' ? 'Update' : 'Add' }} Answer
                                    </button>
                                    <button type="button"
                                            onclick="closeAnswerModal('{{ $index }}')"
                                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
