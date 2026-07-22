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
        <!-- Logo -->
        <a href="{{ url('/') }}" class="app-brand auth-cover-brand">
            <span class="app-brand-logo demo">
                <img src="{{ company_logo() }}" alt="{{ company_name() }}" style="max-height: auto; width: auto;">
            </span>
        </a>
        <!-- /Logo -->
        <div class="authentication-inner row m-0">
            <!-- Left Text -->
            <div class="d-none d-xl-flex col-xl-8 p-0">
                <div class="auth-cover-bg d-flex justify-content-center align-items-center">
                    <img src="{{ login_cover() }}" alt="auth-login-cover" class="my-5 auth-illustration" />
                    <img src="{{ asset('assets/img/illustrations/bg-shape-image-light.png') }}" alt="auth-login-cover"
                        class="platform-bg" />
                </div>
            </div>
            <!-- /Left Text -->

            <!-- Register -->
            <div class="d-flex col-12 col-xl-4 align-items-center authentication-bg p-sm-12 p-6">
                <div class="w-px-400 mx-auto mt-12 pt-5">

                    <h4 class="mb-1">
                        {{ __('Adventure starts here') }} {{ company_name() }}! 🚀
                    </h4>

                    <p class="mb-6">
                        {{ __('Make your app management easy and fun!') }}
                    </p>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <form method="POST">
                        @csrf

                        <!-- Name -->
                        <div class="mb-4">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', session('register_data.name')) }}" placeholder="Enter your name">
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mobile -->
                        <div class="mb-4">
                            <label class="form-label">{{ __('Mobile') }}</label>
                            <input type="text" name="mobile"
                                class="form-control @error('mobile') is-invalid @enderror"
                                value="{{ old('mobile', session('register_data.mobile')) }}"
                                placeholder="Enter Mobile Number">
                            @error('mobile')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="form-label">{{ __('Email') }}</label>
                            <input type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', session('register_data.email')) }}" placeholder="Enter Email">
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-4 form-password-toggle">
                            <label class="form-label">{{ __('Password') }}</label>
                            <div class="input-group input-group-merge">
                                <input type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    value="{{ old('password') }}" placeholder="••••••••">
                                <span class="input-group-text cursor-pointer">
                                    <i class="icon-base ti tabler-eye-off"></i>
                                </span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4 form-password-toggle">
                            <label class="form-label">{{ __('Confirm Password') }}</label>
                            <div class="input-group input-group-merge">
                                <input type="password" name="password_confirmation"
                                    class="form-control"
                                    value="{{ old('password_confirmation') }}" placeholder="••••••••">
                                <span class="input-group-text cursor-pointer">
                                    <i class="icon-base ti tabler-eye-off"></i>
                                </span>
                            </div>
                        </div>

                        <!-- Terms -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" name="terms" value="1"
                                    class="form-check-input @error('terms') is-invalid @enderror"
                                    {{ old('terms') ? 'checked' : '' }}>
                                <label class="form-check-label">
                                    I agree to <a href="#">Privacy Policy & Terms</a>
                                </label>
                            </div>
                            @error('terms')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Send OTP -->
                        <button type="submit" formaction="{{ route('register.sendOtp') }}"
                            class="btn btn-primary w-100">
                            {{ session('showOtp') ? 'Submit & Send OTP Again' : 'Submit & Send OTP' }}
                        </button>

                        @if (session('showOtp'))
                            <hr class="my-4">

                            <div class="alert alert-info">
                                OTP has been sent to
                                <strong>{{ session('register_data.email') }}</strong>
                            </div>

                            <!-- OTP -->
                            <div class="mb-4">
                                <label class="form-label">Enter OTP</label>
                                <input type="text" name="otp" maxlength="6"
                                    class="form-control @error('otp') is-invalid @enderror"
                                    placeholder="Enter 6 Digit OTP">
                                @error('otp')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Verify -->
                            <button type="submit" formaction="{{ route('register.verifyOtp') }}"
                                class="btn btn-success w-100">
                                Verify OTP & Register
                            </button>

                            <!-- Resend -->
                            <div class="text-center mt-3">
                                <button id="resendBtn" type="submit" formaction="{{ route('register.sendOtp') }}"
                                    class="btn btn-link" disabled>
                                    Resend OTP in <span id="countdown">{{ session('remaining', 60) }}</span>s
                                </button>
                            </div>
                        @endif

                    </form>

                    <p class="text-center mt-5">
                        <span>Already have an account?</span>
                        <a href="{{ route('login') }}">Sign in instead</a>
                    </p>

                </div>
            </div>
            <!-- /Register -->
        </div>
    </div>

    @if (session('showOtp'))
        <script>
            (function () {
                let seconds = parseInt("{{ (int) session('remaining', 60) }}", 10);

                const btn = document.getElementById('resendBtn');
                const txt = document.getElementById('countdown');

                function enableResend() {
                    btn.disabled = false;
                    btn.innerHTML = 'Resend OTP';
                }

                if (isNaN(seconds) || seconds <= 0) {
                    enableResend();
                } else {
                    txt.innerHTML = seconds;
                    const timer = setInterval(function () {
                        seconds--;
                        if (seconds <= 0) {
                            clearInterval(timer);
                            enableResend();
                        } else {
                            txt.innerHTML = seconds;
                        }
                    }, 1000);
                }
            })();
        </script>
    @endif

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
</body>

</html>