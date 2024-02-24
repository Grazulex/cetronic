<section class="head">
    <div class="container">
        <div class="row d-flex align-items-center">
            <div class="col-xl-7 d-flex justify-content-start">
                <span class="d-xl-block">
                    {{ __('nav.message') }}
                     |
                    <a class="text-decoration-none" href="{{ url('/contact') }}">{{ ucfirst(__('home.footer.column1.contact')) }}</a>
                     |
                    <a class="text-decoration-none" href="tel:{{ __('home.footer.column4.phone') }}">{{ __('home.footer.column4.phone') }}</a>
                </span>
            </div>
            <div class="col-xl-5 d-flex justify-content-end">
                <div class="d-flex align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="dropdown">
                            @auth
                                <button class="btn btn-dropdown-login dropdown-toggle px-2" type="button"
                                    id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ Auth::user()->name }}
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right user-nav" aria-labelledby="dropdownMenuButton1">
                                    {{--  <li><a class="" href="{{ route('user_dashboard') }}">{{ __('nav.account') }}</a></li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                                            <li>{{ __('Log Out') }}</li>
                                        </x-dropdown-link>
                                    </form> --}}
                                    <div aria-labelledby="navbarDropdownMenuLink">
                                        <a class="dropdown-item nav-item--strong" href="{{ route('user_dashboard') }}">
                                            <svg class="user-svg"   xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M406.5 399.6C387.4 352.9 341.5 320 288 320H224c-53.5 0-99.4 32.9-118.5 79.6C69.9 362.2 48 311.7 48 256C48 141.1 141.1 48 256 48s208 93.1 208 208c0 55.7-21.9 106.2-57.5 143.6zm-40.1 32.7C334.4 452.4 296.6 464 256 464s-78.4-11.6-110.5-31.7c7.3-36.7 39.7-64.3 78.5-64.3h64c38.8 0 71.2 27.6 78.5 64.3zM256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-272a40 40 0 1 1 0-80 40 40 0 1 1 0 80zm-88-40a88 88 0 1 0 176 0 88 88 0 1 0 -176 0z"/></svg>
                                            <span>{{ __('nav.account') }}</span>
                                        </a>
                                        <div class="dropdown-divider" style="border: none;"></div>
                                        @if (Auth::user()->role != 'customer')
                                            <a class="dropdown-item nav-item--strong" href="{{ route('brand.download.agent') }}">
                                                <svg class="user-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V160H256c-17.7 0-32-14.3-32-32V0H64zM256 0V128H384L256 0zM216 232V334.1l31-31c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-72 72c-9.4 9.4-24.6 9.4-33.9 0l-72-72c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l31 31V232c0-13.3 10.7-24 24-24s24 10.7 24 24z"/></svg>
                                                <span>{{ __('nav.cataloge_download') }}</span>
                                            </a>

                                            <div class="dropdown-divider"></div>
                                        @endif
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                                                <li class="dropdown-item mb-2 mt-3">
                                                    <svg class="user-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--! Font Awesome Pro 6.3.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L353.3 251.6C407.9 237 448 187.2 448 128C448 57.3 390.7 0 320 0C250.2 0 193.5 55.8 192 125.2L38.8 5.1zM264.3 304.3C170.5 309.4 96 387.2 96 482.3c0 16.4 13.3 29.7 29.7 29.7H514.3c3.9 0 7.6-.7 11-2.1l-261-205.6z"/></svg>
                                                    <span>{{ __('Log Out') }}</span>
                                                </li>
                                            </x-dropdown-link>
                                        </form>

                                    </div>
                                </ul>
                            </div>
                        @else
                            <div class="d-flex align-items-center me-3">
                                <a class="login-register-link" href="{{ route('login') }}">{{ __('nav.login') }}</a>
                                <span class="px-2">|</span>
                                @if (Route::has('register'))
                                    <a class="login-register-link" href="{{ route('register') }}">{{ __('nav.register') }}</a>
                            </div>

                        </div>
                        @endif
                    @endauth
                </div>
                <div class="d-flex align-items-center ms-2">
                    <div class="dropdown">

                        <button class="btn btn-dropdown-login dropdown-toggle px-2" type="button" id="dropdownMenuButton1"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <span
                                class="flag-icon flag-icon-{{ Config::get('languages')[App::getLocale()]['flag-icon'] }}"></span>
                            {{ Config::get('languages')[App::getLocale()]['display'] }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-L" aria-labelledby="navbarDarkDropdownMenuLink">

                            <div aria-labelledby="navbarDropdownMenuLink">
                                @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                                    <a rel="alternate" hreflang="{{ $localeCode }}"
                                        href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                                        class="dropdown-item dropdown-languages-link">
                                        {{ $properties['native'] }}
                                    </a>
                                @endforeach
                            </div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
