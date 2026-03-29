@extends('layouts.app')

@section('title', 'Draft Flashcards')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Draft Flashcards</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Flashcards that you haven't published yet - these won't show up in the revision or answer pool.
                </p>
            </div>

            <!-- Navigation Tabs -->
            <div class="mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <a href="{{ route('flashcards.index') }}"
                           class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Active
                        </a>
                        <a href="{{ route('flashcards.fresh-learning') }}"
                           class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Fresh learning
                        </a>
                        <a href="{{ route('flashcards.intermediate-mastery') }}"
                           class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                            Intermediate mastery
                        </a>
                        <a href="{{ route('flashcards.high-mastery') }}"
                           class="py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
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
                           class="py-2 px-1 border-b-2 border-indigo-500 font-medium text-sm text-indigo-600">
                            Drafts ({{ $flashcards->total() }})
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
                                            <div class="flex-shrink-0 mr-3">
                                                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ Str::limit($flashcard->text, 100) }}
                                                </p>
                                                <div class="flex flex-row">
                                                    <span class="border rounded-md border-gray-200 inline-block px-2 py-1 text-sm text-gray-500">
                                                        Created {{ $flashcard->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('flashcards.show', $flashcard) }}"
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Complete
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
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No draft flashcards</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        All your flashcards are complete and ready to study.
                    </p>
                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('flashcards.create-statement') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Create True/False
                        </a>
                        <a href="{{ route('flashcards.create-multiple-choice') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
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
