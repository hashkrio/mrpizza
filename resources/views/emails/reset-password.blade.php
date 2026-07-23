<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('Reset Your Password') }}</title>

    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            background-color: #f4f5f8 !important;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }

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

        .btn-container {
            margin: 32px 0;
        }

        .reset-btn {
            display: inline-block;
            background-color: #C0392B;
            color: #ffffff !important;
            font-size: 16px;
            font-weight: 700;
            text-decoration: none;
            padding: 15px 42px;
            border-radius: 8px;
        }

        .link-fallback {
            margin: 0 0 12px;
            font-size: 13px;
            color: #777777;
            line-height: 1.6;
            word-break: break-all;
        }

        .link-fallback a {
            color: #C0392B;
            text-decoration: none;
        }

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
                                {{ __('We received a request to reset the password for your account. Click the button below to choose a new password') }}:
                            </p>

                            <!-- Reset Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="btn-container"
                                style="margin: 32px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $link }}" class="reset-btn" target="_blank"
                                            style="display: inline-block; background-color: #C0392B; color: #ffffff; font-size: 16px; font-weight: 700; text-decoration: none; padding: 15px 42px; border-radius: 8px;">
                                            {{ __('Reset Password') }}
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p class="link-fallback" style="font-size: 13px; color: #777777; word-break: break-all;">
                                {{ __('If the button does not work, copy and paste this link into your browser') }}:<br>
                                <a href="{{ $link }}" style="color: #C0392B; text-decoration: none;">{{ $link }}</a>
                            </p>

                            <p class="expiry-text" style="font-size: 14px; color: #444444;">
                                ⏳ {{ __('This link is valid for 60 minutes') }}.
                            </p>

                            <p class="warning-text" style="font-size: 14px; color: #777777;">
                                {{ __('If you did not request a password reset, no action is needed. Your password will remain unchanged.') }}
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
                                        {{ __('Support') }}: <a href="mailto:{{ $company->email }}" class="footer-link"
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