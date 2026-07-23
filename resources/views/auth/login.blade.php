<!doctype html>

<html lang="en" class=" layout-wide  customizer-hide" dir="ltr" data-skin="default" data-bs-theme="light"
    data-assets-path="{{ asset('assets') }}" data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="" />
    <link rel="icon" type="image/x-icon" href="{{ company_favicon() }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/@form-validation/form-validation.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/page-auth.css') }}" />
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('assets/js/config.js') }}"></script>
</head>

<body>
    <div class="authentication-wrapper authentication-cover">
        <a href="{{ route('home') }}" class="app-brand auth-cover-brand">
            <span class="app-brand-logo demo">
                <img src="{{ company_logo() ?? asset('/assets/img/logo.png') }}" alt="{{ company_name() }}"
                    style="max-height: auto; width: auto;">
            </span>
        </a>
        <div class="authentication-inner row m-0">

            <div class="d-none d-xl-flex col-xl-8 p-0">
                <div class="auth-cover-bg d-flex justify-content-center align-items-center">
                    <img src="{{ login_cover() ?? asset('/assets/img/login_cover.png') }}" alt="auth-login-cover"
                        class="my-5 auth-illustration" />
                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}" alt="auth-login-cover"
                        class="platform-bg" />
                </div>
            </div>
            <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
                <div class="w-px-400 mx-auto mt-12 pt-5">

                    <h4 class="mb-1">
                        {{ __('Welcome to') }} {{ company_name() }}! 👋
                    </h4>

                    <p class="mb-6">
                        {{ __('Please sign-in to your account and start the adventure') }}
                    </p>
                    <form method="POST">
                        @csrf

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Email / Mobile -->
                        <div class="mb-4">
                            <label class="form-label"> {{ __('Email or Mobile Number') }} </label>
                            <input type="text" name="login"
                                class="form-control @error('login') is-invalid @enderror"
                                placeholder="Enter Email or Mobile Number" value="{{ old('login', session('login')) }}"
                                {{ session('showOtp') ? 'readonly' : '' }} required>

                            @error('login')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        @if (session('showOtp'))
                            <input type="hidden" name="login" value="{{ session('login') }}">
                            <!-- OTP -->
                            <div class="mb-4">
                                <label class="form-label"> {{ __('Enter OTP') }} </label>
                                <input type="text" name="otp" maxlength="6"
                                    class="form-control @error('otp') is-invalid @enderror"
                                    placeholder="Enter 6 Digit OTP" autocomplete="one-time-code">
                                @error('otp')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <button type="submit" formaction="{{ route('login.verifyOtp') }}"
                                class="btn btn-success w-100">
                                <i class="ti tabler-check me-1"></i>
                                {{ __('Verify OTP') }}
                            </button>

                            <div class="text-center mt-3">
                                <button id="resendBtn" type="submit" formaction="{{ route('login.sendOtp') }}"
                                    class="btn btn-link" disabled>
                                    {{ __('Resend OTP in') }} <span id="countdown">60</span>{{ __('s') }}
                                </button>
                            </div>
                        @else
                            <!-- Password -->
                            <div class="mb-4 form-password-toggle">
                                <label class="form-label"> {{ __('Password') }} </label>
                                <div class="input-group input-group-merge">
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="••••••••">

                                    <span class="input-group-text cursor-pointer">
                                        <i class="icon-base ti tabler-eye-off"></i>
                                    </span>

                                </div>

                                @error('password')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="my-4">
                                <div class="d-flex justify-content-between">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember"
                                            id="remember">
                                        <label class="form-check-label" for="remember">
                                            {{ __('Remember Me') }}
                                        </label>
                                    </div>
                                    <a href="{{ route('forgot-password') }}">
                                        {{ __('Forgot Password?') }}
                                    </a>
                                </div>
                            </div>

                            <div class="d-grid gap-3">

                                <button type="submit" formaction="{{ route('login.password') }}"
                                    class="btn btn-primary">
                                    <i class="ti tabler-lock me-1"></i>
                                    {{ __('Login with Password') }}
                                </button>

                                <button type="submit" formaction="{{ route('login.sendOtp') }}"
                                    class="btn btn-outline-primary">
                                    <i class="ti tabler-mail me-1"></i>
                                    {{ __('Login with OTP') }}
                                </button>
                            </div>
                        @endif

                    </form>

                    <p class="text-center mt-5">
                        <span>{{ __('New on our platform?') }}</span>

                        <a href="{{ route('register') }}">
                            {{ __('Create an account') }}
                        </a>
                    </p>

                </div>
            </div>
        </div>
    </div>

    <!-- / Content -->
    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@algolia/autocomplete-js.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
    <script src="{{ asset('assets/js/pages-auth.js') }}"></script>
    @if (session('showOtp'))
        <script>
            let seconds = 60;
            const timer = setInterval(function() {
                seconds--;
                document.getElementById('countdown').innerHTML = seconds;

                if (seconds <= 0) {

                    clearInterval(timer);
                    const btn = document.getElementById('resendBtn');
                    btn.disabled = false;
                    btn.innerHTML = "Resend OTP";

                }
            }, 1000);
        </script>
    @endif
</body>

</html>
