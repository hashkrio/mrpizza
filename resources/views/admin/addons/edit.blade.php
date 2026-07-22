@extends('layouts.app')
@section('title', __('Edit Addon') . ' - ' . company_name())

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">{{ __('Edit Addon') }}</h4>
            <a href="{{ route('admin.addons.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> {{ __('Back') }}
            </a>
        </div>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.addons.update', $addon->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="category_id">{{ __('Category') }} <span
                                    class="text-danger">*</span></label>
                            <select name="category_id" id="category_id"
                                class="form-select select2 @error('category_id') is-invalid @enderror">
                                <option value="">{{ __('Select category') }}</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $addon->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="name">{{ __('Addon Name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $addon->name) }}" placeholder="{{ __('Enter addon name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="status">{{ __('Status') }} <span
                                    class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select select2 @error('status') is-invalid @enderror">
                                <option value="1" {{ old('status', $addon->status) == 1 ? 'selected' : '' }}>
                                    {{ __('Active') }}</option>
                                <option value="0" {{ old('status', $addon->status) == 0 ? 'selected' : '' }}>
                                    {{ __('Inactive') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="card border mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">{{ __('Prices per Country') }}</h6>

                            <div class="row">
                                @foreach ($locales as $code => $label)
                                    <div class="col-md-3 mb-3">
                                        <label class="form-label fw-bold">{{ $label }}</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0"
                                                name="price[{{ $code }}]"
                                                class="form-control @error('price.' . $code) is-invalid @enderror"
                                                value="{{ old('price.' . $code, $addon->price[$code] ?? '') }}"
                                                placeholder="{{ __('Price for') }} {{ $label }}">
                                        </div>
                                        @error('price.' . $code)
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        <a href="{{ route('admin.addons.index') }}"
                            class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
