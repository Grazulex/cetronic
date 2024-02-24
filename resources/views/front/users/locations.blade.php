@extends('front.layout.default')
@section('content')
    <section class="login_page">
        <h2 class="login_page_title">{{ __('user.locations') }}</h2>
        <div class="container">

            <x-menu-dashboard-component />

            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="row mt-4">
                @foreach (Auth::user()->locations as $location)
                    <div class="col-xl-3">
                        <div class="panel panel-default">

                            <div class="panel-body">
                                <div class="panel-heading">{{ $location->type->name }}-{{ $location->company }}</div>
                                <span class="info">vat : </span>
                                {{ $location->vat }}<br>
                                <span class="info">Nom Complet :</span> {{ $location->firstname }}
                                {{ $location->lastname }}<br>
                                <span class="info">phone :</span> {{ $location->phone }}<br>
                                <span class="info">address : </span>
                                {{ $location->street_extra }} <br>
                                {{ $location->street }}
                                {{ $location->street_number }}<br>
                                <span class="info">City : </span> {{ $location->zip }} {{ $location->city }}<br>
                                <span class="info">country :</span> {{ $location->country->value }}<br>

                                <div class="panel-footer mt-4">
                                    <a class="icon-action px-2"
                                        href="{{ route('user_location.edit', ['location' => $location->id]) }}">
                                        <svg width="27" height="26" viewBox="0 0 27 26" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M23.141 14.3723V22.8745C23.141 24.0435 22.1447 25 20.9269 25H3.2141C1.99635 25 1 24.0435 1 22.8745V5.87011C1 4.70106 1.99635 3.74456 3.2141 3.74456H11.6498M10.9635 15.4351H15.3917L25.6762 5.56195C26.1079 5.14747 26.1079 4.47788 25.6762 4.0634L22.8088 1.31086C22.3771 0.89638 21.6797 0.89638 21.248 1.31086L10.9635 11.184V15.4351ZM10.9635 15.4351L9.8564 16.4978M18.7128 3.74456L23.141 7.99565"
                                                stroke="#4D4D4D" stroke-miterlimit="10" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </a>

                                    <a class="icon-action"
                                        href="{{ route('user_location.delete', ['location' => $location->id]) }}">
                                        <svg width="23" height="28" viewBox="0 0 23 28" fill="none"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                d="M1 4.3913H22M11.5 8.91304V22.4783M7.07895 8.91304V22.4783M15.9211 8.91304V22.4783M15.9211 4.3913H7.07895V3.61137C7.07895 2.16441 8.21737 1 9.6321 1H13.3679C14.7826 1 15.9211 2.16441 15.9211 3.61137V4.3913ZM16.0869 27H6.92419C5.15577 27 3.69683 25.587 3.6084 23.7783L2.65789 4.3913H20.3421L19.3916 23.7783C19.3032 25.587 17.8442 27 16.0869 27Z"
                                                stroke="#4D4D4D" stroke-miterlimit="10" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <br>

                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row">
                <div class="icon-add">
                    <a href="{{ route('user_location.add') }}">{{ __('user.location.add') }}</a><br>
                </div>
            </div>

        </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
