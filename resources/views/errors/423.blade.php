@extends('errors::minimal')

@section('title', __('Locked'))
@section('code', '423')
@section('message', __('Locked'))
@section('error_additional_comment', $error_additional_comment ?? '')