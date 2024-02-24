@extends('front.layout.mail')
@section('content')

{{ __('emails.body.help_new') }}

<div>
    <ul>
    @foreach ($datas as $key => $value)
        <li>{{ $key }}: {{ $value }} </li>
    @endforeach
    </ul>
</div>

@stop
