@extends('layouts.app')

@section('title', 'Study')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Study Mode</h1>
                <p class="mt-2 text-lg text-gray-600">
                    Practice with your flashcards
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
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Choose Study Mode</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-indigo-500 rounded-md flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Answer a random question</h3>
                                        <p class="text-sm text-gray-500">Test yourself without seeing answers</p>
                                    </div>
                                </div>
                                <a href="{{ route('study.random') }}"
                                       class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Random question
                                </a>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-yellow-500 rounded-md flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Mastery session</h3>
                                        <p class="text-sm text-gray-500">Answer questions based on their current level of subject mastery.</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <ul class="pl-2">
                                        <li class="list-item mb-2">
                                            <a href="{{ route('study.easy') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">Fresh learning</a> - Questions you haven't seen before, or have yet to begin mastering.
                                        </li>
                                        <li class="list-item mb-2">
                                            <a href="{{ route('study.medium') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">Intermediate mastery</a> - You've answered correctly before, so you're on your way to mastering these questions.
                                        </li>
                                        <li class="list-item mb-2">
                                            <a href="{{ route('study.hard') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">High mastery</a> - Answered multiple times correctly. You're close to mastering these questions.
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
