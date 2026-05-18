<x-kaikon::app-layout>
    <x-slot:additionalStyles>
        <link rel="stylesheet" href="{{ url('/css/forms.css') }}">
        {{ $styles ?? '' }}
    </x-slot:additionalStyles>

    <x-slot:header>{{ $header }}</x-slot:header>

    <div class="form-page-narrow">
        {{ $slot }}
    </div>

    <x-slot:scripts>
        {{ $scripts ?? '' }}
    </x-slot:scripts>
</x-kaikon::app-layout>
