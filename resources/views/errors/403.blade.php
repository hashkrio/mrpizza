@extends('layouts.user.app')

@section('title', __('403 - Forbidden') . ' - ' . company_name())

@push('css_script')
    <style>
        .error-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .error-card {
            background-color: #ffffff;
            max-width: 550px;
            width: 100%;
            padding: 50px 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 19px 22px rgba(1, 0, 0, 0.09);
        }

        .error-code {
            font-size: 110px;
            font-weight: 900;
            color: #C0392B;
            line-height: 1;
            margin-bottom: 10px;
            letter-spacing: 2px;
            text-shadow: 2px 2px 0px #FFF5F5;
        }

        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 12px;
        }

        .error-description {
            font-size: 15px;
            color: #666666;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .btn-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 12px 26px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .btn-primary {
            background-color: #7A0A0A;
            color: #ffffff;
            border: 2px solid #7A0A0A;
        }

        .btn-primary:hover {
            background-color: #C0392B;
            border-color: #C0392B;
        }

        .btn-secondary {
            background-color: transparent;
            color: #555555;
            border: 2px solid #dddddd;
        }

        .btn-secondary:hover {
            border-color: #999999;
            color: #111111;
        }

        .error-footer {
            background-color: #2B2B2B;
            color: #aaaaaa;
            text-align: center;
            padding: 20px;
            font-size: 12px;
        }

        .error-footer a {
            color: #E57373;
            text-decoration: none;
        }
    </style>
@endpush

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <main class="error-container">
                <div class="error-card">

                    <div class="error-code">403</div>

                    <h2 class="error-title">
                        {{ __('Access Denied') }}
                    </h2>

                    <p class="error-description">
                        {{ __("Sorry, you don't have permission to access this page. If you believe this is an error, please contact the administrator.") }}
                    </p>

                    <div class="btn-group">
                        <a href="{{ url('/') }}" class="btn btn-primary">
                            {{ __('Back to Homepage') }}
                        </a>

                        <a href="javascript:history.back()" class="btn btn-primary">
                            {{ __('Go Back') }}
                        </a>
                    </div>

                </div>
            </main>
        </div>
    </div>
@endsection
