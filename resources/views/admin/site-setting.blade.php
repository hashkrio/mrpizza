@extends('layouts.app')
@section('title', __('Site Settings') . ' - ' . company_name())

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row g-6 mb-6">
            <div class="col-md">
                <div class="card">
                    <h5 class="card-header">{{ __('Site Settings') }}</h5>
                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.site.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-6">
                                <label class="form-label" for="company-name">{{ __('Company Name') }}</label>
                                <input type="text" class="form-control" id="company-name"
                                    placeholder="{{ __('Company Name') }}" name="name"
                                    value="{{ old('name', $setting->name) }}" />
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="company-email">{{ __('Email') }}</label>
                                <input type="email" class="form-control" id="company-email"
                                    placeholder="{{ __('Email') }}" name="email"
                                    value="{{ old('email', $setting->email) }}" />
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="company-mobile">{{ __('Mobile') }}</label>
                                <input type="text" class="form-control" id="company-mobile"
                                    placeholder="{{ __('Mobile') }}" name="mobile"
                                    value="{{ old('mobile', $setting->mobile) }}" />
                                @error('mobile')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="company-address">{{ __('Address') }}</label>
                                <textarea class="form-control" id="company-address" rows="3" placeholder="{{ __('Address') }}" name="address">{{ old('address', $setting->address) }}</textarea>
                                @error('address')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Currency symbol per language --}}
                            <div class="card border mb-6">
                                <div class="card-body">
                                    <h6 class="mb-3">{{ __('Currency Symbol per Language') }}</h6>

                                    <div class="row">
                                        @foreach ($locales as $code => $label)
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label fw-bold" for="currency_{{ $code }}">{{ $label }}</label>
                                                <input type="text" id="currency_{{ $code }}"
                                                    name="currency_symbol[{{ $code }}]"
                                                    class="form-control @error('currency_symbol.'.$code) is-invalid @enderror"
                                                    value="{{ old('currency_symbol.'.$code, $setting->currency_symbols[$code] ?? '') }}"
                                                    placeholder="{{ __('e.g.') }} {{ $code === 'pt' ? '€' : '£' }}">
                                                @error('currency_symbol.'.$code)
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="company-logo">{{ __('Logo') }}</label>
                                <input type="file" class="form-control" id="company-logo" name="logo" />
                                @error('logo')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6">
                                @if ($setting->logo)
                                    <img src="{{ asset('public/storage/company/' . $setting->logo) }}" width="120">
                                @endif
                            </div>


                            <div class="mb-6">
                                <label class="form-label" for="company-favicon">{{ __('Favicon') }}</label>
                                <input type="file" class="form-control" id="company-favicon" name="favicon" />
                                @error('favicon')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6">
                                @if ($setting->favicon)
                                    <img src="{{ asset('public/storage/company/' . $setting->favicon) }}" width="20">
                                @endif
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="company-login-cover">{{ __('Login Cover') }}</label>
                                <input type="file" class="form-control" id="company-login-cover" name="login_cover" />
                                @error('login_cover')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6">
                                @if ($setting->login_cover)
                                    <img src="{{ asset('public/storage/company/' . $setting->login_cover) }}" width="120">
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">{{ __('Update Settings') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js_script')
    <script></script>
@endpush