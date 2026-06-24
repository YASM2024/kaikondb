@extends('kaikon::errors.layout')

@section('title', __('messages.section_maintenance_title'))
@section('message')
    <div style="max-width: 36rem; margin: 0 auto;">
        <p style="font-size: 1.1rem; margin-bottom: 1rem;">
            {{ $sectionLabel }}
        </p>
        <p style="font-size: 1rem; font-weight: 400; line-height: 1.6;">
            {{ $message ?? __('messages.section_maintenance_default', ['section' => $sectionLabel]) }}
        </p>
        <p style="font-size: 0.9rem; margin-top: 2rem;">
            <a href="{{ route('home') }}">{{ __('messages.section_maintenance_back_home') }}</a>
        </p>
    </div>
@endsection
