<x-kaikon::guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-kaikon::input-label for="password" :value="__('Password')" />

            <x-kaikon::text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-kaikon::input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-kaikon::primary-button>
                {{ __('Confirm') }}
            </x-kaikon::primary-button>
        </div>
    </form>
</x-kaikon::guest-layout>
