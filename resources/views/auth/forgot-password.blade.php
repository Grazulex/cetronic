@extends('front.layout.default')
@section('content')

    <section class="login_page">
        <h2 class="login_page_title">{{ __('password.forget') }}</h2>

        <div class="container">

            <div class="row mt-5 p-5">
                <div class="col-xl-12">
                    <span class="forgot-p">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                    </span>

                </div>
            </div>


            <!-- Session Status -->
            <x-auth-session-status :status="session('status')" />

            <!-- Validation Errors -->
            <x-auth-validation-errors :errors="$errors" />

            <div class="row d-flex justify-content-center">
                <div class="col-xl-6 mt-4 ">
                    <div class="login">
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf

                            <!-- Email Address -->
                            <div>
                                <x-label class="champ-form-commande" for="email" :value="__('Email')" />

                                <x-input class="login-form form-control" id="email" type="email" name="email"
                                    :value="old('email')" required autofocus />
                            </div>

                            <div class="d-flex justify-content-center">
                                <button type="submit">
                                    {{ __('Reset Password') }}
                                </button>
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
