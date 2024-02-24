@extends('front.layout.mail')
@section('content')

{{ __('emails.body.user_new') }}

<div>
    <ul>
    @foreach ($datas as $key => $value)
        <li>{{ $key }}: {{ $value }} </li>
    @endforeach
    </ul>
</div>

@stop
