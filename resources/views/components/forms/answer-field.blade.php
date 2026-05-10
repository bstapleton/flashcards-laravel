@props([
    'index' => 0,
    'answer' => null,
    'showDelete' => false
])

<div class="answer-field" data-index="{{ $index }}">
    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
        <div class="space-y-4">
            <!-- Answer Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <input type="checkbox"
                           name="answers[{{ $index }}][is_correct]"
                           {{ $answer && $answer['is_correct'] ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label class="text-sm font-medium text-gray-700">
                        Correct Answer
                    </label>
                </div>
                @if($showDelete)
                    <button type="button"
                            onclick="removeAnswer({{ $index }})"
                            class="text-red-600 hover:text-red-800 text-sm font-medium">
                        Remove
                    </button>
                @endif
            </div>

            <!-- Answer Text -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Answer Text
                </label>
                <input type="text"
                       name="answers[{{ $index }}][text]"
                       value="{{ $answer ? $answer['text'] : '' }}"
                       placeholder="Enter answer text..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            </div>

            <!-- Answer Explanation -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Explanation (Optional)
                </label>
                <textarea name="answers[{{ $index }}][explanation]"
                          rows="2"
                          placeholder="Provide an explanation for this answer..."
                          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ $answer ? ($answer['explanation'] ?? '') : '' }}</textarea>
            </div>
        </div>
    </div>
</div>
