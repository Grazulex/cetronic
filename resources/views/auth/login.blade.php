@extends('front.layout.default')
@section('content')
<section class="login_page">
    <h2 class="login_page_title">{{ __('user.account') }}</h2>
    <div class="container">
        <div class="row">
            <!-- LOGIN FORM -->
            <div class="offset-lg-3 col-lg-6 mb-3" data-aos="fade-right" data-aos-delay="200" data-aos-duration="1000">
                <div class="login">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <h2 class="title-login">{{ __('user.login') }}</h2>


                        <br />
                        <!-- Email Address -->
                        <div>
                            <label class="champ-form-commande" for="email">{{ __('user.email') }}</label>
                            <input class="form-control login-form form-control" type="email" name="email"
                                value="{{ old('email') }}" autofocus />
                            @error('email')
                                <span class="text-error" role="alert">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="champ-form-commande" for="password">{{ __('user.password') }}</label>

                            <input class="form-control login-form form-control" type="password" name="password"
                                autocomplete="current-password" />
                            @error('password')
                                <span class="text-error" role="alert">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="form-check form-check-inline" <label for="remember_me">
                            <input class="form-check-input" id="remember_me" type="checkbox" name="remember">
                            <span class="form-check-label"> {{ __('user.remember') }}</span>
                            </label>
                        </div>

                        <div>
                            <div class="d-flex justify-content-end">
                                @if (Route::has('password.request'))
                                    <a class="link-password-request" href="{{ route('password.request') }}">
                                        {{ __('user.forgot') }}
                                    </a>
                                @endif
                            </div>

                            <div class="d-flex justify-content-center">
                                <button class="login_btn" gtype="submit">
                                    {{ __('user.login') }}
                                </button>
                            </div>

                            <div class="d-flex justify-content-center mt-4">
                                @if (Route::has('register'))
                                    <a class="link-password-request" href="{{ route('register') }}">
                                        {{ __('user.register_now') }}
                                        <!-- Don't have an account yet ? Register Now -->
                                    </a>
                                @endif
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