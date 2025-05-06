<x-kaikon::guest-layout>
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-kaikon::input-label for="email" :value="__('Email')" />
            <x-kaikon::text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-kaikon::input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-kaikon::input-label for="password" :value="__('Password')" />
            <x-kaikon::text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-kaikon::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-kaikon::input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-kaikon::text-input id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />

            <x-kaikon::input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-kaikon::primary-button>
                {{ __('Reset Password') }}
            </x-kaikon::primary-button>
        </div>
    </form>
</x-kaikon::guest-layout>
