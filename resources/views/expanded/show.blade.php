<x-kaikon::app-layout>
    @slot('header')
    {{ $header }}
    @endslot

{!! $body !!}
</x-kaikon::app-layout>