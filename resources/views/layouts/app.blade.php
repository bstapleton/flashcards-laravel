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
            </div>
        </x-ui.navigation>
        @endauth

        <!-- Page Content -->
        <main>
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
