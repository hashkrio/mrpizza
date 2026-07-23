@extends('layouts.user.app')

@section('title', __('My Cart') . ' - ' . company_name())

@section('content')

    {{-- Hero --}}
    <section class="menu-hero">
        <div class="hero-shape"></div>
        <div class="container text-center position-relative">
            <span class="hero-eyebrow">{{ company_name() }}</span>
            <h2 class="menu-hero-title">{{ __('My Cart') }}</h2>
        </div>
    </section>

    <section class="cart-body py-6">
        <div class="container" id="cartWrap">

            @if (count($items))
                <div class="row g-4">

                    {{-- Items --}}
                    <div class="col-lg-8">
                        <div class="cart-panel">
                            <div class="cart-panel-head">
                                <h5>{{ __('Cart Items') }}
                                    <span class="text-muted fw-normal">({{ count($items) }})</span>
                                </h5>
                                <button type="button" class="cart-clear-btn" id="cartClear">
                                    <i class="icon-base ti tabler-trash"></i>{{ __('Clear Cart') }}
                                </button>
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
                                            <input type="number" min="1" value="{{ $row['qty'] }}"
                                                class="cart-qty">
                                            <button type="button" class="qty-btn increment-btn">+</button>
                                        </div>
                                        <span class="cart-item-total line-total">
                                            {{ $symbol }}{{ number_format($row['line_total'], 2) }}
                                        </span>
                                        <button type="button" class="cart-remove" title="{{ __('Remove') }}">
                                            <i class="icon-base ti tabler-trash"></i>
                                        </button>
                                    </div>

                                    {{-- Addons for this line --}}
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
                        </div>

                        <a href="{{ route('menu') }}" class="cart-continue">
                            <i class="icon-base ti tabler-arrow-left"></i>{{ __('Continue Shopping') }}
                        </a>
                    </div>

                    {{-- Summary --}}
                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h5>{{ __('Order Summary') }}</h5>

                            <div class="summary-row">
                                <span>{{ __('Subtotal') }}</span>
                                <span class="val"
                                    id="cartSubtotal">{{ $symbol }}{{ number_format($total, 2) }}</span>
                            </div>
                            <div class="summary-row">
                                <span>{{ __('Delivery') }}</span>
                                <span class="val">{{ __('Calculated at checkout') }}</span>
                            </div>

                            <hr class="summary-divider">

                            <div class="summary-total">
                                <span class="label">{{ __('Total') }}</span>
                                <span class="amount"
                                    id="cartTotal">{{ $symbol }}{{ number_format($total, 2) }}</span>
                            </div>

                            <a href="{{ url('/checkout') }}" class="btn btn-brand w-100">
                                {{ __('Proceed to Checkout') }}
                                <i class="icon-base ti tabler-arrow-right ms-1"></i>
                            </a>

                            <p class="summary-note">
                                <i class="icon-base ti tabler-lock"></i>
                                {{ __('Secure checkout') }}
                            </p>
                        </div>
                    </div>

                </div>
            @else
                {{-- Empty --}}
                <div class="cart-empty" id="cartEmpty">
                    <div class="cart-empty-ic">
                        <i class="icon-base ti tabler-shopping-cart-off"></i>
                    </div>
                    <h3>{{ __('Your cart is empty') }}</h3>
                    <p>{{ __("Looks like you haven't added any items to your cart yet.") }}</p>
                    <a href="{{ route('menu') }}" class="btn btn-brand">{{ __('Browse Menu') }}</a>
                </div>
            @endif

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

        // Line total = (base price + addon total) * qty
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
        $('#cartWrap').on('click', '.increment-btn', function() {
            var input = $(this).siblings('.cart-qty');
            input.val(parseInt(input.val()) + 1).trigger('change');
        });

        $('#cartWrap').on('click', '.decrement-btn', function() {
            var input = $(this).siblings('.cart-qty');
            var val = parseInt(input.val());
            if (val > 1) {
                input.val(val - 1).trigger('change');
            }
        });

        // ---- Change quantity ----
        $('#cartWrap').on('change', '.cart-qty', function() {
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
                    if (typeof updateCartBadge === 'function') {
                        updateCartBadge(response.count);
                    }
                }
            });
        });

        // ---- Remove item ----
        $('#cartWrap').on('click', '.cart-remove', function() {
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
                        if (typeof updateCartBadge === 'function') {
                            updateCartBadge(response.count);
                        }
                        if ($('.cart-item').length === 0) {
                            location.reload();
                        }
                    });
                }
            });
        });

        // ---- Sync addons without reloading ----
        $('#cartWrap').on('change', '.addon-select', function(e) {
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
                        // Update HTML data attribute for the line's addon total
                        item.data('addon-total', response.addon_total);

                        // Update the visible line item total
                        item.find('.line-total').text(money(response.line_total));

                        // Update subtotal & grand total elements on the page
                        $('#cartSubtotal, #cartTotal').text(money(response.cart_total));

                        if (typeof updateCartBadge === 'function') {
                            updateCartBadge(response.count);
                        }
                    }
                },
                error: function(xhr) {
                    var msg = xhr.responseJSON ? xhr.responseJSON.message :
                        '{{ __('Something went wrong.') }}';
                    alert(msg);
                }
            });
        });

        // Initialise Select2 on every addon dropdown
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

        // ---- Clear cart ----
        $('#cartClear').on('click', function() {
            $.ajax({
                type: 'DELETE',
                url: '{{ route('cart.clear') }}',
                data: {
                    _token: token
                },
                success: function(response) {
                    location.reload();
                }
            });
        });
    </script>

    <script>
        // Initialise Select2 on every addon dropdown
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
