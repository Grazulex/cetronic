@extends('front.layout.mail')
@section('content')


{!! __('emails.cart_update.body', ['product' => $product, 'name' => $name]) !!}


@stop