<x-kaikon::app-layout>
    <x-slot:header>{{ $pageTitle }}</x-slot:header>

    <div class="container py-4">
        @include('kaikon::admin.partials.history-page-body')
    </div>

    @if (!$entries->isEmpty())
        <x-slot:scripts>
            @include('kaikon::admin.partials.history-page-scripts')
        </x-slot:scripts>
    @endif
</x-kaikon::app-layout>
