@extends('front.layout.mail')
@section('content')


{!! __('emails.cart_remove.body', ['product' => $product, 'name' => $name]) !!}


@stop