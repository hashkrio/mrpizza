@extends('layouts.user.app')

@section('title', __('My Orders') . ' - ' . company_name())

@section('content')

    {{-- Hero --}}
    <section class="menu-hero">
        <div class="hero-shape"></div>
        <div class="container text-center position-relative">
            <span class="hero-eyebrow">{{ company_name() }}</span>
            <h2 class="menu-hero-title">{{ __('My Orders') }} - {{ date('F Y') }}</h2>
        </div>
    </section>

    <section class="myorder-body py-6">
        <div class="container">

            @if (count($orders))

                @foreach ($orders as $order)
                    @php
                        $sym = $order->currency_symbol;
                    @endphp

                    <div class="card border-0 shadow-sm rounded-3 mb-4 overflow-hidden">

                        {{-- Head --}}
                        <div
                            class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-3 py-3">
                            <div>
                                <h5 class="text-red mb-0">{{ $order->order_no }}</h5>
                                <small class="text-muted">{{ $order->created_at->format('d M Y, h:i A') }}</small>
                            </div>

                            <div class="d-flex align-items-center gap-3">
                                <span class="fw-bold fs-5 text-green">
                                    {{ $sym }}{{ number_format($order->total, 2) }}
                                </span>

                                <span class="badge rounded-pill bg-success-subtle text-success {{ $order->status }}">
                                    {{ __(ucfirst($order->status)) }}
                                </span>

                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill myorder-toggle"
                                    data-bs-toggle="collapse" data-bs-target="#order{{ $order->id }}"
                                    aria-expanded="false">
                                    {{ __('Details') }}
                                    <i class="icon-base ti tabler-chevron-down ms-1"></i>
                                </button>
                            </div>
                        </div>

                        <div class="collapse" id="order{{ $order->id }}">

                            {{-- Items --}}
                            <ul class="list-group list-group-flush">
                                @foreach ($order->items as $item)
                                    <li
                                        class="list-group-item d-flex justify-content-between align-items-start gap-3 px-4 py-3">
                                        <div class="min-w-0">
                                            <h6 class="fw-bold mb-1">
                                                {{ $item->item_name }}
                                                @if ($item->size)
                                                    <span class="badge rounded-pill bg-success-subtle text-success">
                                                        {{ ucfirst($item->size) }}
                                                    </span>
                                                @endif
                                            </h6>

                                            <small class="text-muted">
                                                {{ $sym }}{{ number_format($item->item_price, 2) }} &times;
                                                {{ $item->qty }}
                                            </small>

                                            @if (count($item->addons))
                                                <div class="d-flex flex-wrap align-items-center gap-1 mt-2">
                                                    <small
                                                        class="text-red fw-semibold text-uppercase me-1">{{ __('Addons') }}</small>
                                                    @foreach ($item->addons as $addon)
                                                        <span class="myorder-chip bg-cream">
                                                            {{ $addon->addon_name }}
                                                            <span>{{ $sym }}{{ number_format($addon->addon_price, 2) }}</span>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if ($item->item_note)
                                                <div class="text-muted fst-italic small mt-2">
                                                    <i
                                                        class="icon-base ti tabler-note text-yellow me-1"></i>{{ $item->item_note }}
                                                </div>
                                            @endif
                                        </div>

                                        <span class="fw-bold text-red text-nowrap">
                                            {{ $sym }}{{ number_format($item->total, 2) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>

                            {{-- Detail footer --}}
                            <div class="p-4">
                                <div class="row g-4">

                                    {{-- Delivery details --}}
                                    <div class="col-md-6">
                                        <h6 class="text-red fw-bold text-uppercase border-bottom border-red pb-2 mb-3">
                                            {{ __('Delivery Details') }}
                                        </h6>

                                        <p class="small mb-1">
                                            <i class="icon-base ti tabler-user text-red me-2"></i>{{ $order->name }}
                                        </p>
                                        <p class="small mb-1">
                                            <i class="icon-base ti tabler-phone text-red me-2"></i>{{ $order->mobile }}
                                        </p>
                                        @if ($order->email)
                                            <p class="small mb-1">
                                                <i class="icon-base ti tabler-mail text-red me-2"></i>{{ $order->email }}
                                            </p>
                                        @endif
                                        <p class="small text-muted mb-1">
                                            <i class="icon-base ti tabler-map-pin text-red me-2"></i>{{ $order->address }}
                                        </p>

                                        @if ($order->order_note)
                                            <p class="small text-muted fst-italic mb-0">
                                                <i
                                                    class="icon-base ti tabler-message-2 text-yellow me-1"></i>{{ $order->order_note }}
                                            </p>
                                        @endif
                                    </div>

                                    {{-- Summary --}}
                                    <div class="col-md-6">
                                        <h6 class="text-red fw-bold text-uppercase border-bottom border-red pb-2 mb-3">
                                            {{ __('Order Summary') }}
                                        </h6>

                                        <div class="d-flex justify-content-between small mb-2">
                                            <span class="text-muted">{{ __('Items Total') }}</span>
                                            <span
                                                class="fw-semibold">{{ $sym }}{{ number_format($order->items_total, 2) }}</span>
                                        </div>

                                        @if ($order->addons_total > 0)
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span class="text-muted">{{ __('Addons') }}</span>
                                                <span
                                                    class="fw-semibold">{{ $sym }}{{ number_format($order->addons_total, 2) }}</span>
                                            </div>
                                        @endif

                                        @if ($order->discount > 0)
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span class="text-muted">{{ __('Discount') }}</span>
                                                <span
                                                    class="fw-semibold text-green">-{{ $sym }}{{ number_format($order->discount, 2) }}</span>
                                            </div>
                                        @endif

                                        @if ($order->delivery_charge > 0)
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span class="text-muted">{{ __('Delivery') }}</span>
                                                <span
                                                    class="fw-semibold">{{ $sym }}{{ number_format($order->delivery_charge, 2) }}</span>
                                            </div>
                                        @endif

                                        @if ($order->tax > 0)
                                            <div class="d-flex justify-content-between small mb-2">
                                                <span class="text-muted">{{ __('Tax') }}</span>
                                                <span
                                                    class="fw-semibold">{{ $sym }}{{ number_format($order->tax, 2) }}</span>
                                            </div>
                                        @endif

                                        <hr class="border-top border-secondary-subtle my-3">

                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">{{ __('Total') }}</span>
                                            <span
                                                class="fw-bold fs-4 text-green">{{ $sym }}{{ number_format($order->total, 2) }}</span>
                                        </div>

                                        <span
                                            class="badge rounded-pill bg-white border text-dark float-end fw-semibold mt-3 px-3 py-2">
                                            <i class="icon-base ti tabler-cash text-green me-1"></i>
                                            {{ strtoupper($order->payment_method) }}
                                        </span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class=" mt-4">
                    {{ $orders->links('pagination::bootstrap-5') }}
                </div>
            @else
                {{-- Empty --}}
                <div class="text-center py-5">
                    <div class="myorder-empty-ic mb-4">
                        <i class="icon-base ti tabler-receipt-off"></i>
                    </div>
                    <h3>{{ __('No orders yet') }}</h3>
                    <p class="text-muted mx-auto mb-4" style="max-width:380px">
                        {{ __("You haven't placed any orders. Browse the menu to get started.") }}
                    </p>
                    <a href="{{ route('menu') }}" class="btn btn-brand">{{ __('Browse Menu') }}</a>
                </div>
            @endif

        </div>
    </section>

@endsection
