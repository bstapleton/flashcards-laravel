@extends('layouts.app')

@section('title', 'Revision')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-gray-900">Revision Mode</h1>
                <p class="mt-2 text-lg text-gray-600">
                    Review your flashcards with answers visible
                </p>
            </div>

            @if(isset($error))
                <div class="mb-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="text-sm text-red-600">
                        {{ $error }}
                    </div>
                </div>
            @endif

            <!-- Revision Options -->
            <div class="max-w-4xl mx-auto">
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-6 py-4">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Choose Revision Mode</h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Random Flashcard -->
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-indigo-500 rounded-md flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Random Flashcard</h3>
                                        <p class="text-sm text-gray-500">Review a random question with answers visible</p>
                                    </div>
                                </div>
                                <form method="GET" action="{{ route('revision.random') }}">
                                    <button type="submit" 
                                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Start Random Revision
                                    </button>
                                </form>
                            </div>

                            <!-- Study All -->
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-green-500 rounded-md flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Review All</h3>
                                        <p class="text-sm text-gray-500">Go through all your active flashcards</p>
                                    </div>
                                </div>
                                <button onclick="startRevisionSession()" 
                                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Start Revision Session
                                </button>
                            </div>

                            <!-- Difficulty Practice -->
                            <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-yellow-500 rounded-md flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Difficulty Review</h3>
                                        <p class="text-sm text-gray-500">Focus on specific difficulty levels</p>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <button onclick="reviewByDifficulty('easy')" 
                                            class="w-full py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Easy
                                    </button>
                                    <button onclick="reviewByDifficulty('medium')" 
                                            class="w-full py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Medium
                                    </button>
                                    <button onclick="reviewByDifficulty('hard')" 
                                            class="w-full py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Hard
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Stats -->
                            <div class="border border-gray-200 rounded-lg p-6">
                                <div class="flex items-center mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-purple-500 rounded-md flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-lg font-medium text-gray-900">Your Progress</h3>
                                        <p class="text-sm text-gray-500">Quick overview of your study activity</p>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('dashboard') }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                        View Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function startRevisionSession() {
    // This would typically start a revision session
    alert('Revision session feature coming soon! Try Random Flashcard for now.');
}

function reviewByDifficulty(difficulty) {
    // This would filter flashcards by difficulty
    alert(`Review by ${difficulty} difficulty coming soon! Try Random Flashcard for now.`);
}
</script>
@endpush
@endsection
