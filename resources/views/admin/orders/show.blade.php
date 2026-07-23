@extends('layouts.app')

@section('title', __('Order') . ' ' . $order->order_no)

@section('content')
    @php
        $sym = $order->currency_symbol;

        $statusMap = [
            'Pending' => 'bg-label-warning',
            'Confirmed' => 'bg-label-success',
            'Delivered' => 'bg-label-success',
            'Cancelled' => 'bg-label-danger',
        ];
        $statusClass = $statusMap[$order->status] ?? 'bg-label-secondary';
        $payClass = $order->payment_status === 'Paid' ? 'bg-label-success' : 'bg-label-warning';
    @endphp
    <div class="container-fluid flex-grow-1 container-p-y">

        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
                <h4 class="mb-1">{{ $order->order_no }}</h4>
                <p class="text-muted mb-0">{{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $statusClass }}">{{ __(ucfirst($order->status)) }}</span>
                <span class="badge {{ $payClass }}">{{ __(ucfirst($order->payment_status)) }}</span>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('Back') }}
                </a>
            </div>
        </div>

        <div class="row g-4">

            {{-- Left: items --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Order Items') }}
                            <span class="text-muted fw-normal">({{ count($order->items) }})</span>
                        </h5>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Item') }}</th>
                                    <th class="text-center">{{ __('Qty') }}</th>
                                    <th class="text-end">{{ __('Price') }}</th>
                                    <th class="text-end">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $item->item_name }}</span>
                                            @if ($item->size)
                                                <span class="badge rounded-pill bg-label-primary ms-1">
                                                    {{ ucfirst($item->size) }}
                                                </span>
                                            @endif

                                            @if (count($item->addons))
                                                <div class="mt-2 d-flex flex-wrap gap-1">
                                                    @foreach ($item->addons as $addon)
                                                        <span class="badge bg-label-secondary fw-normal">
                                                            {{ $addon->addon_name }}
                                                            &middot;
                                                            {{ $sym }}{{ number_format($addon->addon_price, 2) }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if ($item->item_note)
                                                <div class="small text-muted fst-italic mt-1">
                                                    <i class="icon-base ti tabler-note me-1"></i>{{ $item->item_note }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $item->qty }}</td>
                                        <td class="text-end">{{ $sym }}{{ number_format($item->item_price, 2) }}
                                        </td>
                                        <td class="text-end fw-semibold">
                                            {{ $sym }}{{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($order->order_note)
                        <div class="card-footer bg-light">
                            <span class="fw-semibold small">{{ __('Order Note') }}:</span>
                            <span class="text-muted small fst-italic">{{ $order->order_note }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Right: customer + summary --}}
            <div class="col-lg-4">

                {{-- Customer --}}
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Customer Details') }}</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <i class="icon-base ti tabler-user text-muted me-2"></i>{{ $order->name }}
                        </p>
                        <p class="mb-2">
                            <i class="icon-base ti tabler-phone text-muted me-2"></i>{{ $order->mobile }}
                        </p>
                        @if ($order->email)
                            <p class="mb-2">
                                <i class="icon-base ti tabler-mail text-muted me-2"></i>{{ $order->email }}
                            </p>
                        @endif
                        <p class="mb-0 text-muted">
                            <i class="icon-base ti tabler-map-pin me-2"></i>{{ $order->address }}
                        </p>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ __('Order Summary') }}</h5>
                    </div>
                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('Items Total') }}</span>
                            <span
                                class="fw-semibold">{{ $sym }}{{ number_format($order->items_total, 2) }}</span>
                        </div>

                        @if ($order->addons_total > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ __('Addons') }}</span>
                                <span
                                    class="fw-semibold">{{ $sym }}{{ number_format($order->addons_total, 2) }}</span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">{{ __('Subtotal') }}</span>
                            <span class="fw-semibold">{{ $sym }}{{ number_format($order->subtotal, 2) }}</span>
                        </div>

                        @if ($order->discount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ __('Discount') }}</span>
                                <span
                                    class="fw-semibold text-success">-{{ $sym }}{{ number_format($order->discount, 2) }}</span>
                            </div>
                        @endif

                        @if ($order->delivery_charge > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ __('Delivery') }}</span>
                                <span
                                    class="fw-semibold">{{ $sym }}{{ number_format($order->delivery_charge, 2) }}</span>
                            </div>
                        @endif

                        @if ($order->tax > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">{{ __('Tax') }}</span>
                                <span class="fw-semibold">{{ $sym }}{{ number_format($order->tax, 2) }}</span>
                            </div>
                        @endif

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold">{{ __('Total') }}</span>
                            <span
                                class="fw-bold fs-4 text-primary">{{ $sym }}{{ number_format($order->total, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">{{ __('Payment Method') }}</span>
                            <span class="badge bg-label-info">{{ strtoupper($order->payment_method) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
