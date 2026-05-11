@extends('layouts.app')

@section('title', 'My Attempt History')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0 max-w-4xl mx-auto">
            <div class="mb-8">
                <a href="{{ route('answer.index') }}"
                   class="text-sm text-gray-500 hover:text-gray-700 mb-4 inline-block">
                    ← Back to Answer home page
                </a>
                <h1 class="text-2xl font-bold text-gray-900">My Attempt History</h1>
                <p class="mt-2 text-gray-600">Review your past answers and track your progress over time.</p>
            </div>

            @if($attempts->count() > 0)
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        @foreach($attempts as $attempt)
                            <li class="hover:bg-gray-50">
                                <a href="{{ route('attempts.show', $attempt) }}" class="block px-4 py-4 sm:px-6">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0">
                                                    @if($attempt->correctness->value === 'complete')
                                                        <x-ui.icon variant="check" class="h-6 w-6 text-green-500" />
                                                    @else
                                                        <x-ui.icon variant="times" class="h-6 w-6 text-red-500" />
                                                    @endif
                                                </div>
                                                <div class="ml-4 flex-1">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{ Str::limit($attempt->question, 80) }}
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ $attempt->flashcard->type->value }} •
                                                        {{ $attempt->answered_at->diffForHumans() }} •
                                                        {{ $attempt->points_earned > 0 ? '+' . $attempt->points_earned . ' points' : $attempt->points_earned . ' points' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ml-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ strtolower($attempt->difficulty->name) }}-100 text-{{ strtolower($attempt->difficulty->name) }}-800">
                                                {{ $attempt->difficulty->value }}
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $attempts->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <x-ui.icon variant="sad" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No attempts yet</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        You haven't answered any questions yet. Start practicing to see your attempt history here.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('answer.random') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Start Answering Questions
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
