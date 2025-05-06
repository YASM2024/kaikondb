@php
$errors = session('errors') ?? new \Illuminate\Support\MessageBag();
@endphp
<x-kaikon::guest-layout>
    <!-- Session Status -->
    <x-kaikon::auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-kaikon::input-label for="email" :value="__('messages.Email')" />
            <x-kaikon::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-kaikon::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-kaikon::input-label for="password" :value="__('messages.Password')" />

            <x-kaikon::text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-kaikon::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('messages.RememberMe') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('messages.ForgotYourPassword') }}
                </a>
            @endif

            <x-kaikon::primary-button class="ms-3">
                {{ __('messages.LogIn') }}
            </x-kaikon::primary-button>
        </div>
    </form>
</x-kaikon::guest-layout>
