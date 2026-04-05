@extends('layouts.app')

@section('title', 'Answer Results')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('answer.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                    ← Back to Answer home page
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Answer Results</h1>
            </div>

            @if($scorecard)
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-6 py-4">
                        <!-- Result Header -->
                        <div class="mb-6">
                            @if($scorecard->correctness->value === 'complete')
                                <div class="flex items-center">
                                    <svg class="h-8 w-8 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h2 class="text-xl font-semibold text-green-600">Correct!</h2>
                                </div>
                            @else
                                <div class="flex items-center">
                                    <svg class="h-8 w-8 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <h2 class="text-xl font-semibold text-red-600">Incorrect</h2>
                                </div>
                            @endif
                        </div>

                        <!-- Question -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Question</h3>
                            <p class="text-gray-700">{{ $scorecard->question }}</p>
                        </div>

                        <!-- Answer Results -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Your Answer(s)</h3>
                            <div class="space-y-3">
                                @foreach($scorecard->formatted_answers as $answer)
                                    <div class="p-4 border rounded-lg @if($answer->getWasSelected()) @if($answer->getIsCorrect()) border-green-300 bg-green-50 @else border-red-300 bg-red-50 @endif @else border-gray-200 @endif">
                                        <div class="flex items-center">
                                            @if($answer->getWasSelected())
                                                @if($answer->getIsCorrect())
                                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-sm font-medium text-green-700">Your answer (Correct)</span>
                                                @else
                                                    <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-sm font-medium text-red-700">Your answer (Incorrect)</span>
                                                @endif
                                            @else
                                                @if($answer->getIsCorrect())
                                                    <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-sm font-medium text-green-700">Correct answer (Not selected)</span>
                                                @else
                                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    <span class="text-sm font-medium text-gray-500">Incorrect answer (Not selected)</span>
                                                @endif
                                            @endif
                                        </div>
                                        <p class="mt-2 text-gray-700">{{ $answer->getText() }}</p>
                                        @if(method_exists($answer, 'getExplanation') && $answer->getExplanation())
                                            <p class="mt-2 text-sm text-gray-600">{{ $answer->getExplanation() }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Explanation (if available) -->
                        @if($scorecard->getExplanation())
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Explanation</h3>
                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <p class="text-gray-700">{{ $scorecard->getExplanation() }}</p>
                                </div>
                            </div>
                        @endif

                        <!-- Score and Progress -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Score & Progress</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Points earned</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($scorecard->points_earned > 0)
                                            <span class="text-green-600 font-semibold">+{{ $scorecard->points_earned }}</span>
                                        @else
                                            <span class="text-red-600 font-semibold">{{ $scorecard->points_earned }}</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Your total score</dt>
                                    <dd class="text-sm text-gray-900 font-semibold">{{ $scorecard->getTotalScore() }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Difficulty change</dt>
                                    <dd class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ strtolower($scorecard->difficulty->name) }}-100 text-{{ strtolower($scorecard->difficulty->name) }}-800">
                                            {{ $scorecard->difficulty->value }}
                                        </span>
                                        →
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ strtolower($scorecard->getNewDifficulty()->name) }}-100 text-{{ strtolower($scorecard->getNewDifficulty()->name) }}-800">
                                            {{ $scorecard->getNewDifficulty()->value }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Next available</dt>
                                    <dd class="text-sm text-gray-900">{{ $scorecard->getEligibleAt() }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between">
                            <div class="space-x-3">
                                <a href="{{ route('answer.random') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Answer another question
                                </a>
                            </div>
                            <div class="space-x-3">
                                <a href="{{ route('revision.show', $flashcard) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Review this flashcard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Scorecard not found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Unable to load the scorecard results.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('answer.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Back to Answer home page
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
