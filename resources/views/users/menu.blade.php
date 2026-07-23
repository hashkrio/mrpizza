@extends('layouts.user.app')

@section('title', __('Menu') . ' - ' . company_name())

@section('content')

    {{-- ===================== MENU HERO ===================== --}}
    <section class="menu-hero">
        <div class="hero-shape"></div>
        <div class="container text-center position-relative">
            <span class="hero-eyebrow">{{ __('Our Delicious') }}</span>
            <h2 class="menu-hero-title">{{ __('Explore Our Menu') }}</h2>
            <p class="menu-hero-text">
                {{ __('Handcrafted with the freshest ingredients and baked to perfection in our fire wood oven.') }}
            </p>
        </div>
    </section>

    {{-- ===================== MENU BODY ===================== --}}
    <section class="menu-body bg-cream py-6">
        <div class="container">

            {{-- Category filter tabs --}}
            <div class="menu-filter" id="menuFilter" data-animate="fade-up">
                <button type="button" class="menu-pill {{ empty($activeCat) ? 'active' : '' }}" data-category="">
                    {{ __('All') }}
                </button>
                @foreach ($categories as $cat)
                    <button type="button" class="menu-pill {{ $activeCat == $cat->id ? 'active' : '' }}"
                        data-category="{{ $cat->id }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            {{-- Loader --}}
            <div id="menuLoader" class="text-center py-5 d-none">
                <div class="spinner-border text-danger" role="status">
                    <span class="visually-hidden">{{ __('Loading...') }}</span>
                </div>
            </div>

            {{-- Items grid --}}
            <div class="row gy-4 mt-2" id="menuItems">
                @include('users.partials.menu-items', [
                    'items' => $items,
                    'symbol' => $symbol,
                    'fallback' => $fallback,
                ])
            </div>

        </div>
    </section>

@endsection

@push('js_script')
    <script>
        var token = '{{ csrf_token() }}';
        var itemsUrl = '{{ route('menu.items') }}';
        var activeXhr = null;
var preselectCat = '{{ session('preselect_category', '') }}';


        // ---- Load items for a category (empty string = All) ----
        function loadItems(category) {
            // Cancel a previous request if the user clicks quickly
            if (activeXhr) activeXhr.abort();

            $('#menuItems').addClass('d-none');
            $('#menuLoader').removeClass('d-none');

            activeXhr = $.ajax({
                type: 'GET',
                url: itemsUrl,
                data: category ? {
                    category: category
                } : {},
                dataType: 'json',
                success: function(response) {
                    $('#menuItems').html(response.html);
                },
                error: function(xhr, status) {
                    if (status !== 'abort') {
                        $('#menuItems').html(
                            '<div class="col-12 text-center text-muted py-5">' +
                            '{{ __('Something went wrong. Please try again.') }}' +
                            '</div>'
                        );
                    }
                },
                complete: function(xhr, status) {
                    if (status !== 'abort') {
                        $('#menuLoader').addClass('d-none');
                        $('#menuItems').removeClass('d-none');
                        activeXhr = null;
                    }
                }
            });
        }

        // ---- Category tab click ----
        $('#menuFilter').on('click', '.menu-pill', function() {
            var button = $(this);
            if (button.hasClass('active')) return;

            $('#menuFilter .menu-pill').removeClass('active');
            button.addClass('active');

            loadItems(button.data('category') || '');
        });

        // ---- Add to cart (single-price items) ----
        // Delegated from document because the grid is replaced on every category change
        // ---- Add to cart (single-price items) ----
        $(document).on('click', '.js-add-to-cart', function() {

            var button = $(this);
            var id = button.data('id');

            if (button.prop('disabled')) return;

            button.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: '{{ route('cart.add') }}',
                data: {
                    id: id,
                    qty: 1,
                    _token: token
                },

                success: function(response) {

                    if (typeof updateCartBadge === 'function') {
                        updateCartBadge(response.count);
                    }

                    // Change icon temporarily
                    button.html('<i class="icon-base ti tabler-check"></i>');

                    // Success Toast
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('Success') }}',
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false
                    });

                    setTimeout(function() {
                        button.html('<i class="icon-base ti tabler-shopping-cart-plus"></i>');
                    }, 1200);

                    button.prop('disabled', false);
                },

                error: function(xhr) {

                    let msg = '{{ __('Something went wrong.') }}';

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('Failed') }}',
                        text: msg,
                        confirmButtonText: '{{ __('OK') }}'
                    });

                    button.prop('disabled', false);
                }
            });

        });

        $(function () {
        if (preselectCat) {
        $('#menuFilter .menu-pill').removeClass('active');

        var target = $('#menuFilter .menu-pill[data-category="' + preselectCat + '"]');
        if (target.length) {
        target.addClass('active');
        } else {
        $('#menuFilter .menu-pill[data-category=""]').addClass('active');
        }

        loadItems(preselectCat);
        }
        });
    </script>
@endpush
