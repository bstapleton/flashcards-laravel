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

            <!-- User Stats -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Your Performance Stats</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Track your learning progress and achievements
                    </p>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Weekly Comparison -->
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900">This Week vs Last Week</div>
                            <div id="weekly-chart" class="mt-4"></div>
                        </div>

                        <!-- Monthly Comparison -->
                        <div class="text-center">
                            <div class="text-lg font-semibold text-gray-900">This Month vs Last Month</div>
                            <div id="monthly-chart" class="mt-4"></div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-5 sm:px-6">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">{{ $userData['points'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">Total Points</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">{{ $userData['question_count'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">Total Flashcards</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">{{ $userData['attempt_count'] }}</div>
                            <div class="text-sm text-gray-600 mt-1">Total Attempts Made</div>
                        </div>
                    </div>
                </div>
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
                                    <x-ui.icon variant="plus" class="h-6 w-6 text-indigo-600" />
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
                                    <x-ui.icon variant="play" class="h-6 w-6 text-green-600" />
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
                                    <x-ui.icon variant="clock" class="h-6 w-6 text-gray-600" />
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
                                    <x-ui.icon variant="cards" class="h-6 w-6 text-gray-600" />
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
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.userData = @json($userData);
</script>
@endpush
