{{-- resources/views/admin/items/edit.blade.php --}}
@extends('layouts.app')
@section('title', __('Edit Item') . ' - ' . company_name())

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">{{ __('Edit Item') }}</h4>
            <a href="{{ route('admin.items.index') }}" class="btn btn-outline-secondary">
                <i class="icon-base ti tabler-arrow-left me-1"></i> {{ __('Back') }}
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @php
                        $oldSizes = old('sizes', $item->sizes ?? []);
                        $hasSizes = old('has_sizes', $item->has_sizes) ? true : false;
                        $priceData = old('price', $item->price ?? []);
                    @endphp
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="category_id">{{ __('Category') }} <span
                                    class="text-danger">*</span></label>
                            <select name="category_id" id="category_id"
                                class="form-select  select2 @error('category_id') is-invalid @enderror">
                                <option value="">{{ __('Select category') }}</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $item->category_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="name">{{ __('Name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="name" id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $item->name) }}" placeholder="{{ __('Enter item name') }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">{{ __('Description') }}</label>
                        <textarea name="description" id="description" rows="3"
                            class="form-control @error('description') is-invalid @enderror" placeholder="{{ __('Enter description') }}">{{ old('description', $item->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="has_sizes" name="has_sizes"
                            value="1" {{ $hasSizes ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_sizes">{{ __('This item has sizes') }}</label>
                    </div>

                    <div class="mb-3 sizes-wrapper {{ $hasSizes ? '' : 'd-none' }}">
                        <label class="form-label d-block">{{ __('Available Sizes') }}</label>
                        @foreach (['small' => __('Small'), 'medium' => __('Medium'), 'large' => __('Large')] as $key => $label)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input size-checkbox" type="checkbox" name="sizes[]"
                                    value="{{ $key }}" id="size_{{ $key }}"
                                    {{ collect($oldSizes)->contains($key) ? 'checked' : '' }}>
                                <label class="form-check-label" for="size_{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>

                    <div class="card border mb-3">
                        <div class="card-body">
                            <h6 class="mb-3">{{ __('Prices per Country') }}</h6>

                            @foreach ($locales as $loc => $label)
                                @php
                                    $locPrice = $priceData[$loc] ?? null;
                                    $singleVal = is_array($locPrice) ? '' : $locPrice;
                                @endphp
                                <div class="mb-3">
                                    <label class="form-label fw-bold">{{ $label }}</label>

                                    <div
                                        class="price-single price-single-{{ $loc }} {{ $hasSizes ? 'd-none' : '' }}">
                                        <input type="number" step="0.01" min="0"
                                            name="price[{{ $loc }}]" class="form-control"
                                            value="{{ $singleVal }}"
                                            placeholder="{{ __('Price for') }} {{ $label }}">
                                    </div>

                                    <div
                                        class="row price-sized price-sized-{{ $loc }} {{ $hasSizes ? '' : 'd-none' }}">
                                        @foreach (['small' => __('Small'), 'medium' => __('Medium'), 'large' => __('Large')] as $key => $sizeLabel)
                                            @php
                                                $sizeVal = is_array($locPrice) ? $locPrice[$key] ?? '' : '';
                                                $showField = collect($oldSizes)->contains($key);
                                            @endphp
                                            <div
                                                class="col-md-4 mb-2 price-size-field price-size-{{ $key }} {{ $showField ? '' : 'd-none' }}">
                                                <label class="form-label small">{{ $sizeLabel }}</label>
                                                <input type="number" step="0.01" min="0"
                                                    name="price[{{ $loc }}][{{ $key }}]"
                                                    class="form-control" value="{{ $sizeVal }}"
                                                    placeholder="{{ $sizeLabel }}">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="status">{{ __('Status') }} <span
                                    class="text-danger">*</span></label>
                            <select name="status" id="status"
                                class="form-select select2 @error('status') is-invalid @enderror">
                                <option value="1" {{ old('status', $item->status) == 1 ? 'selected' : '' }}>
                                    {{ __('Active') }}</option>
                                <option value="0"
                                    {{ old('status', $item->status) === 0 || old('status') === '0' ? 'selected' : '' }}>
                                    {{ __('Inactive') }}</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="image">{{ __('Image') }}</label>
                            <input type="file" name="image" id="image"
                                class="form-control @error('image') is-invalid @enderror" accept="image/*"
                                onchange="readURL(this)">
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="mt-3">
                                <img id="imgPreview"
                                    src="{{ $item->image ? asset('public/storage/' . $item->image) : '#' }}"
                                    alt="Preview" class="rounded {{ $item->image ? '' : 'd-none' }}"
                                    style="width:100px;height:100px;object-fit:cover;">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                        <a href="{{ route('admin.items.index') }}"
                            class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js_script')
    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imgPreview').attr('src', e.target.result).removeClass('d-none');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        $(function() {
            function toggleSizeUI() {
                var hasSizes = $('#has_sizes').is(':checked');

                if (hasSizes) {
                    $('.sizes-wrapper').removeClass('d-none');
                    $('.price-single').addClass('d-none');
                    $('.price-sized').removeClass('d-none');

                    // single inputs must NOT submit
                    $('.price-single').find('input').prop('disabled', true);
                    $('.price-sized').find('input').prop('disabled', false);
                } else {
                    $('.sizes-wrapper').addClass('d-none');
                    $('.price-single').removeClass('d-none');
                    $('.price-sized').addClass('d-none');

                    // sized inputs must NOT submit
                    $('.price-single').find('input').prop('disabled', false);
                    $('.price-sized').find('input').prop('disabled', true);
                }
                syncSizeFields();
            }

            function syncSizeFields() {
                if (!$('#has_sizes').is(':checked')) return;

                var selected = $('.size-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                ['small', 'medium', 'large'].forEach(function(size) {
                    var $field = $('.price-size-' + size);
                    if (selected.includes(size)) {
                        $field.removeClass('d-none');
                        $field.find('input').prop('disabled', false);
                    } else {
                        $field.addClass('d-none');
                        $field.find('input').prop('disabled', true);
                    }
                });
            }

            $('#has_sizes').on('change', toggleSizeUI);
            $('.size-checkbox').on('change', syncSizeFields);

            toggleSizeUI();
        });
    </script>
@endpush
