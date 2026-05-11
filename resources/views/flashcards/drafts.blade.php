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
                                                <x-ui.icon variant="edit" class="h-5 w-5 text-yellow-400" />
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
                                        @if($flashcard->answers->count() > 0)
                                            <a href="{{ route('flashcards.edit-multiple-choice', $flashcard) }}"
                                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                Edit
                                            </a>
                                        @else
                                            <a href="{{ route('flashcards.edit-statement', $flashcard) }}"
                                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                Edit
                                            </a>
                                        @endif
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
                    <x-ui.icon variant="edit" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No draft flashcards</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        All your flashcards are complete and ready to study.
                    </p>
                    <div class="mt-6 flex space-x-3">
                        <a href="{{ route('flashcards.create-statement') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <x-ui.icon variant="check" class="w-4 h-4 mr-2" />
                            Create True/False
                        </a>
                        <a href="{{ route('flashcards.create-multiple-choice') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-ui.icon variant="clipboard" class="w-4 h-4 mr-2" />
                            Create Multiple Choice
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
