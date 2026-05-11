@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sign in to your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    register for a new account
                </a>
            </p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="text-sm text-red-600">
                    {{ $errors->first() }}
                </div>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            <input type="hidden" name="remember" value="true">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <x-forms.input
                        name="username"
                        placeholder="Username"
                        :error="$errors->first('username')"
                        class="rounded-t-md"
                    />
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <x-forms.input
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                        :error="$errors->first('password')"
                        class="rounded-b-md"
                    />
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        Remember me
                    </label>
                </div>
            </div>

            <div>
                <x-ui.button type="submit" class="w-full">
                    Sign in
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
@endsection
