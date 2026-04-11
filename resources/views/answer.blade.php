@extends('layouts.app')

@section('title', 'answer')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Answer mode</h1>
                <p class="mt-2 text-lg text-gray-600">
                    Test your knowledge
                </p>
            </div>

            @if(isset($error))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="text-sm text-red-600">
                        {{ $error }}
                    </div>
                </div>
            @endif

            <!-- Study Options -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-6 py-4">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Choose Answer Mode</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-indigo-500 rounded-md flex items-center justify-center">
                                            <x-ui.icon variant="play" class="w-6 h-6 text-white" />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Answer a random question</h3>
                                        <p class="text-sm text-gray-500">Answer a random question from your active flashcard pool.</p>
                                    </div>
                                </div>
                                <a href="{{ route('answer.random') }}"
                                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Random question
                                </a>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-yellow-500 rounded-md flex items-center justify-center">
                                            <x-ui.icon variant="lightning" class="w-6 h-6 text-white" />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Mastery session</h3>
                                        <p class="text-sm text-gray-500">Answer random questions based on their current level of subject mastery.</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <ul class="pl-2">
                                        <li class="list-item mb-2">
                                            <a href="{{ route('answer.fresh-learning') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">Fresh learning</a> - Questions you haven't seen before, or have yet to begin mastering.
                                        </li>
                                        <li class="list-item mb-2">
                                            <a href="{{ route('answer.intermediate-mastery') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">Intermediate mastery</a> - You've answered correctly before, so you're on your way to mastering these questions.
                                        </li>
                                        <li class="list-item mb-2">
                                            <a href="{{ route('answer.high-mastery') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">High mastery</a> - Answered multiple times correctly. You're close to mastering these questions.
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function startStudySession() {
    // This would typically start a study session
    alert('Study session feature coming soon! Try Random Flashcard for now.');
}

function practiceByDifficulty(difficulty) {
    // This would filter flashcards by difficulty
    alert(`Practice by ${difficulty} difficulty coming soon! Try Random Flashcard for now.`);
}
</script>
@endpush
@endsection
