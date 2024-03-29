@extends('front.layout.default')
@section('content')

    <div>
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <!-- Validation Errors -->
    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-label for="password" :value="__('Password')" />

            <x-input id="password" type="password" name="password" required autocomplete="current-password" />
        </div>

        <div>
            <x-button>
                {{ __('Confirm') }}
            </x-button>
        </div>
    </form>
@stop
