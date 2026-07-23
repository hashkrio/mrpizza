@extends('layouts.user.app')

@section('title', __('Item Detail') . ' - ' . company_name())

@php
    $sizeOrder = ['small', 'medium', 'large'];
    $isValid = fn($v) => $v !== null && $v !== '';
@endphp

@section('content')

    {{-- Small hero strip --}}
    <section class="menu-hero">
        <div class="hero-shape"></div>
        <div class="container text-center position-relative">
            <span class="hero-eyebrow">{{ company_name() }}</span>
            <h2 class="menu-hero-title">{{ $item['name'] }}</h2>
        </div>
    </section>

    <section class="detail-body py-6">
        <div class="container">

            {{-- Detail content --}}
            @php
                $img = $item['image'] ?? $fallback;
                $price = $item['price'];
                $isSized = is_array($price);

                // Valid sizes preserved in preferred order
                $validSizes = [];
                if ($isSized) {
                    foreach ($sizeOrder as $s) {
                        if (isset($price[$s]) && $isValid($price[$s])) {
                            $validSizes[$s] = $price[$s];
                        }
                    }
                    // include any non-standard sizes too
                    foreach ($price as $s => $amt) {
                        if (!isset($validSizes[$s]) && $isValid($amt)) {
                            $validSizes[$s] = $amt;
                        }
                    }
                }

                $hasSingle = !$isSized && $isValid($price);
                $canAddToCart = $hasSingle || ($isSized && count($validSizes));
            @endphp

            <div class="row gy-4 align-items-start detail-card">
                <div class="col-lg-6">
                    <div class="detail-img">
                        <img src="{{ $img }}" alt="{{ $item['name'] }}">
                        @if (!empty($item['category']))
                            <span class="dish-cat">{{ $item['category'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="detail-title">{{ $item['name'] }}</h2>

                    @if (!empty($item['category']))
                        <p class="detail-cat">
                            <strong>{{ __('Category') }}:</strong> {{ $item['category'] }}
                        </p>
                    @endif

                    @if (!empty($item['description']))
                        <p class="detail-desc">{{ $item['description'] }}</p>
                    @endif

                    {{-- Available prices --}}
                    <div class="detail-price-wrap mb-3">
                        @if ($isSized)
                            @if (count($validSizes))
                                @foreach ($validSizes as $size => $amt)
                                    <span class="dish-price">
                                        <span class="size">{{ ucfirst($size) }}</span>
                                        {{ $symbol }}{{ number_format((float) $amt, 2) }}
                                    </span>
                                @endforeach
                            @else
                                <span class="dish-price text-muted">{{ __('Price on request') }}</span>
                            @endif
                        @elseif ($hasSingle)
                            <span class="dish-price">{{ $symbol }}{{ number_format((float) $price, 2) }}</span>
                        @else
                            <span class="dish-price text-muted">{{ __('Price on request') }}</span>
                        @endif
                    </div>

                    {{-- Add to cart controls --}}
                    @if ($canAddToCart)
                        <div class="detail-add-wrap">
                            @if ($isSized)
                                {{-- Size selection --}}
                                <div class="mb-3">
                                    <label class="form-label d-block mb-2">{{ __('Choose a size') }}</label>
                                    <div class="size-options d-flex flex-wrap gap-2" id="sizeOptions">
                                        @foreach ($validSizes as $size => $amt)
                                            <button type="button" class="btn btn-outline-brand size-btn"
                                                data-size="{{ $size }}">
                                                {{ ucfirst($size) }} —
                                                {{ $symbol }}{{ number_format((float) $amt, 2) }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="d-flex align-items-center gap-2">
                                <input type="number" min="1" value="1" id="addQty" class="form-control"
                                    style="width:90px;">
                                <button type="button" class="btn btn-brand" id="addToCartBtn"
                                    data-id="{{ $item['hash'] }}" data-sized="{{ $isSized ? '1' : '0' }}">
                                    {{ __('Add to Cart') }}
                                </button>
                            </div>
                        </div>
                    @else
                        <button type="button" class="btn btn-brand mt-3" disabled>
                            {{ __('Currently unavailable') }}
                        </button>
                    @endif
                </div>
            </div>

            {{-- Related items --}}
            @if ($related->count())
                <div class="mt-6">
                    <h3 class="text-center mb-4">{{ __('You may also like') }}</h3>
                    <div class="row gy-4">
                        @foreach ($related as $r)
                            @php
                                $rUrl = route('menu.detail', ['id' => $r['hash']]);
                                $rImg = $r['image'] ?? $fallback;
                                $rPrice = $r['price'];

                                $base = null;
                                $rIsSized = is_array($rPrice);
                                if ($rIsSized) {
                                    foreach ($sizeOrder as $s) {
                                        if (isset($rPrice[$s]) && $isValid($rPrice[$s])) {
                                            $base = ['size' => $s, 'amount' => $rPrice[$s]];
                                            break;
                                        }
                                    }
                                    if (!$base) {
                                        foreach ($rPrice as $s => $a) {
                                            if (
                                                $isValid($a) &&
                                                ($base === null || (float) $a < (float) $base['amount'])
                                            ) {
                                                $base = ['size' => $s, 'amount' => $a];
                                            }
                                        }
                                    }
                                } elseif ($isValid($rPrice)) {
                                    $base = ['single' => true, 'amount' => $rPrice];
                                }

                                $rCanQuickAdd = $base && isset($base['single']);
                            @endphp
                            <div class="col-sm-6 col-md-3">
                                <div class="dish-card">
                                    <a href="{{ $rUrl }}" class="dish-img">
                                        <img src="{{ $rImg }}" alt="{{ $r['name'] }}">
                                    </a>
                                    <div class="dish-body">
                                        <h4 class="dish-title">
                                            <a href="{{ $rUrl }}">{{ $r['name'] }}</a>
                                        </h4>
                                        <div class="dish-price-wrap">
                                            @if ($base && isset($base['single']))
                                                <span
                                                    class="dish-price">{{ $symbol }}{{ number_format((float) $base['amount'], 2) }}</span>
                                            @elseif ($base)
                                                <span class="dish-price">
                                                    {{ $symbol }}{{ number_format((float) $base['amount'], 2) }}
                                                    <span class="size">({{ ucfirst($base['size']) }})</span>
                                                </span>
                                            @else
                                                <span class="dish-price text-muted">{{ __('Price on request') }}</span>
                                            @endif
                                        </div>

                                        <div class="d-flex gap-2 mt-2">
                                            <a href="{{ $rUrl }}" class="btn btn-outline-brand btn-sm flex-fill">
                                                {{ __('View Details') }}
                                            </a>
                                            @if ($rCanQuickAdd)
                                                <button type="button" class="btn btn-brand btn-sm js-add-to-cart"
                                                    data-id="{{ $r['hash'] }}" title="{{ __('Add to Cart') }}">
                                                    <i class="icon-base ti tabler-shopping-cart-plus"></i>
                                                </button>
                                            @elseif ($base)
                                                <a href="{{ $rUrl }}" class="btn btn-brand btn-sm"
                                                    title="{{ __('Choose a size') }}">
                                                    <i class="icon-base ti tabler-shopping-cart-plus"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>

@endsection

@push('js_script')
<script>
    var token = '{{ csrf_token() }}';
    var selectedSize = null;

    // ---- Pick a size (main item) ----
    $('.size-btn').on('click', function () {
        $('.size-btn').removeClass('active');
        $(this).addClass('active');
        selectedSize = $(this).data('size');
    });

    // ---- Add to cart (main item) ----
    $('#addToCartBtn').on('click', function () {

        var button = $(this);
        var id = button.data('id');
        var sized = button.data('sized') == '1';
        var qty = parseInt($('#addQty').val()) || 1;

        // Sized items need a size chosen first
        if (sized && !selectedSize) {
            Swal.fire({
                icon: 'warning',
                title: '{{ __("Select Size") }}',
                text: '{{ __("Please select a size.") }}',
                confirmButtonText: '{{ __("OK") }}'
            });
            return;
        }

        button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '{{ route("cart.add") }}',
            data: {
                id: id,
                size: selectedSize,
                qty: qty,
                _token: token
            },

            success: function (response) {

                if (typeof updateCartBadge === 'function') {
                    updateCartBadge(response.count);
                }

                Swal.fire({
                    icon: 'success',
                    title: '{{ __("Success") }}',
                    text: response.message,
                    timer: 1800,
                    showConfirmButton: false
                });

                button.prop('disabled', false);
            },

            error: function (xhr) {

                let msg = '{{ __("Something went wrong.") }}';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: '{{ __("Error") }}',
                    text: msg,
                    confirmButtonText: '{{ __("OK") }}'
                });

                button.prop('disabled', false);
            }
        });

    });

    // ---- Quick add (related items) ----
    $('.js-add-to-cart').on('click', function () {

        var button = $(this);
        var id = button.data('id');

        button.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '{{ route("cart.add") }}',
            data: {
                id: id,
                qty: 1,
                _token: token
            },

            success: function (response) {

                if (typeof updateCartBadge === 'function') {
                    updateCartBadge(response.count);
                }

                button.html('<i class="icon-base ti tabler-check"></i>');

                Swal.fire({
                    toast: true,
                    position: 'top-center',
                    icon: 'success',
                    title: response.message,
                    showConfirmButton: false,
                    timer: 1500,
                    timerProgressBar: true
                });

                setTimeout(function () {
                    button.html('<i class="icon-base ti tabler-shopping-cart-plus"></i>');
                }, 1200);

                button.prop('disabled', false);
            },

            error: function (xhr) {

                let msg = '{{ __("Something went wrong.") }}';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: '{{ __("Error") }}',
                    text: msg,
                    confirmButtonText: '{{ __("OK") }}'
                });

                button.prop('disabled', false);
            }
        });

    });
</script>
@endpush
