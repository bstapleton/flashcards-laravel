@extends('layouts.app')

@section('title', $tag->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="px-4 py-6 sm:px-0">
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $tag->name }}</h1>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tag->getColorClasses() }}">
                            {{ $tag->name }}
                        </span>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('subjects.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Back to Subjects
                        </a>
                        <button type="button" onclick="openDeleteModal()" class="inline-flex items-center px-3 py-2 border border-red-300 text-sm leading-4 font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <x-ui.icon variant="trash" class="w-4 h-4 mr-2" />
                            Delete this Subject
                        </button>
                    </div>
                </div>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $tag->flashcards->count() }} flashcard{{ $tag->flashcards->count() != 1 ? 's' : '' }} have been assigned to this subject
                </p>
            </div>

            @if($tag->flashcards->count() > 0)
                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                    <ul class="divide-y divide-gray-200">
                        @foreach($tag->flashcards as $flashcard)
                            <li class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ match($flashcard->difficulty->name) {
                                                    'EASY' => 'bg-green-100 text-green-800',
                                                    'MEDIUM' => 'bg-yellow-100 text-yellow-800',
                                                    'HARD' => 'bg-red-100 text-red-800',
                                                    'BURIED' => 'bg-gray-100 text-gray-800',
                                                    default => 'bg-gray-100 text-gray-800'
                                                } }}">
                                                    {{ ucfirst($flashcard->difficulty->mastery()) }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ Str::limit($flashcard->text, 100) }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Created {{ $flashcard->created_at->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('revision.show', $flashcard) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            Revise
                                        </a>
                                        <a href="{{ route('answer.show', $flashcard) }}" class="text-green-600 hover:text-green-900 text-sm font-medium">
                                            Attempt
                                        </a>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="text-center py-12">
                    <x-ui.icon variant="sad" class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No flashcards</h3>
                    <p class="mt-1 text-sm text-gray-500">No flashcards have been assigned to this tag yet.</p>
                    <div class="mt-6">
                        <a href="{{ route('flashcards.create-statement') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-ui.icon variant="plus" class="w-4 h-4 mr-2" />
                            Create True/False
                        </a>
                        <a href="{{ route('flashcards.create-multiple-choice') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <x-ui.icon variant="plus" class="w-4 h-4 mr-2" />
                            Create Multiple Choice
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeDeleteModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <x-ui.icon variant="warning" class="h-6 w-6 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Tag</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete the tag "{{ $tag->name }}"? This action cannot be undone.
                            </p>
                            <p class="text-sm text-gray-500 mt-2">
                                The tag will be detached from <span class="font-semibold">{{ $tag->flashcards->count() }}</span> flashcard{{ $tag->flashcards->count() != 1 ? 's' : '' }} and then permanently deleted.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form action="{{ route('subjects.destroy', $tag) }}" method="POST" onsubmit="this.submit(); return false;">
                    @csrf
                    @method('DELETE')
                    <x-ui.button type="submit" variant="destructive">
                        Delete tag
                    </x-ui.button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDeleteModal();
    }
});
</script>
@endsection
