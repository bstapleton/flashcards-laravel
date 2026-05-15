<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Flashcards') - Flashcard App</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        <!-- Navigation -->
        @auth
        <x-ui.navigation class="bg-white shadow-sm border-b">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" class="text-xl font-bold text-indigo-600">
                            Flashcards
                        </a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <x-ui.nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                            Dashboard
                        </x-ui.nav-link>
                        <x-ui.nav-link href="{{ route('subjects.index') }}" :active="request()->routeIs('subjects.*')">
                            My Subjects
                        </x-ui.nav-link>
                        <x-ui.nav-link href="{{ route('flashcards.index') }}" :active="request()->routeIs('flashcards.*')">
                            My Flashcards
                        </x-ui.nav-link>
                        <x-ui.nav-link href="{{ route('revision.index') }}" :active="request()->routeIs('revision.*')">
                            Revise
                        </x-ui.nav-link>
                        <x-ui.nav-link href="{{ route('answer.index') }}" :active="request()->routeIs('answer.*')">
                            Answer
                        </x-ui.nav-link>
                    </div>
                </div>
                <div class="flex items-center sm:hidden">
                    <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path class="hidden" id="menu-close-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            <path id="menu-open-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
            <!-- Mobile menu -->
            <div class="sm:hidden hidden" id="mobile-menu">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('dashboard') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('subjects.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('subjects.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300' }}">
                        My Subjects
                    </a>
                    <a href="{{ route('flashcards.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('flashcards.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300' }}">
                        My Flashcards
                    </a>
                    <a href="{{ route('revision.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('revision.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300' }}">
                        Revise
                    </a>
                    <a href="{{ route('answer.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ request()->routeIs('answer.*') ? 'border-indigo-500 text-indigo-700 bg-indigo-50' : 'border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300' }}">
                        Answer
                    </a>
                </div>
            </div>
        </x-ui.navigation>
        @endauth

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const menuOpenIcon = document.getElementById('menu-open-icon');
            const menuCloseIcon = document.getElementById('menu-close-icon');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                    menuOpenIcon.classList.toggle('hidden');
                    menuCloseIcon.classList.toggle('hidden');
                });
            }
        });
    </script>
</body>
</html>
