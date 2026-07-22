@extends('layouts.user.app')

@section('title', __('Checkout') . ' - ' . company_name())

@section('content')

    {{-- Hero --}}
    <section class="menu-hero">
        <div class="hero-shape"></div>
        <div class="container text-center position-relative">
            <span class="hero-eyebrow">{{ company_name() }}</span>
            <h1 class="menu-hero-title">{{ __('Checkout') }}</h1>
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
                                $lineAddons  = $addonsByCategory[$row['category_id']] ?? [];
                                $selectedIds = collect($row['addons'])->pluck('id')->all();
                            @endphp
                            <div class="cart-item"
                                 data-key="{{ $row['key'] }}"
                                 data-price="{{ $row['price'] }}"
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
                                                    (+{{ $symbol }}{{ number_format($addon['price'], 2) }})
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

                    {{-- Customer details --}}
                    <div class="cart-panel checkout-form">
                        <div class="cart-panel-head">
                            <h5>{{ __('Delivery Details') }}</h5>
                        </div>
                        <div class="p-4">
                            <form id="checkoutForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Full Name') }} *</label>
                                        <input type="text" name="name" class="form-control" required
                                               value="{{ auth()->user()->name ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Mobile') }} *</label>
                                        <input type="text" name="mobile" class="form-control" required
                                               value="{{ auth()->user()->mobile ?? '' }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">{{ __('Email') }}</label>
                                        <input type="email" name="email" class="form-control"
                                               value="{{ auth()->user()->email ?? '' }}">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">{{ __('Delivery Address') }} *</label>
                                        <textarea name="address" class="form-control" rows="3" required>{{ auth()->user()->address ?? '' }}</textarea>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>

                </div>

                {{-- Right: summary --}}
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h5>{{ __('Order Summary') }}</h5>

                        <div class="summary-row">
                            <span>{{ __('Subtotal') }}</span>
                            <span class="val" id="cartSubtotal">{{ $symbol }}{{ number_format($total, 2) }}</span>
                        </div>
                        <div class="summary-row">
                            <span>{{ __('Delivery') }}</span>
                            <span class="val">{{ __('Calculated at checkout') }}</span>
                        </div>

                        <hr class="summary-divider">

                        <div class="summary-total">
                            <span class="label">{{ __('Total') }}</span>
                            <span class="amount" id="cartTotal">{{ $symbol }}{{ number_format($total, 2) }}</span>
                        </div>

                        <button type="button" class="btn btn-brand w-100" id="placeOrderBtn">
                            {{ __('Place Order') }}
                            <i class="icon-base ti tabler-check ms-1"></i>
                        </button>

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
    var token  = '{{ csrf_token() }}';

    function money(value) {
        return symbol + Number(value).toFixed(2);
    }

    function lineTotal(item) {
        var price      = parseFloat(item.data('price')) || 0;
        var addonTotal = parseFloat(item.data('addon-total')) || 0;
        var qty        = parseInt(item.find('.cart-qty').val()) || 0;
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
        var key  = item.data('key');
        var qty  = parseInt($(this).val());

        if (!qty || qty < 1) { qty = 1; $(this).val(1); }
        item.find('.decrement-btn').prop('disabled', qty <= 1);

        $.ajax({
            type: 'PATCH',
            url: '{{ route('cart.update') }}',
            data: { key: key, qty: qty, _token: token },
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
        var key  = item.data('key');

        $.ajax({
            type: 'DELETE',
            url: '{{ route('cart.remove') }}',
            data: { key: key, _token: token },
            success: function(response) {
                item.slideUp(250, function() {
                    $(this).remove();
                    updateTotal();
                    if (typeof updateCartBadge === 'function') updateCartBadge(response.count);
                    // No items left → back to cart (which shows the empty state)
                    if ($('.cart-item').length === 0) {
                        window.location.href = '{{ route('cart') }}';
                    }
                });
            }
        });
    });

    // ---- Sync addons (no reload) ----
    $('#checkoutWrap').on('change', '.addon-select', function() {
        var select   = $(this);
        var item     = select.closest('.cart-item');
        var key      = item.data('key');
        var addonIds = select.val() || [];

        $.ajax({
            type: 'POST',
            url: '{{ route('cart.addons.sync') }}',
            data: { key: key, addon_ids: addonIds, _token: token },
            success: function(response) {
                if (response.success) {
                    item.data('addon-total', response.addon_total);
                    item.find('.line-total').text(money(response.line_total));
                    $('#cartSubtotal, #cartTotal').text(money(response.cart_total));
                    if (typeof updateCartBadge === 'function') updateCartBadge(response.count);
                }
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : '{{ __('Something went wrong.') }}';
                alert(msg);
            }
        });
    });

    // ---- Place order ----
    $('#placeOrderBtn').on('click', function() {
        var btn  = $(this);
        var form = $('#checkoutForm');

        // Basic required-field check
        var name    = form.find('[name=name]').val().trim();
        var mobile  = form.find('[name=mobile]').val().trim();
        var address = form.find('[name=address]').val().trim();

        if (!name || !mobile || !address) {
            alert('{{ __('Please fill in your name, mobile and delivery address.') }}');
            return;
        }

        btn.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: '{{ route('checkout.place') }}',
            data: {
                name: name,
                mobile: mobile,
                email: form.find('[name=email]').val().trim(),
                address: address,
                 note: $('#orderNote').val().trim(),
                _token: token
            },
            success: function(response) {
                window.location.href = '{{ route('menu') }}';
            },
            error: function(xhr) {
                btn.prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    var first = Object.values(xhr.responseJSON.errors)[0][0];
                    alert(first);
                } else {
                    alert('{{ __('Something went wrong. Please try again.') }}');
                }
            }
        });
    });
</script>

<script>
    // Select2 on addon dropdowns
    $(function () {
        $('.addon-select').each(function () {
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