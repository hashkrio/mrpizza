@extends('layouts.app')
@section('title', __('Profile') . ' - ' . company_name())
@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row g-6 mb-6">
            <!-- Browser Default -->
            <div class="col-md">
                <div class="card">
                    <h5 class="card-header">{{ __('Profile') }}</h5>
                    <div class="card-body">

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-name">{{ __('Name') }}</label>
                                <input type="text" class="form-control" id="basic-default-name"
                                    placeholder="{{ __('Name') }}" name="name" value="{{ $user->name }}" />
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-email">{{ __('Email') }}</label>
                                <input type="email" id="basic-default-email" class="form-control"
                                    placeholder="{{ __('Email') }}" name="email" value="{{ $user->email }}" />
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label" for="basic-default-phone">{{ __('Phone Number') }}</label>
                                <input type="text" id="basic-default-phone" class="form-control"
                                    placeholder="{{ __('Phone Number') }}" name="mobile" value="{{ $user->mobile }}" />
                                @error('mobile')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <label class="form-label"
                                    for="basic-default-upload-file">{{ __('Profile picture') }}</label>
                                <input type="file" class="form-control" id="basic-default-upload-file"
                                    name="profile_image" />
                                @error('profile_image')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-6">
                                <div class="mb-6">
                                    @if ($user->profile_image)
                                        <img src="{{ asset('public/storage/profile/' . $user->profile_image) }}"
                                            width="100">
                                    @endif
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">{{ __('Update Profile') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /Browser Default -->

            <div class="col-md">
                <div class="card">
                    <h5 class="card-header">{{ __('Change Password') }}</h5>
                    <div class="card-body">

                        @if (session('password_success'))
                            <div class="alert alert-success">
                                {{ session('password_success') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.profile.password') }}" method="POST">
                            @csrf
                            <div class="mb-6">
                                <label class="form-label" for="current_password">{{ __('Current Password') }}</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" class="form-control" id="current_password"
                                        name="current_password" placeholder="••••••••" />
                                    <span class="input-group-text cursor-pointer toggle-password"
                                        data-target="current_password">
                                        <i class="icon-base ti tabler-eye-off"></i>
                                    </span>
                                </div>
                                @error('current_password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="form-label" for="password">{{ __('New Password') }}</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="••••••••" />
                                    <span class="input-group-text cursor-pointer toggle-password" data-target="password">
                                        <i class="icon-base ti tabler-eye-off"></i>
                                    </span>
                                </div>
                                @error('password')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label class="form-label"
                                    for="password_confirmation">{{ __('Confirm New Password') }}</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="••••••••" />
                                    <span class="input-group-text cursor-pointer toggle-password"
                                        data-target="password_confirmation">
                                        <i class="icon-base ti tabler-eye-off"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">{{ __('Update Password') }}</button>
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
