@extends('layouts.app')

@section('title', 'Attempt Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('attempts.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                    ← Back to attempt history
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Attempt Details</h1>
            </div>

            @if($attempt)
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-6 py-4">
                        <!-- Result Header -->
                        <div class="mb-6">
                            @if($attempt->correctness->value === 'complete')
                                <div class="flex items-center">
                                    <x-ui.icon variant="check" class="h-8 w-8 text-green-500 mr-3" />
                                    <h2 class="text-xl font-semibold text-green-600">Correct!</h2>
                                </div>
                            @else
                                <div class="flex items-center">
                                    <x-ui.icon variant="times" class="h-8 w-8 text-red-500 mr-3" />
                                    <h2 class="text-xl font-semibold text-red-600">Incorrect</h2>
                                </div>
                            @endif
                        </div>

                        <!-- Question -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Question</h3>
                            <p class="text-gray-700">{{ $attempt->question }}</p>
                        </div>

                        <!-- Answer Results -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Your Answer(s)</h3>
                            <div class="space-y-3">
                                @foreach($attempt->formatted_answers as $answer)
                                    <div class="p-4 border rounded-lg @if($answer->getWasSelected()) @if($answer->getIsCorrect()) border-green-300 bg-green-50 @else border-red-300 bg-red-50 @endif @else border-gray-200 @endif">
                                        <div class="flex items-center">
                                            @if($answer->getWasSelected())
                                                @if($answer->getIsCorrect())
                                                    <x-ui.icon variant="check" class="h-5 w-5 text-green-500 mr-2" />
                                                    <span class="text-sm font-medium text-green-700">Your answer (Correct)</span>
                                                @else
                                                    <x-ui.icon variant="times" class="h-5 w-5 text-red-500 mr-2" />
                                                    <span class="text-sm font-medium text-red-700">Your answer (Incorrect)</span>
                                                @endif
                                            @else
                                                @if($answer->getIsCorrect())
                                                    <x-ui.icon variant="check" class="h-5 w-5 text-green-500 mr-2" />
                                                    <span class="text-sm font-medium text-green-700">Correct answer (Not selected)</span>
                                                @else
                                                    <x-ui.icon variant="check" class="h-5 w-5 text-gray-400 mr-2" />
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

                        <!-- Metadata -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Attempt Details</h3>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Points earned</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($attempt->points_earned > 0)
                                            <span class="text-green-600 font-semibold">+{{ $attempt->points_earned }}</span>
                                        @else
                                            <span class="text-red-600 font-semibold">{{ $attempt->points_earned }}</span>
                                        @endif
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Question type</dt>
                                    <dd class="text-sm text-gray-900">{{ $attempt->question_type }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Difficulty (when attempted)</dt>
                                    <dd class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ strtolower($attempt->difficulty->name) }}-100 text-{{ strtolower($attempt->difficulty->name) }}-800">
                                            {{ $attempt->difficulty->value }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Answered at</dt>
                                    <dd class="text-sm text-gray-900">{{ $attempt->answered_at->format('M j, Y \a\t g:i A') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Subjects (if exists) -->
                        @if($attempt->tags)
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Tags</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(explode(',', $attempt->tags) as $subject)
                                        @if(trim($subject))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ trim($subject) }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex justify-between">
                            <div class="space-x-3">
                                <a href="{{ route('answer.random') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <x-ui.icon variant="random" class="w-4 h-4 mr-2" />
                                    Answer another question
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <x-ui.icon variant="sad" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Attempt not found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        This attempt doesn't exist or you don't have access to it.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('attempts.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Back to attempt history
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
