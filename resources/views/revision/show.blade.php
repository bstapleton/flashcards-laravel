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

            @if($flashcard)
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-6 py-4">
                        <!-- Question -->
                        <div class="mb-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-2">Question</h2>
                            <p class="text-gray-700">{{ $flashcard->text }}</p>
                        </div>

                        <!-- Explanation (if exists) -->
                        @if($flashcard->explanation)
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Explanation</h3>
                                <p class="text-gray-700">{{ $flashcard->explanation }}</p>
                            </div>
                        @endif

                        <!-- Answers -->
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Answers</h3>
                            @if($flashcard->answers->count() > 0)
                                <div class="space-y-2">
                                    @foreach($flashcard->answers as $answer)
                                        <div class="flex items-center p-3 rounded-md {{ $answer->is_correct ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }}">
                                            <div class="flex-shrink-0">
                                                @if($answer->is_correct)
                                                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                @else
                                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
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

                        <!-- Tags (if exists) -->
                        @if($flashcard->tags->count() > 0)
                            <div class="mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Tags</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($flashcard->tags as $tag)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
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
                            <a href="{{ route('answer.show', $flashcard->id) }}"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Attempt This
                            </a>
                        </div>
                        <div class="space-x-3">
                            @if (isset($isPooled) && $isPooled)
                                <a href="{{ route($route) }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Revise another {{ $flashcard->mastery_text }}
                                </a>
                            @else
                                <a href="{{ route('revision.random') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Revise random
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
