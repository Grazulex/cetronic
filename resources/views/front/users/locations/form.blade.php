@extends('front.layout.default')
@section('content')


    <section class="login_page">
        <h1 class="login_page_title">{{ __('user.locations') }}</h1>
        <div class="container">
            <div class="row">

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors)
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger">{{ $error }}</div>
                    @endforeach
                @endif

                <form class="form-horizontal" method="POST"
                    action="{{ isset($location) ? route('user_location.update', [$location]) : route('user_location.create') }}">
                    {{ csrf_field() }}

                    <div class="row ">
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="type"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.type') }}</label>
                                <select class="form-control login-form"name="type" id="type">
                                    @foreach (\App\Enum\LocationTypeEnum::cases() as $type)
                                        <option value="{{ $type->value }}"
                                            {{ isset($location) && $location->type == $type ? 'selected' : '' }}>
                                            {{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="company"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.company') }}</label>
                                <input id="company" type="text" class="form-control login-form" name="company"
                                    value="{{ isset($location) ? $location->company : '' }}" required>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="firstname"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.firstname') }}</label>

                                <input id="firstname" type="text" class="form-control login-form" name="firstname"
                                    value="{{ isset($location) ? $location->firstname : '' }}" required>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="lastname"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.lastname') }}</label>
                                <input id="lastname" type="text" class="form-control login-form" name="lastname"
                                    value="{{ isset($location) ? $location->lastname : '' }}" required>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="phone"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.phone') }}</label>

                                <input id="phone" type="text" class="form-control login-form" name="phone"
                                    value="{{ isset($location) ? $location->phone : '' }}" required>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="vat"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.vat') }}</label>

                                <input id="vat" type="text" class="form-control login-form" name="vat"
                                    value="{{ isset($location) ? $location->vat : '' }}" required>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="street"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.street') }}</label>

                                <input id="street" type="text" class="form-control login-form" name="street"
                                    value="{{ isset($location) ? $location->street : '' }}" required>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="street_number"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.street_number') }}</label>

                                <input id="street_number" type="text" class="form-control login-form"
                                    name="street_number" value="{{ isset($location) ? $location->street_number : '' }}">
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="street_other"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.street_other') }}</label>

                                <input id="street_other" type="text" class="form-control login-form"
                                    name="street_other" value="{{ isset($location) ? $location->street_other : '' }}">
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="zip"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.zip') }}</label>

                                <input id="zip" type="text" class="form-control login-form" name="zip"
                                    value="{{ isset($location) ? $location->zip : '' }}" required>
                            </div>
                        </div>


                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="city"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.city') }}</label>
                                <input id="city" type="text" class="form-control login-form" name="city"
                                    value="{{ isset($location) ? $location->city : '' }}" required>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="form-group">
                                <label for="country"
                                    class="col-md-4 control-label champ-form-commande">{{ __('user.location.country') }}</label>
                                <select class="form-control login-form" name="country" id="country">
                                    @foreach (\App\Enum\CountryEnum::cases() as $country)
                                        <option value="{{ $country->value }}"
                                            {{ isset($location) && $location->country == $country ? 'selected' : '' }}>
                                            {{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>





                    </div>

                    <div class="row mt-3 ">
                        <div class="form-group d-flex justify-content-center">
                            <button type="submit" class="login_btn">
                                {{ isset($location) ? __('user.update') : __('user.save') }}
                            </button>
                        </div>
                    </div>




                </form>

            </div>
        </div>
    </section>

    <x-section-brands-component />
    <x-section-features-component />
@endsection
