@extends('front.layout.mail')
@section('content')


{!! __('emails.user_actif.body', ['name' => $name]) !!}


@stop