@props([
    'header' => '',
    'script' => '',
    'scriptVersion' => null,
])

@php
    $scriptUrl = url($script);
    if ($scriptVersion !== null) {
        $scriptUrl .= '?v=' . $scriptVersion;
    }
@endphp

<x-kaikon::app-layout>
    <x-slot:additionalStyles>
        <link rel="stylesheet" href="{{ url('/css/masters.css') }}">
        {{ $styles ?? '' }}
    </x-slot:additionalStyles>

    <x-slot:header>{{ $header }}</x-slot:header>

    {{ $slot }}

    <x-slot:scripts>
        <script src="{{ $scriptUrl }}" type="module"></script>
    </x-slot:scripts>
</x-kaikon::app-layout>
