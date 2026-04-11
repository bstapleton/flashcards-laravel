@extends('layouts.app')

@section('title', 'Subjects')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-8 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Subjects</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Manage your subjects
                        </p>
                    </div>
                    <a href="{{ route('subjects.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <x-ui.icon variant="plus" class="w-4 h-4 mr-2" />
                        New Subject
                    </a>
                </div>

                @if($tags->count() > 0)
                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                        <ul class="divide-y divide-gray-200">
                            @foreach($tags as $subject)
                                <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $subject->getColorClasses() }}">
                                                {{ $subject->name }}
                                            </span>
                                            </div>
                                            <div class="ml-4">
                                                <div
                                                    class="text-sm font-medium text-gray-900">{{ $subject->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $subject->flashcards_count }}
                                                    flashcard{{ $subject->flashcards_count != 1 ? 's' : '' }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('subjects.show', $subject) }}"
                                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-12">
                        <x-ui.icon variant="tag" class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No subjects</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by creating a new subject.</p>
                        <div class="mt-6">
                            <a href="{{ route('subjects.create') }}"
                               class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <x-ui.icon variant="plus" class="w-4 h-4 mr-2" />
                                New Tag
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
