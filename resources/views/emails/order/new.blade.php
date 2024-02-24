@extends('front.layout.mail')
@section('content')


{!! __('emails.order_new.body', ['reference' => strtoupper($reference), 'name' => $name],$locale) !!}


@stop
