<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('Registration Successful') }}</title>

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
            margin: 0 0 20px;
            font-size: 15px;
            line-height: 1.6;
            color: #555555;
        }

        /* User Details Box */
        .details-table {
            width: 100%;
            background-color: #fcfcfc;
            border: 1px solid #eeeeee;
            border-radius: 8px;
            margin: 20px 0 25px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .details-table td {
            padding: 12px 18px;
            font-size: 14px;
            border-bottom: 1px solid #f0f0f0;
        }

        .details-table tr:last-child td {
            border-bottom: none;
        }

        .label-cell {
            font-weight: 600;
            color: #444444;
            width: 35%;
        }

        .value-cell {
            color: #111111;
            font-weight: 500;
        }

        .password-highlight {
            font-family: 'Courier New', Courier, monospace;
            background-color: #FFF5F5;
            color: #C0392B;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 700;
            border: 1px dashed #E57373;
        }

        /* Button */
        .btn-container {
            margin: 30px 0;
            text-align: center;
        }

        .btn-login {
            background-color: #C0392B;
            color: #ffffff !important;
            padding: 12px 28px;
            text-decoration: none;
            font-weight: 700;
            border-radius: 6px;
            display: inline-block;
            font-size: 15px;
        }

        /* Misc */
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
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="main-card"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden;">

                    <tr>
                        <td align="center" class="header-bg" style="background-color: #7A0A0A; padding: 30px 20px;">
                            @if ($company && $company->logo)
                                <img src="{{ asset('public/storage/' . $company->logo) }}"
                                    alt="{{ $company->name ?? 'Company Logo' }}" class="header-logo"
                                    style="max-height: 60px; width: auto; display: block;" border="0">
                            @else
                                <h2 class="header-title" style="color: #ffffff; margin: 0; font-size: 24px;">
                                    {{ $company->name ?? config('app.name') }}
                                </h2>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td class="content-body" style="padding: 40px 35px; color: #333333;">

                            <h3 class="greeting-text" style="margin-top: 0; color: #111111; font-size: 20px;">
                                {{ __('Congratulations') }}, {{ $user->name }}! 🎉
                            </h3>

                            <p class="body-text" style="font-size: 15px; color: #555555; line-height: 1.6;">
                                {{ __('Your account has been successfully created and verified. Below are your account details for future login references') }}:
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0" class="details-table">
                                <tr>
                                    <td class="label-cell">{{ __('Full Name') }}:</td>
                                    <td class="value-cell">{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="label-cell">{{ __('Email Address') }}:</td>
                                    <td class="value-cell">{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="label-cell">{{ __('Mobile Number') }}:</td>
                                    <td class="value-cell">{{ $user->mobile }}</td>
                                </tr>
                                <tr>
                                    <td class="label-cell">{{ __('Password') }}:</td>
                                    <td class="value-cell">
                                        <span class="password-highlight">{{ $plainPassword }}</span>
                                    </td>
                                </tr>
                            </table>

                            <div class="btn-container">
                                <a href="{{ route('login') }}" class="btn-login">
                                    {{ __('Login to Your Account') }}
                                </a>
                            </div>

                            <p style="font-size: 13px; color: #888888; line-height: 1.5; margin-bottom: 0;">
                                🔒
                                <em>{{ __('For security reasons, please do not share this email with anyone else.') }}</em>
                            </p>

                            <hr class="divider" style="border: none; border-top: 1px solid #eeeeee; margin: 25px 0;">

                            <p class="signoff-text text-center" style="font-size: 14px; color: #555555;">
                                {{ __('Best regards') }},
                                <br>
                                {{ $company->name ?? config('app.name') }} {{ __('Team') }}
                            </p>
                        </td>
                    </tr>

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
