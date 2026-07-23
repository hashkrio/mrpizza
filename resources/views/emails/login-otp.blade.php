<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('Login OTP Code') }}</title>

    <style>
        /* General Reset */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f4f5f8 !important;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }

        /* Container Styles */
        .wrapper {
            width: 100% !important;
            background-color: #f4f5f8;
            padding: 40px 10px;
        }

        .main-card {
            max-width: 600px !important;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Header */
        .header-bg {
            background-color: #820300;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 3px solid #C0392B;
        }

        .header-logo {
            max-height: 60px;
            width: auto;
            display: block;
            margin: 0 auto;
        }

        .header-title {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Body Content */
        .content-body {
            padding: 40px 35px;
            color: #333333;
        }

        .greeting-text {
            margin-top: 0;
            margin-bottom: 16px;
            color: #111111;
            font-size: 20px;
            font-weight: 600;
        }

        .body-text {
            margin: 0 0 16px;
            font-size: 15px;
            line-height: 1.6;
            color: #555555;
        }

        /* OTP Box */
        .otp-container {
            margin: 30px 0;
        }

        .otp-box {
            background-color: #FFF5F5;
            border: 2px dashed #E57373;
            border-radius: 10px;
            padding: 22px 10px;
            text-align: center;
        }

        .otp-code {
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #C0392B;
            font-family: 'Courier New', Courier, monospace;
            display: block;
            padding-left: 10px;
        }

        /* Meta Information */
        .expiry-text {
            margin: 0 0 12px;
            font-size: 14px;
            color: #444444;
            line-height: 1.5;
        }

        .warning-text {
            margin: 0 0 28px;
            font-size: 14px;
            color: #777777;
            line-height: 1.5;
        }

        .divider {
            border: none;
            border-top: 1px solid #eeeeee;
            margin: 25px 0;
        }

        .signoff-text {
            margin: 0;
            font-size: 14px;
            color: #555555;
            line-height: 1.5;
        }

        .text-center {
            text-align: center;
        }

        /* Footer */
        .footer-bg {
            background-color: #2B2B2B;
            padding: 25px;
            text-align: center;
            color: #aaaaaa;
            font-size: 12px;
            line-height: 1.6;
        }

        .footer-link {
            color: #E57373;
            text-decoration: none;
        }
    </style>
</head>

<body style="margin:0; padding:0; background-color:#f4f5f8; font-family:Arial, sans-serif;">

    @php
        $company = company_setting();
    @endphp

    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapper"
        style="background-color: #f4f5f8; padding: 40px 10px;">
        <tr>
            <td align="center">

                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="main-card"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden;">

                    <!-- Header -->
                    <tr>
                        <td align="center" class="header-bg" style="background-color: #7A0A0A; padding: 30px 20px;">
                            @if ($company && $company->logo)
                                <img src="{{ company_logo() }}"
                                    alt="{{ $company->name ?? 'Company Logo' }}" class="header-logo"
                                    style="max-height: 60px; width: auto; display: block;" border="0">
                            @else
                                <h2 class="header-title" style="color: #ffffff; margin: 0; font-size: 24px;">
                                    {{ $company->name ?? config('app.name') }}
                                </h2>
                            @endif
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content-body" style="padding: 40px 35px; color: #333333;">

                            <h3 class="greeting-text" style="margin-top: 0; color: #111111; font-size: 20px;">
                                {{ __('Hello') }} {{ $user->name ?? __('Valued Customer') }},
                            </h3>

                            <p class="body-text" style="font-size: 15px; color: #555555; line-height: 1.6;">
                                {{ __('We received a request to log in to your account. Use the One-Time Password (OTP) below to complete your verification') }}:
                            </p>

                            <!-- OTP Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="otp-container"
                                style="margin: 30px 0;">
                                <tr>
                                    <td align="center" class="otp-box"
                                        style="background-color: #FFF5F5; border: 2px dashed #E57373; border-radius: 10px; padding: 22px 10px;">
                                        <span class="otp-code"
                                            style="font-size: 36px; font-weight: 800; letter-spacing: 10px; color: #C0392B;">
                                            {{ $otp }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <p class="expiry-text" style="font-size: 14px; color: #444444;">
                                ⏳ {{ __('This OTP is valid for 10 minutes') }}.
                            </p>

                            <p class="warning-text" style="font-size: 14px; color: #777777;">
                                {{ __('If you did not request this code, no action is needed. You can safely ignore this email.') }}
                            </p>

                            <hr class="divider" style="border: none; border-top: 1px solid #eeeeee; margin: 25px 0;">

                            <p class="signoff-text text-center" style="font-size: 14px; color: #555555;">
                                {{ __('Best regards') }}, <br>
                                {{ $company->name ?? config('app.name') }} {{ __('Team') }}
                            </p>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer-bg"
                            style="background-color: #2B2B2B; padding: 25px; text-align: center; color: #aaaaaa; font-size: 12px;">

                            @if ($company)
                                <p style="margin: 0 0 6px 0;">
                                    @if ($company->address)
                                        {{ $company->address }},
                                    @endif
                                    @if ($company->mobile)
                                        {{ $company->mobile }}
                                    @endif
                                </p>
                                @if ($company->email)
                                    <p style="margin: 0 0 10px 0;">
                                        {{ __('Support') }}: <a href="mailto:{{ $company->email }}"
                                            class="footer-link"
                                            style="color: #E57373; text-decoration: none;">{{ $company->email }}</a>
                                    </p>
                                @endif
                            @endif

                            <p style="margin: 0; color: #888888;">
                                &copy; {{ date('Y') }} {{ $company->name ?? config('app.name') }}.
                                {{ __('All Rights Reserved') }}.
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
