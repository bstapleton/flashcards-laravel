@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Welcome back, {{ Auth::user()->display_name }}!
                </p>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Quick Actions</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Get started with your flashcards
                    </p>
                </div>
                <ul class="divide-y divide-gray-200">
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-indigo-600">
                                        Create New Flashcard
                                    </div>
                                    <div class="mt-1 space-x-4">
                                        <a href="{{ route('flashcards.create-statement') }}" class="text-sm text-gray-600 hover:text-indigo-600">
                                            True/False
                                        </a>
                                        <span class="text-gray-400">•</span>
                                        <a href="{{ route('flashcards.create-multiple-choice') }}" class="text-sm text-gray-600 hover:text-indigo-600">
                                            Multiple Choice
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                Add a new question to study
                            </div>
                        </div>
                    </li>
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('answer.index') }}" class="text-sm font-medium text-green-600 hover:text-green-900">
                                        Test your knowledge
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                Answer some of your questions
                            </div>
                        </div>
                    </li>
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 7V12L14.5 13.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('attempts.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                        View Attempt History
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                Review your past answers and progress
                            </div>
                        </div>
                    </li>
                    <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('flashcards.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                        Manage Flashcards
                                    </a>
                                </div>
                            </div>
                            <div class="flex items-center text-sm text-gray-500">
                                View and edit your flashcards
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Stats for your library</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Information about your learning library
                    </p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <p class="mt-1 max-w-2xl text-gray-900">
                        {{ $stats['total_flashcards'] }} total flashcards, of which {{ $stats['active_flashcards'] }} are <a href="{{ route('flashcards.index') }}">in active rotation</a>.
                    </p>
                    <p>
                        You have {{ $stats['draft_flashcards'] }} that you <a href="{{ route('flashcards.drafts') }}">haven't published</a> to the learning pool yet, and {{ $stats['hidden_flashcards'] }} are hidden.
                    </p>
                    <p class="bold">By category:</p>
                    <ul class="list-disc">
                        <li class="ml-5">
                            <a href="{{ route('flashcards.fresh-learning') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">{{ $stats['fresh_learning'] }} Fresh learning</a>
                        </li>
                        <li class="ml-5">
                            <a href="{{ route('flashcards.intermediate-mastery') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">{{ $stats['intermediate_mastery'] }} Intermediate mastery</a>
                        </li>
                        <li class="ml-5">
                            <a href="{{ route('flashcards.high-mastery') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">{{ $stats['high_mastery'] }} High mastery</a>
                        </li>
                        <li class="ml-5">
                            <a href="{{ route('flashcards.completely-mastered') }}" class="underline text-indigo-600 hover:text-gray-600 hover:no-underline">{{ $stats['buried_flashcards'] }} Completely mastered</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
