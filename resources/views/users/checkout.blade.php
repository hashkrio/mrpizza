@extends('layouts.user.app')

@section('title', __('Checkout') . ' - ' . company_name())

@section('content')

    {{-- Hero --}}
    <section class="menu-hero">
        <div class="hero-shape"></div>
        <div class="container text-center position-relative">
            <span class="hero-eyebrow">{{ company_name() }}</span>
            <h2 class="menu-hero-title">{{ __('Checkout') }}</h2>
        </div>
    </section>

    <section class="cart-body py-6">
        <div class="container" id="checkoutWrap">
            <div class="row g-4">

                {{-- Left: items + customer form --}}
                <div class="col-lg-8">

                    {{-- Order items --}}
                    <div class="cart-panel mb-4">
                        <div class="cart-panel-head">
                            <h5>{{ __('Order Items') }}
                                <span class="text-muted fw-normal">({{ count($items) }})</span>
                            </h5>
                            <a href="{{ route('cart') }}" class="cart-clear-btn">
                                <i class="icon-base ti tabler-edit"></i>{{ __('Back to Cart') }}
                            </a>
                        </div>

                        @foreach ($items as $row)
                            @php
                                $lineAddons = $addonsByCategory[$row['category_id']] ?? [];
                                $selectedIds = collect($row['addons'])->pluck('id')->all();
                            @endphp
                            <div class="cart-item" data-key="{{ $row['key'] }}" data-price="{{ $row['price'] }}"
                                data-addon-total="{{ $row['addon_total'] }}">
                                <div class="cart-item-thumb">
                                    <img src="{{ $row['image'] ?? $fallback }}"
                                        onerror="this.onerror=null;this.src='{{ $fallback }}';"
                                        alt="{{ $row['name'] }}">
                                </div>

                                <div class="cart-item-info">
                                    <h6 class="cart-item-name">{{ $row['name'] }}</h6>
                                    @if (!empty($row['size']))
                                        <span class="cart-item-size">{{ ucfirst($row['size']) }}</span><br>
                                    @endif
                                    <span class="cart-item-unit">
                                        {{ $symbol }}{{ number_format($row['price'], 2) }} {{ __('each') }}
                                    </span>
                                </div>

                                <div class="cart-item-actions">
                                    <div class="qty-stepper">
                                        <button type="button" class="qty-btn decrement-btn"
                                            {{ $row['qty'] <= 1 ? 'disabled' : '' }}>&minus;</button>
                                        <input type="number" min="1" value="{{ $row['qty'] }}" class="cart-qty">
                                        <button type="button" class="qty-btn increment-btn">+</button>
                                    </div>
                                    <span class="cart-item-total line-total">
                                        {{ $symbol }}{{ number_format($row['line_total'], 2) }}
                                    </span>
                                    <button type="button" class="cart-remove" title="{{ __('Remove') }}">
                                        <i class="icon-base ti tabler-trash"></i>
                                    </button>
                                </div>

                                {{-- Addons --}}
                                @if (count($lineAddons))
                                    <div class="cart-item-addons">
                                        <span class="addon-label">{{ __('Addons') }}:</span>
                                        <select class="addon-select" multiple
                                            data-placeholder="{{ __('Choose addons...') }}">
                                            @foreach ($lineAddons as $addon)
                                                <option value="{{ $addon['id'] }}"
                                                    {{ in_array($addon['id'], $selectedIds) ? 'selected' : '' }}>
                                                    {{ $addon['name'] }}
                                                    ({{ $symbol }}{{ number_format($addon['price'], 2) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif

                            </div>
                        @endforeach
                        <div class="order-note-wrap">
                            <label class="form-label">{{ __('Order Notes') }}</label>
                            <textarea id="orderNote" class="form-control" rows="3"
                                placeholder="{{ __('Any special instructions for your whole order? e.g. ring the bell, no cutlery...') }}"></textarea>
                        </div>
                    </div>

                    @auth
                        {{-- Customer details --}}
                        <div class="cart-panel checkout-form">
                            <div class="cart-panel-head">
                                <h5>{{ __('Delivery Details') }}</h5>
                            </div>
                            <div class="p-4">
                                <form id="checkoutForm">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Full Name') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" required
                                                value="{{ auth()->user()->name ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Mobile') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" name="mobile" class="form-control" required
                                                value="{{ auth()->user()->mobile ?? '' }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('Email') }} <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" required
                                                value="{{ auth()->user()->email ?? '' }}">
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label">{{ __('Delivery Address') }} <span
                                                    class="text-danger">*</span></label>
                                            <textarea name="address" class="form-control" rows="3" required>{{ auth()->user()->address ?? '' }}</textarea>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="cart-panel">
                            <div class="p-5 text-center">
                                <i class="ti tabler-user-circle fs-1 text-primary mb-3"></i>

                                <h4>{{ __('Login Required') }}</h4>

                                <p class="text-muted">
                                    {{ __('Please login or create an account to continue with your order.') }}
                                </p>

                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <a href="{{ route('login') }}" class="btn btn-brand">
                                        {{ __('Login') }}
                                    </a>

                                    <a href="{{ route('register') }}" class="btn btn-outline-brand">
                                        {{ __('Register') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endauth
                </div>

                {{-- Right: Payment Method & Order Summary --}}
                <div class="col-lg-4">

                    {{-- Payment Method Section --}}
                    <div class="cart-summary mb-4">
                        <h5 class="mb-3">{{ __('Payment Method') }}</h5>

                        <div class="border rounded p-3 bg-light">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paymentCod"
                                    value="cod" checked>
                                <label class="form-check-label fw-bold cursor-pointer" for="paymentCod">
                                    <i class="ti tabler-cash text-success me-1"></i> {{ __('Cash on Delivery') }}
                                </label>
                            </div>
                            <div class="small text-muted mt-1 ps-4">
                                {{ __('Pay with cash upon food delivery.') }}
                            </div>
                        </div>
                    </div>

                    {{-- Summary Section --}}
                    <div class="cart-summary">
                        <h5>{{ __('Order Summary') }}</h5>

                        <div class="summary-row">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="val"
                                id="cartSubtotal">{{ $symbol }}{{ number_format($total, 2) }}</span>
                        </div>

                        <hr class="summary-divider">

                        <div class="summary-total">
                            <span class="label">{{ __('Total') }}</span>
                            <span class="amount" id="cartTotal">{{ $symbol }}{{ number_format($total, 2) }}</span>
                        </div>

                        <button type="button" class="btn btn-brand w-100" id="placeOrderBtn"
                            {{ auth()->check() ? '' : 'disabled' }}>
                            {{ __('Place Order') }}
                            <i class="icon-base ti tabler-check ms-1"></i>
                        </button>
                        @guest
                            <small class="text-danger d-block mt-2 text-center">
                                {{ __('Please login to place your order.') }}
                            </small>
                        @endguest
                        <p class="summary-note">
                            <i class="icon-base ti tabler-lock"></i>
                            {{ __('Secure checkout') }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection

@push('js_script')
    <script>
        var symbol = '{{ $symbol }}';
        var token = '{{ csrf_token() }}';

        function money(value) {
            return symbol + Number(value).toFixed(2);
        }

        function lineTotal(item) {
            var price = parseFloat(item.data('price')) || 0;
            var addonTotal = parseFloat(item.data('addon-total')) || 0;
            var qty = parseInt(item.find('.cart-qty').val()) || 0;
            return (price + addonTotal) * qty;
        }

        function updateTotal() {
            var total = 0;
            $('.cart-item').each(function() {
                total += lineTotal($(this));
            });
            $('#cartTotal, #cartSubtotal').text(money(total));
        }

        // ---- Plus / Minus ----
        $('#checkoutWrap').on('click', '.increment-btn', function() {
            var input = $(this).siblings('.cart-qty');
            input.val(parseInt(input.val()) + 1).trigger('change');
        });

        $('#checkoutWrap').on('click', '.decrement-btn', function() {
            var input = $(this).siblings('.cart-qty');
            var val = parseInt(input.val());
            if (val > 1) input.val(val - 1).trigger('change');
        });

        // ---- Change quantity ----
        $('#checkoutWrap').on('change', '.cart-qty', function() {
            var item = $(this).closest('.cart-item');
            var key = item.data('key');
            var qty = parseInt($(this).val());

            if (!qty || qty < 1) {
                qty = 1;
                $(this).val(1);
            }
            item.find('.decrement-btn').prop('disabled', qty <= 1);

            $.ajax({
                type: 'PATCH',
                url: '{{ route('cart.update') }}',
                data: {
                    key: key,
                    qty: qty,
                    _token: token
                },
                success: function(response) {
                    item.find('.line-total').text(money(lineTotal(item)));
                    updateTotal();
                    if (typeof updateCartBadge === 'function') updateCartBadge(response.count);
                }
            });
        });

        // ---- Remove item ----
        $('#checkoutWrap').on('click', '.cart-remove', function() {
            var item = $(this).closest('.cart-item');
            var key = item.data('key');

            $.ajax({
                type: 'DELETE',
                url: '{{ route('cart.remove') }}',
                data: {
                    key: key,
                    _token: token
                },
                success: function(response) {
                    item.slideUp(250, function() {
                        $(this).remove();
                        updateTotal();
                        if (typeof updateCartBadge === 'function') updateCartBadge(response
                            .count);
                        if ($('.cart-item').length === 0) {
                            window.location.href = '{{ route('cart') }}';
                        }
                    });
                }
            });
        });

        // ---- Sync addons (no reload) ----
        $('#checkoutWrap').on('change', '.addon-select', function() {
            var select = $(this);
            var item = select.closest('.cart-item');
            var key = item.data('key');
            var addonIds = select.val() || [];

            $.ajax({
                type: 'POST',
                url: '{{ route('cart.addons.sync') }}',
                data: {
                    key: key,
                    addon_ids: addonIds,
                    _token: token
                },
                success: function(response) {
                    if (response.success) {
                        item.data('addon-total', response.addon_total);
                        item.find('.line-total').text(money(response.line_total));
                        $('#cartSubtotal, #cartTotal').text(money(response.cart_total));
                        if (typeof updateCartBadge === 'function') updateCartBadge(response.count);
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message :
                        '{{ __('Something went wrong.') }}';
                    alert(msg);
                }
            });
        });

        // ---- Place order ----
        $('#placeOrderBtn').on('click', function() {

            @guest
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __('Login Required') }}',
                    text: '{{ __('Please login or create an account before placing your order.') }}',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('Login') }}',
                    cancelButtonText: '{{ __('Register') }}',
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('login') }}";
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = "{{ route('register') }}";
                    }
                });
                return;
            @endguest

            var btn = $(this);
            var form = $('#checkoutForm');

            var name = form.find('[name=name]').val().trim();
            var email = form.find('[name=email]').val().trim();
            var mobile = form.find('[name=mobile]').val().trim();
            var address = form.find('[name=address]').val().trim();
            var paymentMethod = $('input[name="payment_method"]:checked').val();

            // Validation
            if (!name || !mobile || !address) {
                Swal.fire({
                    icon: 'warning',
                    title: '{{ __('Missing Information') }}',
                    text: '{{ __('Please fill in your name, mobile and delivery address.') }}',
                    confirmButtonText: '{{ __('OK') }}',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            // Confirmation
          Swal.fire({
            title: '{{ __("Confirm Your Order") }}',
            text: '{{ __("Please review your order before continuing. Once placed, it will be submitted for processing. Do you want to proceed?") }}',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '{{ __("Place Order") }}',
            cancelButtonText: '{{ __("Review Order") }}',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            reverseButtons: true
        }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                btn.prop('disabled', true);

                $.ajax({
                    type: 'POST',
                    url: '{{ route('checkout.place') }}',
                    data: {
                        name: name,
                        mobile: mobile,
                        email: email,
                        address: address,
                        payment_method: paymentMethod,
                        note: $('#orderNote').val().trim(),
                        _token: token
                    },

                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('Order Placed!') }}',
                            text: '{{ __('Your order has been placed successfully.') }}',
                            timer: 1800,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route('menu') }}';
                        });
                    },

                    error: function(xhr) {
                        btn.prop('disabled', false);

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            var first = Object.values(xhr.responseJSON.errors)[0][0];
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('Validation Error') }}',
                                text: first
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: '{{ __('Oops!') }}',
                                text: '{{ __('Something went wrong. Please try again.') }}'
                            });
                        }
                    }
                });
            });
        });
    </script>

    <script>
        // Select2 on addon dropdowns
        $(function() {
            $('.addon-select').each(function() {
                var $this = $(this);
                $this.select2({
                    placeholder: $this.data('placeholder') || '',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $this.parent()
                });
            });
        });
    </script>
@endpush
