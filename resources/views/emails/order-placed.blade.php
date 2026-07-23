<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ __('Order Confirmation') }}</title>

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

        /* Order number badge */
        .order-box {
            background-color: #FFF5F5;
            border: 2px dashed #E57373;
            border-radius: 10px;
            padding: 20px 10px;
            text-align: center;
        }

        .order-no {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 3px;
            color: #C0392B;
            font-family: 'Courier New', Courier, monospace;
            display: block;
        }

        .order-date {
            font-size: 13px;
            color: #888888;
            display: block;
            margin-top: 6px;
        }

        /* Section titles */
        .block-title {
            font-size: 13px;
            font-weight: 700;
            color: #820300;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #f0e0e0;
        }

        /* Items */
        .item-row td {
            padding: 12px 0;
            border-bottom: 1px solid #eeeeee;
            vertical-align: top;
        }

        .item-name {
            font-size: 15px;
            font-weight: 600;
            color: #222222;
            margin: 0 0 4px;
        }

        .item-size {
            display: inline-block;
            background-color: #FFF6E4;
            color: #2E8B4E;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 2px 9px;
            border-radius: 20px;
            margin-left: 6px;
        }

        .item-meta {
            font-size: 13px;
            color: #888888;
            margin: 0;
        }

        .item-addon {
            font-size: 12px;
            color: #666666;
            margin: 5px 0 0;
            line-height: 1.5;
        }

        .item-price {
            font-size: 15px;
            font-weight: 700;
            color: #C0392B;
            text-align: right;
            white-space: nowrap;
        }

        /* Summary */
        .sum-row td {
            padding: 6px 0;
            font-size: 14px;
            color: #666666;
        }

        .sum-row td.val {
            text-align: right;
            color: #222222;
            font-weight: 600;
        }

        .sum-total td {
            padding: 14px 0 0;
            font-size: 18px;
            font-weight: 800;
            color: #111111;
            border-top: 2px solid #eeeeee;
        }

        .sum-total td.val {
            text-align: right;
            color: #2E8B4E;
        }

        /* Info panel */
        .info-panel {
            background-color: #FAFAFA;
            border-radius: 10px;
            padding: 20px;
        }

        .info-line {
            margin: 0 0 6px;
            font-size: 14px;
            color: #444444;
            line-height: 1.5;
        }

        .info-label {
            color: #999999;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .note-text {
            margin: 10px 0 0;
            font-size: 13px;
            color: #777777;
            font-style: italic;
            line-height: 1.5;
        }

        .pay-badge {
            display: inline-block;
            background-color: #EAF6EE;
            color: #237A40;
            font-size: 13px;
            font-weight: 600;
            padding: 7px 16px;
            border-radius: 20px;
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
        $sym = $order->currency_symbol;
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
                                {{ __('Hello') }} {{ $order->name }},
                            </h3>

                            <p class="body-text" style="font-size: 15px; color: #555555; line-height: 1.6;">
                                {{ __('Thank you for your order! We have received it and our kitchen is getting started. Here are your order details') }}:
                            </p>

                            <!-- Order Number Box -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                style="margin: 30px 0;">
                                <tr>
                                    <td align="center" class="order-box"
                                        style="background-color: #FFF5F5; border: 2px dashed #E57373; border-radius: 10px; padding: 20px 10px;">
                                        <span class="order-no"
                                            style="font-size: 24px; font-weight: 800; letter-spacing: 3px; color: #C0392B;">
                                            {{ $order->order_no }}
                                        </span>
                                        <span class="order-date" style="font-size: 13px; color: #888888;">
                                            {{ $order->created_at->format('d M Y, h:i A') }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Items -->
                            <p class="block-title" style="font-size: 13px; color: #820300; letter-spacing: 1px;">
                                {{ __('Order Items') }}
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                @foreach ($order->items as $item)
                                    <tr class="item-row">
                                        <td style="padding: 12px 0; border-bottom: 1px solid #eeeeee;">
                                            <p class="item-name"
                                                style="font-size: 15px; font-weight: 600; color: #222222; margin: 0 0 4px;">
                                                {{ $item->item_name }}
                                                @if ($item->size)
                                                    <span class="item-size"
                                                        style="background-color: #FFF6E4; color: #2E8B4E; font-size: 11px; padding: 2px 9px; border-radius: 20px;">
                                                        {{ ucfirst($item->size) }}
                                                    </span>
                                                @endif
                                            </p>

                                            <p class="item-meta" style="font-size: 13px; color: #888888; margin: 0;">
                                                {{ $sym }}{{ number_format($item->item_price, 2) }} &times;
                                                {{ $item->qty }}
                                            </p>

                                            @if (count($item->addons))
                                                <p class="item-addon"
                                                    style="font-size: 12px; color: #666666; margin: 5px 0 0;">
                                                    + @foreach ($item->addons as $addon)
                                                        {{ $addon->addon_name }}
                                                        ({{ $sym }}{{ number_format($addon->addon_price, 2) }})
                                                        {{ !$loop->last ? ', ' : '' }}
                                                    @endforeach
                                                </p>
                                            @endif

                                            @if ($item->item_note)
                                                <p class="note-text"
                                                    style="font-size: 13px; color: #777777; font-style: italic; margin: 6px 0 0;">
                                                    {{ $item->item_note }}
                                                </p>
                                            @endif
                                        </td>

                                        <td class="item-price"
                                            style="padding: 12px 0; border-bottom: 1px solid #eeeeee; text-align: right; font-size: 15px; font-weight: 700; color: #C0392B;">
                                            {{ $sym }}{{ number_format($item->total, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            <!-- Summary -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%"
                                style="margin-top: 22px;">
                                <tr class="sum-row">
                                    <td style="padding: 6px 0; font-size: 14px; color: #666666;">
                                        {{ __('Items Total') }}</td>
                                    <td class="val"
                                        style="padding: 6px 0; text-align: right; font-size: 14px; color: #222222; font-weight: 600;">
                                        {{ $sym }}{{ number_format($order->items_total, 2) }}
                                    </td>
                                </tr>

                                @if ($order->addons_total > 0)
                                    <tr class="sum-row">
                                        <td style="padding: 6px 0; font-size: 14px; color: #666666;">
                                            {{ __('Addons') }}</td>
                                        <td class="val"
                                            style="padding: 6px 0; text-align: right; font-size: 14px; color: #222222; font-weight: 600;">
                                            {{ $sym }}{{ number_format($order->addons_total, 2) }}
                                        </td>
                                    </tr>
                                @endif

                                @if ($order->discount > 0)
                                    <tr class="sum-row">
                                        <td style="padding: 6px 0; font-size: 14px; color: #666666;">
                                            {{ __('Discount') }}</td>
                                        <td class="val"
                                            style="padding: 6px 0; text-align: right; font-size: 14px; color: #2E8B4E; font-weight: 600;">
                                            -{{ $sym }}{{ number_format($order->discount, 2) }}
                                        </td>
                                    </tr>
                                @endif

                                @if ($order->delivery_charge > 0)
                                    <tr class="sum-row">
                                        <td style="padding: 6px 0; font-size: 14px; color: #666666;">
                                            {{ __('Delivery') }}</td>
                                        <td class="val"
                                            style="padding: 6px 0; text-align: right; font-size: 14px; color: #222222; font-weight: 600;">
                                            {{ $sym }}{{ number_format($order->delivery_charge, 2) }}
                                        </td>
                                    </tr>
                                @endif

                                @if ($order->tax > 0)
                                    <tr class="sum-row">
                                        <td style="padding: 6px 0; font-size: 14px; color: #666666;">
                                            {{ __('Tax') }}</td>
                                        <td class="val"
                                            style="padding: 6px 0; text-align: right; font-size: 14px; color: #222222; font-weight: 600;">
                                            {{ $sym }}{{ number_format($order->tax, 2) }}
                                        </td>
                                    </tr>
                                @endif

                                <tr class="sum-total">
                                    <td
                                        style="padding: 14px 0 0; border-top: 2px solid #eeeeee; font-size: 18px; font-weight: 800; color: #111111;">
                                        {{ __('Total') }}
                                    </td>
                                    <td class="val"
                                        style="padding: 14px 0 0; border-top: 2px solid #eeeeee; text-align: right; font-size: 18px; font-weight: 800; color: #2E8B4E;">
                                        {{ $sym }}{{ number_format($order->total, 2) }}
                                    </td>
                                </tr>
                            </table>

                            <p class="text-center" style="margin: 20px 0 0; text-align: center;">
                                <span class="pay-badge"
                                    style="background-color: #EAF6EE; color: #237A40; font-size: 13px; font-weight: 600; padding: 7px 16px; border-radius: 20px;">
                                    {{ strtoupper($order->payment_method) }} &mdash;
                                    {{ __(ucfirst($order->payment_status)) }}
                                </span>
                            </p>

                            <hr class="divider" style="border: none; border-top: 1px solid #eeeeee; margin: 25px 0;">

                            <!-- Delivery Details -->
                            <p class="block-title" style="font-size: 13px; color: #820300; letter-spacing: 1px;">
                                {{ __('Delivery Details') }}
                            </p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td class="info-panel"
                                        style="background-color: #FAFAFA; border-radius: 10px; padding: 20px;">
                                        <p class="info-line"
                                            style="margin: 0 0 6px; font-size: 14px; color: #444444;">
                                            <strong>{{ $order->name }}</strong>
                                        </p>
                                        <p class="info-line"
                                            style="margin: 0 0 6px; font-size: 14px; color: #444444;">
                                            {{ $order->mobile }}
                                        </p>
                                        @if ($order->email)
                                            <p class="info-line"
                                                style="margin: 0 0 6px; font-size: 14px; color: #444444;">
                                                {{ $order->email }}
                                            </p>
                                        @endif
                                        <p class="info-line" style="margin: 0; font-size: 14px; color: #666666;">
                                            {{ $order->address }}
                                        </p>

                                        @if ($order->order_note)
                                            <p class="note-text"
                                                style="margin: 12px 0 0; font-size: 13px; color: #777777; font-style: italic;">
                                                <span class="info-label"
                                                    style="color: #999999; font-size: 12px;">{{ __('Note') }}:</span>
                                                {{ $order->order_note }}
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <hr class="divider" style="border: none; border-top: 1px solid #eeeeee; margin: 25px 0;">

                            <p class="signoff-text text-center"
                                style="font-size: 14px; color: #555555; text-align: center;">
                                {{ __('Thank you for choosing us!') }} <br>
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
