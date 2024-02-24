@extends('front.layout.default')
@section('content')
    <section class="login_page">
        <h2 class="login_page_title">{{ __('user.profile') }}</h2>
        <div class="container">
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

            <x-menu-dashboard-component />

            <div class="row mt-5">
                <div class="col-xl-6">
                    <div class="panel-body">
                        <h2 class="title-login">{{ __('user.change') }}</h2>
                        <form class="form-horizontal" method="POST" action="{{ route('user_profile.update') }}">
                            {{ csrf_field() }}
                            <div class="form-minheight">

                                <div class="form-group">
                                    <label for="name"
                                        class="col-md-4 control-label champ-form-commande">{{ __('user.name') }}</label>

                                    <input id="name" type="text" class="form-control login-form" name="name"
                                        value="{{ Auth::user()->name }}" required>
                                </div>

                                <div class="form-group">
                                    <label for="email"
                                        class="col-md-4 control-label champ-form-commande">{{ __('user.email') }}</label>

                                    <input id="email" type="email" class="form-control login-form" name="email"
                                        value="{{ Auth::user()->email }}" required>
                                </div>

                                <div class="form-group">
                                    <label class="col-md-4 control-label champ-form-commande" for="language" />{{ __('user.language') }}</label>
                                    <select name="language" id="language" class="form-control login-form">
                                        <option value="fr" @if (Auth::user()->language === 'fr') selected @endif>Français</option>
                                        <option value="en" @if (Auth::user()->language === 'en') selected @endif>English</option>
                                        <option value="nl" @if (Auth::user()->language === 'nl') selected @endif>Nederlands</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="receive_cart_notification"
                                        class="col-md-12 control-label champ-form-commande">{{ __('user.receive_cart_notification') }}</label>

                                    <input id="receive_cart_notification" type="checkbox" value="1" name="receive_cart_notification"
                                        @if (Auth::user()->receive_cart_notification)
                                            checked="checked"
                                        @endif
                                        >
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="login_btn">
                                        {{ __('user.update') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="panel-body">
                        <h2 class="title-login">{{ __('user.password') }}</h2>
                        <form class="form-horizontal" method="POST" action="{{ route('user_profile.changepassword') }}">
                            {{ csrf_field() }}

                            <div class="form-minheight">

                                <div class="form-group{{ $errors->has('current-password') ? ' has-error' : '' }}">
                                    <label for="new-password" class="col-md-4 control-label champ-form-commande">{{ __('user.current') }}</label>

                                    <input id="current-password" type="password" class="form-control login-form"
                                        name="current-password" required>

                                    @if ($errors->has('current-password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('current-password') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group{{ $errors->has('new-password') ? ' has-error' : '' }}">
                                    <label for="new-password" class="col-md-4 control-label champ-form-commande">{{ __('user.new') }}</label>

                                    <input id="new-password" type="password" class="form-control login-form"
                                        name="new-password" required>

                                    @if ($errors->has('new-password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('new-password') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="new-password-confirm"
                                        class="col-md-4 control-label champ-form-commande">{{ __('user.confirm') }}</label>

                                    <input id="new-password-confirm" type="password" class="form-control login-form"
                                        name="new-password_confirmation" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="d-flex justify-content-center">
                                    <button type="submit" class="login_btn">
                                        {{ __('user.update') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <x-section-brands-component />
    <x-section-features-component />
@stop
