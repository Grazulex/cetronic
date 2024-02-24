@extends('front.layout.default')
@section('content')
    <section class="login_page">
        <h2 class="login_page_title">{{ __('user.account') }}</h2>
        <div class="container">
            <x-auth-session-status :status="session('status')" />
            <div class="row">
                <!-- REGISTER FORM -->
                <div class="offset-lg-3 col-lg-6 mb-3" data-aos="fade-left" data-aos-delay="200"
                     data-aos-duration="1000">
                    <div class="login">
                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <h2 class="title-login">{{ __('user.register') }}</h2>

                            <!-- Name -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="name">{{ __('user.name') }}</label>

                                <input class="login-form form-control" id="name" type="text" name="name"
                                       value="{{ old('name') }}" autofocus />
                                @error('name')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- Company -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="company">{{ __('user.company') }}</label>

                                <input class="login-form form-control" id="company" type="text" name="company"
                                       value="{{ old('company') }}" />
                                @error('company')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- Email Address -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="email">{{ __('user.email') }}</label>

                                <input class="login-form form-control" id="email" type="email" name="email"
                                       value="{{ old('email') }}" />
                                @error('email')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- phone -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="phone">{{ __('user.phone') }}</label>

                                <input class="login-form form-control" id="phone" type="text" name="phone"
                                       value="{{ old('phone') }}" />
                                @error('phone')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- VAT -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="vat">{{ __('user.vat') }}</label>

                                <input class="login-form form-control" id="vat" type="text" name="vat"
                                       value="{{ old('vat') }}" />
                                @error('vat')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- street -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="street">{{ __('user.street') }}</label>

                                <input class="login-form form-control" id="street" type="text" name="street"
                                       value="{{ old('street') }}" />
                                @error('street')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- street_number -->
                            <div class="form-group">
                                <label class="champ-form-commande"
                                       for="street_number">{{ __('user.street_number') }}</label>

                                <input class="login-form form-control" id="street_number" type="text"
                                       name="street_number"
                                       value="{{ old('street_number') }}" />
                                @error('street_number')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- zip -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="zip">{{ __('user.zip') }}</label>

                                <input class="login-form form-control" id="zip" type="text" name="zip"
                                       value="{{ old('zip') }}" />
                                @error('zip')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- city -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="city">{{ __('user.city') }}</label>

                                <input class="login-form form-control" id="city" type="text" name="city"
                                       value="{{ old('city') }}" />
                                @error('city')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- country -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="country">{{ __('user.country') }}</label>

                                <select class="form-control login-form" name="country" id="country">
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->value }}"
                                            {{ isset($location) && $location->country == $country ? 'selected' : '' }}>
                                            {{ $country->name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- language -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="language">{{ __('user.language') }}</label>
                                <select name="language" id="language" class="form-control">
                                    <option value="fr">Français</option>
                                    <option value="en">English</option>
                                    <option value="nl">Nederlands</option>
                                </select>
                                @error('language')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- is customer -->
                            <div class="form-group">
                                <label class="champ-form-commande d-block"
                                       for="customer">{{ __('user.customer') }}</label>

                                <div class="cb-group">
                                    <div class="cb-item">
                                        <input type="radio" id="customer_yes" name="customer" value="yes"
                                               checked="{{ old('customer') == 'yes' ? true : false }}" /> {{ __('user.yes') }}
                                    </div>
                                    <div class="cb-item">
                                        <input type="radio" id="customer_no" name="customer"
                                               value="no"
                                               checked="{{ old('customer') == 'no' ? true : false }}" /> {{ __('user.no') }}
                                    </div>
                                </div>


                            </div>

                            <!-- brands -->
                            <div class="form-group">
                                <label class="champ-form-commande d-block" for="brands">{{ __('user.brands') }}</label>
                                <div class="cb-group">
                                    @foreach ($brands as $brand)
                                        @if ($loop->index % 4 == 0 && $loop->index > 0 )
                                            <div class="cb-line-breaker"></div>
                                        @endif
                                        <div class="cb-item">
                                            <input type="checkbox" name="brands[]"
                                                   value="{{ $brand->name }}" /> {{ $brand->name }}
                                        </div>
                                    @endforeach
                                </div>

                            </div>

                            <!-- Password -->
                            <div class="form-group">
                                <label class="champ-form-commande" for="password">{{ __('user.password') }}</label>

                                <input class="login-form form-control" id="password" type="password" name="password"
                                       autocomplete="new-password" placeholder="{{ __('user.rules.password') }}" />
                                @error('password')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- Confirm Password -->
                            <div class="form-group">
                                <label class="champ-form-commande"
                                       for="password_confirmation">{{ __('user.confirm') }}</label>

                                <input class="login-form form-control" id="password_confirmation" type="password"
                                       name="password_confirmation" placeholder="{{ __('user.rules.confirmation') }}" />
                                @error('password_confirmation')
                                <span class="text-error" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <input type="text" name="title" style="visibility: hidden;">

                            <div class="form-group">
                                <div class="d-flex justify-content-end">
                                    <a class="link-password-request" href="{{ route('login') }}">
                                        {{ __('user.already') }}
                                    </a>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <button class="login_btn" gtype="submit">
                                        {{ __('user.register') }}
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
