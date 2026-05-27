<x-kaikon::app-layout>
    <x-slot:additionalStyles>
        <link rel="stylesheet" href="{{ url('/css/photos.css') }}">
        {{ $styles ?? '' }}
    </x-slot:additionalStyles>

    <x-slot:header>{{ $header }}</x-slot:header>

    {{ $slot }}

    <x-slot:scripts>
        <script src="{{ url('/js/paginationMessage.js') }}"></script>
        <script src="{{ url('/js/nextPageLoader.js') }}"></script>
        @include('kaikon::photos.partials.config')
        <script type="module" src="{{ url('/js/components/photos/init.js') }}"></script>
    </x-slot:scripts>
</x-kaikon::app-layout>
