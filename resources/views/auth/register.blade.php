@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Create your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Or
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    sign in to your existing account
                </a>
            </p>
        </div>
        
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-md p-4">
                <div class="text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <x-forms.input 
                        name="username" 
                        placeholder="Username"
                        :error="$errors->first('username')"
                    />
                </div>
                
                <div>
                    <label for="display_name" class="block text-sm font-medium text-gray-700">Display Name</label>
                    <x-forms.input 
                        name="display_name" 
                        placeholder="Display Name"
                        required
                        :error="$errors->first('display_name')"
                    />
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <x-forms.input 
                        type="password" 
                        name="password" 
                        placeholder="Password"
                        required
                        :error="$errors->first('password')"
                    />
                </div>
                
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                    <x-forms.input 
                        type="password" 
                        name="password_confirmation" 
                        placeholder="Confirm Password"
                        required
                        :error="$errors->first('password_confirmation')"
                    />
                </div>
            </div>

            <div>
                <x-forms.button type="submit" class="w-full">
                    Register
                </x-forms.button>
            </div>
        </form>
    </div>
</div>
@endsection
