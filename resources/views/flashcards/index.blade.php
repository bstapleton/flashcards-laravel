@php use App\Enums\QuestionType; @endphp
@extends('layouts.app')

@section('title', 'My Flashcards')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">My Flashcards</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage your flashcard collection
                        </p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('flashcards.create-statement') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            True/False
                        </a>
                        <a href="{{ route('flashcards.create-multiple-choice') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            Multiple Choice
                        </a>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="mb-6">
                    <div class="border-b border-gray-200">
                        <nav class="-mb-px flex space-x-8">
                            <a href="{{ route('flashcards.index') }}"
                               class="py-2 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600">
                                Active ({{ $flashcards->total() }})
                            </a>
                            <a href="{{ route('flashcards.fresh-learning') }}"
                               class="hidden md:inline-block py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Fresh learning
                            </a>
                            <a href="{{ route('flashcards.intermediate-mastery') }}"
                               class="hidden md:inline-block py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Intermediate mastery
                            </a>
                            <a href="{{ route('flashcards.high-mastery') }}"
                               class="hidden md:inline-block py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                High mastery
                            </a>
                            <a href="{{ route('flashcards.completely-mastered') }}"
                               class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Completely mastered
                            </a>
                            <a href="{{ route('flashcards.hidden') }}"
                               class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Hidden
                            </a>
                            <a href="{{ route('flashcards.drafts') }}"
                               class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                                Drafts
                            </a>
                        </nav>
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
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900 pb-1">
                                                        <strong>{{ $flashcard->type === QuestionType::STATEMENT ? 'Statement' : 'Multiple-choice' }}
                                                            :</strong> {{ Str::limit($flashcard->text, 100) }}
                                                    </p>
                                                    <div class="flex flex-row">
                                                        <span class="border rounded-l-md border-gray-200 border-r-0 inline-block px-2 py-1 text-sm text-gray-500">
                                                            Revised: {{ $flashcard->last_seen_at ? $flashcard->last_seen_at->diffForHumans() : 'never' }}
                                                        </span>
                                                        <span class="border border-y-1 border-gray-200 inline-block px-2 py-1 text-sm text-gray-500">
                                                            Attempted: {{ $flashcard->last_attempted_at ? $flashcard->last_attempted_at->diffForHumans() : 'never' }}
                                                        </span>
                                                        <span class="border rounded-r-md border-gray-200 border-l-0 inline-block px-2 py-1 text-sm text-gray-500">
                                                            {{ $flashcard->mastery_title }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('revision.show', $flashcard) }}"
                                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                Revise
                                            </a>
                                            <a href="{{ route('answer.show', $flashcard) }}"
                                               class="text-green-600 hover:text-green-900 text-sm font-medium">
                                                Attempt
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
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No flashcards</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Get started by creating a new flashcard.
                        </p>
                        <div class="mt-6 flex space-x-3">
                            <a href="{{ route('flashcards.create-statement') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Create True/False
                            </a>
                            <a href="{{ route('flashcards.create-multiple-choice') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Create Multiple Choice
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
