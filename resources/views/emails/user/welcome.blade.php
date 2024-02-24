@extends('front.layout.mail')
@section('content')


{!! __('emails.user_welcome.body', ['name' => $name]) !!}


@stop