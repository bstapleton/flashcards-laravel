@extends('layouts.app')

@section('title', 'Revision Flashcard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="px-4 py-6 sm:px-0 max-w-2xl mx-auto">
                <div class="mb-8">
                    <a href="{{ route('revision.index') }}"
                       class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                        ← Back to Revision
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">Revision Mode</h1>
                </div>

                @if ($flashcard && $flashcard->difficulty->value === 'buried')
                    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-800">
                                    <strong>Mastered Flashcard:</strong> You have answered this question correctly three consecutive times, so it is considered completely mastered. You'll need to reset it back to 'fresh learning' in order for it to shart showing up again in regular study sessions.
                                </p>
                            </div>
                        </div>
                    </div>
                @elseif ($flashcard && $flashcard->status->value === 'hidden')
                    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-md p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-800">
                                    <strong>Hidden Flashcard:</strong> You have hidden this flashcard, which will prevent it from showing up in regular study sessions.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @if($flashcard)
                    <div class="bg-white shadow sm:rounded-lg">
                        <div class="px-6 py-4">
                            <!-- Question -->
                            <div class="mb-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-2">Question</h2>
                                <p class="text-gray-700">{{ $flashcard->text }}</p>
                            </div>

                            <!-- Answers -->
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Answers</h3>
                                @if($flashcard->answers->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($flashcard->answers as $answer)
                                            <div
                                                class="flex items-center p-3 rounded-md {{ $answer->is_correct ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                                                <div class="flex-shrink-0">
                                                    @if($answer->is_correct)
                                                        <svg class="h-5 w-5 text-green-600" fill="none"
                                                             stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                    @else
                                                        <svg class="h-5 w-5 text-gray-400" fill="none"
                                                             stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium {{ $answer->is_correct ? 'text-green-800' : 'text-gray-800' }}">
                                                        {{ $answer->text }}
                                                    </p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                                        <p class="text-sm text-blue-800">
                                            This is a true/false question.
                                            @if($flashcard->is_true)
                                                The correct answer is <strong>True</strong>.
                                            @else
                                                The correct answer is <strong>False</strong>.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            </div>

                            <!-- Explanation (if exists) -->
                            @if($flashcard->explanation)
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Explanation</h3>
                                    <p class="text-gray-700">{{ $flashcard->explanation }}</p>
                                </div>
                            @endif

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
                                            {{ $flashcard->last_seen_at ? $flashcard->last_seen_at->diffForHumans() : 'Never' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <!-- Subjects (if exists) -->
                            @if($flashcard->tags->count() > 0)
                                <div class="mb-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Subjects Covered</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($flashcard->tags as $subject)
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ $subject->name }}
                                        </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-between">
                            <div class="space-x-3">
                                <a href="{{ route('answer.show', $flashcard->id) }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Attempt This
                                </a>
                                @if ($flashcard && $flashcard->difficulty->value === 'buried')
                                    <form action="{{ route('flashcards.revive', $flashcard) }}" method="POST" class="inline-flex items-center">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            Reset difficulty
                                        </button>
                                    </form>
                                @elseif ($flashcard->status->value === 'hidden')
                                    <form action="{{ route('flashcards.unhide', $flashcard) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Unhide
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('flashcards.hide', $flashcard) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            Hide
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="space-x-3">
                                @if (isset($isPooled) && $isPooled)
                                    <a href="{{ route($route) }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Revise another {{ $flashcard->mastery_text }}
                                    </a>
                                @else
                                    <a href="{{ route('revision.random') }}"
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Revise random
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Flashcard not found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            This flashcard doesn't exist or you don't have access to it.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('revision.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Back to Revision
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
