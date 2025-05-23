@php
$errors = session('errors') ?? new \Illuminate\Support\MessageBag();
@endphp
<x-kaikon::guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-kaikon::input-label for="name" :value="__('messages.Name')" />
            <x-kaikon::text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-kaikon::input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-kaikon::input-label for="email" :value="__('messages.Email')" />
            <x-kaikon::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-kaikon::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-kaikon::input-label for="password" :value="__('messages.Password')" />

            <x-kaikon::text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-kaikon::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-kaikon::input-label for="password_confirmation" :value="__('messages.ConfirmPassword')" />

            <x-kaikon::text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-kaikon::input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('messages.AlreadyRegistered') }}
            </a>

            <x-kaikon::primary-button class="ms-4">
                {{ __('messages.Register') }}
            </x-kaikon::primary-button>
        </div>
    </form>
</x-kaikon::guest-layout>
