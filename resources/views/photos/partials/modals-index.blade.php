@include('kaikon::photos.partials.modal-detail', ['variant' => 'index'])

@include('kaikon::photos.partials.modal-profile')

@if (\Illuminate\Support\Facades\Auth::check())
    @include('kaikon::photos.partials.modal-form', ['mode' => 'register'])
    @include('kaikon::photos.partials.modal-form', ['mode' => 'edit'])
@endif
