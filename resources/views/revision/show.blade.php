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
                                <x-ui.icon variant="warning" class="h-5 w-5 text-yellow-400" />
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
                                <x-ui.icon variant="warning" class="h-5 w-5 text-yellow-400" />
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
                                                        <x-ui.icon variant="check" class="h-5 w-5 text-green-600" />
                                                    @else
                                                        <x-ui.icon variant="times" class="h-5 w-5 text-gray-400" />
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
                                    <x-ui.icon variant="play" class="w-4 h-4 mr-2" />
                                    Attempt This
                                </a>
                                @if ($flashcard && $flashcard->difficulty->value === 'buried')
                                    <form action="{{ route('flashcards.revive', $flashcard) }}" method="POST" class="inline-flex items-center">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" variant="warning">
                                            Reset difficulty
                                        </x-ui.button>
                                    </form>
                                @elseif ($flashcard->status->value === 'hidden')
                                    <form action="{{ route('flashcards.unhide', $flashcard) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" variant="blue">
                                            Unhide
                                        </x-ui.button>
                                    </form>
                                @else
                                    <form action="{{ route('flashcards.hide', $flashcard) }}" method="POST" class="inline-flex">
                                        @csrf
                                        @method('PATCH')
                                        <x-ui.button type="submit" variant="warning">
                                            Hide
                                        </x-ui.button>
                                    </form>
                                @endif
                            </div>
                            <div class="space-x-3">
                                <a href="{{ isset($isPooled) && $isPooled ? route($route) : route('revision.random') }}"
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <x-ui.icon variant="random" class="w-4 h-4 mr-2" />
                                    @if (isset($isPooled) && $isPooled)
                                        Revise another {{ $flashcard->mastery_text }}
                                    @else
                                        Revise random
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-ui.icon variant="sad" class="w-12 h-12 mx-auto text-gray-400" />
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
