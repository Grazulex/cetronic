@extends('front.layout.mail')
@section('content')


{!! __('emails.cart_add.body', ['product' => $product, 'name' => $name]) !!}


@stop