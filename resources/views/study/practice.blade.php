@extends('layouts.app')

@section('title', 'Test your knowledge')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('answer.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                    ← Back to Study
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Test your knowledge</h1>
            </div>

            @if($flashcard)
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-6 py-4">
                        <!-- Question -->
                        <div class="mb-6">
                            @if($flashcard->type->value === 'statement')
                                <h2 class="text-lg font-medium text-gray-900 mb-2">Is the following true or false?</h2>
                            @else
                                <h2 class="text-lg font-medium text-gray-900 mb-2">Question</h2>
                            @endif
                            <p class="text-gray-700">{{ $flashcard->text }}</p>
                        </div>

                        <!-- Answer Options -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Choose Your Answer</h3>
                            @if($flashcard->answers->count() > 0)
                                <form method="POST" action="{{ route('api.flashcards.answer', $flashcard) }}" id="answerForm">
                                    @csrf
                                    <div class="space-y-3">
                                        @foreach($flashcard->answers as $index => $answer)
                                            <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                                <input type="checkbox"
                                                       name="answers[]"
                                                       value="{{ $answer->id }}"
                                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">
                                                        {{ $answer->text }}
                                                    </p>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>

                                    <div class="mt-6">
                                        <button type="submit"
                                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Submit Answer
                                        </button>
                                    </div>
                                </form>
                            @else
                                <!-- True/False Question -->
                                <form method="POST" action="{{ route('api.flashcards.answer', $flashcard) }}" id="answerForm">
                                    @csrf
                                    <div class="space-y-3">
                                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                            <input type="radio"
                                                   name="is_true"
                                                   value="1"
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    True
                                                </p>
                                            </div>
                                        </label>
                                        <label class="flex items-center p-4 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                            <input type="radio"
                                                   name="is_true"
                                                   value="0"
                                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    False
                                                </p>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="mt-6">
                                        <button type="submit"
                                                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Submit Answer
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        <!-- Metadata -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Details</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Current mastery level</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $flashcard->mastery_text }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $flashcard->created_at->format('M j, Y') }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last attempted</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $flashcard->last_attempted_at ? $flashcard->last_attempted_at->diffForHumans() : 'Never' }}
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last revised</dt>
                                    <dd class="text-sm text-gray-900">
                                        {{ $flashcard->last_seen_at ? $flashcard->last_seen_at->diffForHumans() : 'Never' }} - <a class="underline text-indigo-600 hover:text-gray-600 hover:no-underline" href="{{ route('revision.show', $flashcard) }}">review now</a>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Tags (if exists) -->
                        @if($flashcard->tags->count() > 0)
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Tags</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($flashcard->tags as $tag)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ strtolower($tag->colour->name) }}-100 text-{{ strtolower($tag->colour->name) }}-800">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                        <div class="space-x-3">
                            <form method="GET" action="{{ route('revision.random') }}" class="inline">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Review Answer
                                </button>
                            </form>
                        </div>
                        <div class="space-x-3">
                            @if (isset($isPooled) && $isPooled)
                                <a href="{{ route($route) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Answer another {{ $flashcard->mastery_text }}
                                </a>
                            @else
                                <a href="{{ route('revision.random') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Answer random
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Flashcard not found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        This flashcard doesn't exist or you don't have access to it.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('answer.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Back to Study
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('answerForm').addEventListener('submit', function(e) {
    // For multiple choice, ensure at least one answer is selected
    const checkboxes = document.querySelectorAll('input[name="answers[]"]:checked');
    const radioButtons = document.querySelectorAll('input[name="is_true"]:checked');

    if (checkboxes.length === 0 && radioButtons.length === 0) {
        e.preventDefault();
        alert('Please select an answer before submitting.');
        return false;
    }
});
</script>
@endpush
@endsection
