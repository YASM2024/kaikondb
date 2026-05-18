<x-kaikon::expanded.form-page>

    @include('kaikon::expanded.partials.form-body')
    @include('kaikon::expanded.partials.form-actions')

    <x-slot:scripts>
        @include('kaikon::expanded.partials.config')
        <script type="module" src="{{ url('/js/components/expanded/form.js') }}"></script>
    </x-slot:scripts>

</x-kaikon::expanded.form-page>
