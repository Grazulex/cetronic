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

    <!-- Validation Errors -->
    <x-auth-validation-errors :errors="$errors" />

            <div class="row d-flex justify-content-center">
                <div class="col-xl-6 mt-4 ">
                    <div class="login">

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <x-label for="email" class="champ-form-commande" :value="__('Email')" />

            <x-input id="email" class="login-form form-control" type="email" name="email" :value="old('email', $request->email)" required autofocus />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-label for="password" class="champ-form-commande" :value="__('Password')" />

            <x-input id="password" class="login-form form-control" type="password" name="password" required />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-label for="password_confirmation" class="champ-form-commande" :value="__('Confirm Password')" />

            <x-input id="password_confirmation" class="login-form form-control" type="password" name="password_confirmation" required />
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
