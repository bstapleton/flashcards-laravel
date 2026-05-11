@php use App\Enums\QuestionType; @endphp
@extends('layouts.app')

@section('title', 'My Flashcards')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-8 flex justify-between items-center">
                    <x-ui.page-title title="My Flashcards" tagline="Manage your flashcard collection" />
                    <div class="flex space-x-3">
                        <x-ui.button route="flashcards.create-statement" variant="green">
                            <x-ui.icon variant="check" class="w-4 h-4 mr-2" />
                            True/False
                        </x-ui.button>
                        <x-ui.button route="flashcards.create-multiple-choice" variant="indigo">
                            <x-ui.icon variant="clipboard" class="w-4 h-4 mr-2" />
                            Multiple Choice
                        </x-ui.button>
                    </div>
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
                        <x-ui.icon variant="book" class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No flashcards</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Get started by creating a new flashcard.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
