@extends('layouts.user.app')
@section('title', __('My Profile') . ' - ' . company_name())

@section('content')
    <section class="menu-hero">
        <div class="hero-shape"></div>
        <div class="container text-center position-relative">
            <span class="hero-eyebrow">{{ company_name() }}</span>
            <h2 class="menu-hero-title">{{ __('My Profile') }}</h2>
        </div>
    </section>
    <section class="bg-cream py-6">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif
                            <h5 class="card-title mb-4">{{ __('Profile Information') }}</h5>

                            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-4 text-center">
                                    @if ($user->profile_image)
                                        <img id="profilePreview"
                                            src="{{ asset('public/storage/profile/' . $user->profile_image) }}"
                                            alt="Avatar" class="rounded-circle mb-2"
                                            style="width:96px;height:96px;object-fit:cover;">
                                    @else
                                        <img id="profilePreview" src="{{ asset('assets/img/avatar.png') }}" alt="Avatar"
                                            class="rounded-circle mb-2" style="width:96px;height:96px;object-fit:cover;">
                                    @endif
                                    <div>
                                        <label for="profile_image" class="btn btn-sm btn-outline-primary">
                                            {{ __('Change Photo') }}
                                        </label>
                                        <input type="file" name="profile_image" id="profile_image" class="d-none"
                                            accept="image/*" onchange="previewAvatar(this)">
                                    </div>
                                    @error('profile_image')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="name">{{ __('Name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        value="{{ old('name', $user->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="mobile">{{ __('Mobile') }}</label>
                                    <input type="text" name="mobile" id="mobile"
                                        class="form-control @error('mobile') is-invalid @enderror"
                                        value="{{ old('mobile', $user->mobile) }}">
                                    @error('mobile')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="email">{{ __('Email') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="email" name="email" id="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        value="{{ old('email', $user->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="address">{{ __('Address') }}</label>
                                    <textarea name="address" id="address" rows="3" class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                            </form>
                        </div>
                    </div>

                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">{{ __('Change Password') }}</h5>

                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label" for="current_password">{{ __('Current Password') }}</label>
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password">{{ __('New Password') }}</label>
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"
                                        for="password_confirmation">{{ __('Confirm New Password') }}</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control">
                                </div>

                                <button type="submit" class="btn btn-primary">{{ __('Update Password') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
