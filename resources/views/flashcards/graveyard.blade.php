@extends('layouts.app')

@section('title', 'Completely mastered Flashcards')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Completely mastered Flashcards</h1>
                <p class="mt-1 text-sm text-gray-600">
                    You've answered correctly enough times to have mastered these questions.
                </p>
            </div>

            <!-- Navigation Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    @include('partials._flashcard-nav')
                </div>
            </div>

            <!-- Flashcards List -->
            @if($flashcards->count() > 0)
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        @foreach($flashcards as $flashcard)
                            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 mr-3">
                                                <x-ui.icon variant="minus" class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ Str::limit($flashcard->text, 100) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <form method="POST" action="{{ route('flashcards.revive', $flashcard) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="text-green-600 hover:text-green-900 text-sm font-medium">
                                                Reset to fresh learning
                                            </button>
                                        </form>
                                        <a href="{{ route('revision.show', $flashcard) }}"
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Revise
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Pagination -->
                @if($flashcards->hasPages())
                    <div class="mt-6">
                        {{ $flashcards->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-12">
                    <x-ui.icon variant="minus" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No completely mastered flashcards</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        You haven't mastered any flashcards yet. Keep studying!
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('answer.index') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Start Studying
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
