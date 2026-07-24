@extends('layouts.app')
@section('title', __('Dashboard') . ' - ' . company_name())

@php
    $sym = currency_symbol();

    $filterTabs = [
        'today' => __('Today'),
        'week' => __('This Week'),
        'month' => __('This Month'),
        'last_month' => __('Last Month'),
        'year' => __('This Year'),
        'all' => __('All Time'),
    ];
@endphp

@push('css_script')
    <style>
        .kpi-card {
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 .5rem 1.25rem rgba(161, 172, 184, .35) !important;
        }

        .kpi-icon {
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .5rem;
        }

        .top-item-row+.top-item-row {
            margin-top: 1.1rem;
        }

        .dash-empty {
            padding: 2.5rem 1rem;
            text-align: center;
            color: #a1acb8;
        }

        /* ---- Filter tabs ---- */
        .dash-filter {
            display: flex;
            flex-wrap: nowrap;
            gap: .35rem;
            overflow-x: auto;
            padding: .3rem;
            background: var(--bs-body-bg, #fff);
            border-radius: .6rem;
            box-shadow: 0 .125rem .35rem rgba(161, 172, 184, .3);
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .dash-filter::-webkit-scrollbar {
            display: none;
        }

        .dash-filter .dash-filter-btn {
            white-space: nowrap;
            border: 0;
            background: transparent;
            color: #6f6b7d;
            font-size: .8125rem;
            font-weight: 500;
            padding: .45rem .85rem;
            border-radius: .45rem;
            transition: background .15s ease, color .15s ease;
            text-decoration: none;
        }

        .dash-filter .dash-filter-btn:hover {
            background: rgba(115, 103, 240, .08);
            color: #db3727;
        }

        .dash-filter .dash-filter-btn.active {
            background: #db3727;
            color: #fff;
            box-shadow: 0 .15rem .5rem rgba(115, 103, 240, .4);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">

        {{-- ================= Page heading ================= --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <h4 class="fw-bold mb-1">{{ __('Dashboard') }}</h4>
                <p class="text-muted mb-0">
                    {{ __('Overview of your store performance') }} &middot;
                    <span class="fw-semibold text-body">{{ $rangeLabel }}</span>
                </p>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                {{-- ---------- Filter tabs ---------- --}}
                <div class="dash-filter">
                    @foreach ($filterTabs as $key => $label)
                        <a href="{{ route('admin.dashboard', ['range' => $key]) }}"
                            class="dash-filter-btn {{ $range === $key ? 'active' : '' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <a href="{{ route('admin.orders.index') }}" class="btn btn-primary">
                    <i class="icon-base ti tabler-list-details me-1"></i> {{ __('Manage Orders') }}
                </a>
            </div>
        </div>

        {{-- ================= KPI cards ================= --}}
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted fw-semibold">{{ __('Revenue') }}</span>
                            <div class="kpi-icon bg-label-primary">
                                <i class="icon-base ti tabler-currency-dollar fs-4"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-1">{{ $sym }}{{ number_format($kpi['total_sales'], 2) }}</h3>
                        <small class="text-muted">
                            {{ $rangeLabel }} &middot; {{ __('Today') }}:
                            <span
                                class="fw-semibold text-body">{{ $sym }}{{ number_format($kpi['today_sales'], 2) }}</span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted fw-semibold">{{ __('Orders') }}</span>
                            <div class="kpi-icon bg-label-info">
                                <i class="icon-base ti tabler-shopping-cart fs-4"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-1">{{ number_format($kpi['total_orders']) }}</h3>
                        <small class="text-muted">
                            {{ __('Today') }}: <span
                                class="fw-semibold text-body">{{ number_format($kpi['today_orders']) }}</span>
                            &middot; {{ __('7 days') }}: <span
                                class="fw-semibold text-body">{{ number_format($kpi['week_orders']) }}</span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted fw-semibold">{{ __('Avg. Order Value') }}</span>
                            <div class="kpi-icon bg-label-warning">
                                <i class="icon-base ti tabler-receipt fs-4"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-1">{{ $sym }}{{ number_format($kpi['avg_order_value'], 2) }}</h3>
                        <small class="text-muted">
                            {{ __('Last 7 days') }}: <span
                                class="fw-semibold text-body">{{ $sym }}{{ number_format($kpi['week_sales'], 2) }}</span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card h-100 border-0 shadow-sm kpi-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted fw-semibold">{{ __('Customers') }}</span>
                            <div class="kpi-icon bg-label-success">
                                <i class="icon-base ti tabler-users fs-4"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold mb-1">{{ number_format($kpi['total_users']) }}</h3>
                        <small class="text-muted">
                            {{ $rangeLabel }}: <span
                                class="fw-semibold text-success">+{{ number_format($kpi['range_users']) }}</span>
                            &middot; {{ __('Today') }}: <span
                                class="fw-semibold text-success">+{{ number_format($kpi['new_users_today']) }}</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= Secondary stat strip ================= --}}
        <div class="row g-3 mb-4">
            <!-- Items Revenue -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="kpi-icon bg-label-primary">
                                <i class="ti tabler-pizza fs-4 text-primary"></i>
                            </div>
                            <span class="text-muted small fw-medium text-truncate">{{ __('Items Revenue') }}</span>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">
                            {{ $sym }}{{ number_format($kpi['items_revenue'], 2) }}</h5>
                    </div>
                </div>
            </div>

            <!-- Addons Revenue -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="kpi-icon  bg-label-info">
                                <i class="ti tabler-plus fs-4 text-info"></i>
                            </div>
                            <span class="text-muted small fw-medium text-truncate">{{ __('Addons Revenue') }}</span>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">
                            {{ $sym }}{{ number_format($kpi['addons_revenue'], 2) }}</h5>
                    </div>
                </div>
            </div>

            <!-- Buying Customers -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="kpi-icon  bg-label-success">
                                <i class="ti tabler-user-check fs-4 text-success"></i>
                            </div>
                            <span class="text-muted small fw-medium text-truncate">{{ __('Buying Customers') }}</span>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">{{ number_format($kpi['buying_customers']) }}</h5>
                    </div>
                </div>
            </div>

            <!-- Active Items -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="kpi-icon  bg-label-warning">
                                <i class="ti tabler-tools-kitchen-2 fs-4 text-warning"></i>
                            </div>
                            <span class="text-muted small fw-medium text-truncate">{{ __('Active Items') }}</span>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">
                            {{ number_format($kpi['active_items']) }}<small
                                class="text-muted fs-6 fw-normal">/{{ number_format($kpi['total_items']) }}</small>
                        </h5>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="kpi-icon  bg-label-secondary">
                                <i class="ti tabler-category fs-4 text-secondary"></i>
                            </div>
                            <span class="text-muted small fw-medium text-truncate">{{ __('Categories') }}</span>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">{{ number_format($kpi['total_categories']) }}</h5>
                    </div>
                </div>
            </div>

            <!-- Addons -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="kpi-icon  bg-label-danger">
                                <i class="ti tabler-cheese fs-4 text-danger"></i>
                            </div>
                            <span class="text-muted small fw-medium text-truncate">{{ __('Addons') }}</span>
                        </div>
                        <h5 class="fw-bold mb-0 text-dark">{{ number_format($kpi['total_addons']) }}</h5>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= Charts row ================= --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                        <div>
                            <h5 class="card-title mb-1 fw-bold">{{ __('Sales & Orders Overview') }}</h5>
                            <p class="text-muted small mb-0">{{ $trendSubtitle }}</p>
                        </div>
                        <span class="badge bg-label-primary p-2 rounded-2">
                            <i class="icon-base ti tabler-calendar me-1"></i> {{ $rangeLabel }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div id="salesOverviewChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="card-title mb-1 fw-bold">{{ __('Revenue by Category') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Share of total item sales') }} &middot;
                            {{ $rangeLabel }}</p>
                    </div>
                    <div class="card-body">
                        @if (array_sum($categorySeries) > 0)
                            <div id="categoryRevenueChart"></div>
                            <div class="mt-3">
                                @foreach ($categoryRevenue->take(4) as $cat)
                                    <div class="d-flex justify-content-between align-items-center small py-1">
                                        <span class="text-truncate"
                                            style="max-width: 55%;">{{ $cat->category_name }}</span>
                                        <span class="text-muted">
                                            {{ number_format($cat->qty_sold) }} {{ __('sold') }}
                                            &middot; <span
                                                class="fw-semibold text-body">{{ $sym }}{{ number_format($cat->revenue, 2) }}</span>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="dash-empty">
                                <i class="icon-base ti tabler-chart-donut fs-1 d-block mb-2"></i>
                                {{ __('No sales data for this period.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= Weekly trend + Top items ================= --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="card-title mb-1 fw-bold">{{ __('Last 7 Days') }}</h5>
                        <p class="text-muted small mb-0">{{ __('Daily revenue trend') }}</p>
                    </div>
                    <div class="card-body">
                        <div id="weeklyTrendChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                        <div>
                            <h5 class="card-title mb-1 fw-bold">{{ __('Best Selling Items') }}</h5>
                            <p class="text-muted small mb-0">{{ __('By quantity sold') }} &middot; {{ $rangeLabel }}
                            </p>
                        </div>
                        <a href="{{ route('admin.items.index') }}" class="btn btn-sm btn-label-primary">
                            <i class="icon-base ti tabler-eye me-1"></i> {{ __('View All') }}
                        </a>
                    </div>
                    <div class="card-body">
                        @forelse ($topItems as $item)
                            <div class="top-item-row">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-truncate"
                                        style="max-width: 60%;">{{ $item->item_name }}</span>
                                    <span class="text-muted small">
                                        {{ number_format($item->qty_sold) }} {{ __('sold') }}
                                        &middot; <span
                                            class="fw-semibold text-body">{{ $sym }}{{ number_format($item->revenue, 2) }}</span>
                                    </span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary rounded" role="progressbar"
                                        style="width: {{ $maxQtySold > 0 ? round(($item->qty_sold / $maxQtySold) * 100) : 0 }}%"
                                        aria-valuenow="{{ $item->qty_sold }}" aria-valuemin="0"
                                        aria-valuemax="{{ $maxQtySold }}"></div>
                                </div>
                            </div>
                        @empty
                            <div class="dash-empty">
                                <i class="icon-base ti tabler-shopping-bag fs-1 d-block mb-2"></i>
                                {{ __('No sales data for this period.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= Top customers ================= --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                        <div>
                            <h5 class="card-title mb-1 fw-bold">{{ __('Top Customers') }}</h5>
                            <p class="text-muted small mb-0">{{ __('Highest spend') }} &middot; {{ $rangeLabel }}</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-label-primary">
                            <i class="icon-base ti tabler-eye me-1"></i> {{ __('View All') }}
                        </a>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Orders') }}</th>
                                    <th>{{ __('Total Spent') }}</th>
                                    <th>{{ __('Avg. Order') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topCustomers as $i => $customer)
                                    <tr>
                                        <td><span class="badge bg-label-primary">{{ $i + 1 }}</span></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold">{{ $customer->name }}</span>
                                                <small class="text-muted">{{ $customer->mobile }}</small>
                                            </div>
                                        </td>
                                        <td><span
                                                class="badge bg-label-secondary">{{ number_format($customer->orders_count) }}</span>
                                        </td>
                                        <td class="fw-semibold">
                                            {{ $sym }}{{ number_format($customer->total_spent, 2) }}</td>
                                        <td class="text-muted">
                                            {{ $sym }}{{ number_format($customer->orders_count > 0 ? $customer->total_spent / $customer->orders_count : 0, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="icon-base ti tabler-users fs-1 d-block mb-2"></i>
                                            {{ __('No customer data for this period.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= Recent orders ================= --}}
        <div class="row g-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center bg-transparent py-3">
                        <div>
                            <h5 class="card-title mb-1 fw-bold">{{ __('Recent Orders') }}</h5>
                            <p class="text-muted small mb-0">{{ __('Latest 8 orders') }} &middot; {{ $rangeLabel }}</p>
                        </div>
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-label-primary">
                            <i class="icon-base ti tabler-eye me-1"></i> {{ __('View All') }}
                        </a>
                    </div>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Order No') }}</th>
                                    <th>{{ __('Customer') }}</th>
                                    <th>{{ __('Items') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th class="text-end">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentOrders as $order)
                                    <tr>
                                        <td><strong
                                                class="text-primary">{{ $order->order_no ?? '#' . $order->id }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-semibold">{{ $order->name ?? ($order->user->name ?? __('Guest')) }}</span>
                                                <small class="text-muted">{{ $order->mobile }}</small>
                                            </div>
                                        </td>
                                        <td><span class="badge bg-label-secondary">{{ $order->items_count }}</span></td>
                                        <td class="fw-semibold">
                                            {{ $order->currency_symbol ?: $sym }}{{ number_format($order->total, 2) }}
                                        </td>
                                        <td><small
                                                class="text-muted">{{ $order->created_at->format('d M Y, h:i A') }}</small>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                                class="btn btn-icon btn-text-secondary rounded-pill">
                                                <i class="icon-base ti tabler-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="icon-base ti tabler-inbox fs-1 d-block mb-2"></i>
                                            {{ __('No orders found for this period.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js_script')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const symbol = @json($sym);
            const muted = '#a1acb8';
            const gridColor = 'rgba(161,172,184,.2)';
            const money = v => symbol + Number(v).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            /* ---------- 1. Sales & Orders combo ---------- */
            const salesEl = document.querySelector('#salesOverviewChart');
            if (salesEl) {
                const labels = @json($chartMonths);
                new ApexCharts(salesEl, {
                    series: [{
                            name: @json(__('Revenue')),
                            type: 'column',
                            data: @json($chartSales)
                        },
                        {
                            name: @json(__('Orders')),
                            type: 'line',
                            data: @json($chartOrders)
                        }
                    ],
                    chart: {
                        height: 360,
                        type: 'line',
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        parentHeightOffset: 0
                    },
                    stroke: {
                        width: [0, 3],
                        curve: 'smooth'
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            columnWidth: labels.length > 15 ? '65%' : '42%'
                        }
                    },
                    colors: ['#db3727', '#28c76f'],
                    labels: labels,
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        type: 'category',
                        tickAmount: labels.length > 15 ? 12 : undefined,
                        labels: {
                            rotate: labels.length > 15 ? -45 : 0,
                            rotateAlways: labels.length > 15,
                            hideOverlappingLabels: true,
                            style: {
                                colors: muted,
                                fontSize: '12px'
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: [{
                            title: {
                                text: @json(__('Revenue')),
                                style: {
                                    color: '#db3727',
                                    fontWeight: 600
                                }
                            },
                            labels: {
                                style: {
                                    colors: muted
                                },
                                formatter: v => symbol + Number(v).toLocaleString()
                            }
                        },
                        {
                            opposite: true,
                            title: {
                                text: @json(__('Orders')),
                                style: {
                                    color: '#28c76f',
                                    fontWeight: 600
                                }
                            },
                            labels: {
                                style: {
                                    colors: muted
                                },
                                formatter: v => Math.round(v)
                            }
                        }
                    ],
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '13px',
                        markers: {
                            radius: 12
                        }
                    },
                    grid: {
                        borderColor: gridColor,
                        strokeDashArray: 4,
                        padding: {
                            top: -10
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        y: {
                            formatter: (v, {
                                seriesIndex
                            }) => (v === undefined ? v : (seriesIndex === 0 ? money(v) : Math.round(v)))
                        }
                    },
                    responsive: [{
                        breakpoint: 576,
                        options: {
                            plotOptions: {
                                bar: {
                                    columnWidth: '70%'
                                }
                            }
                        }
                    }]
                }).render();
            }

            /* ---------- 2. Revenue by category donut ---------- */
            const catEl = document.querySelector('#categoryRevenueChart');
            if (catEl) {
                new ApexCharts(catEl, {
                    series: @json($categorySeries),
                    labels: @json($categoryLabels),
                    chart: {
                        type: 'donut',
                        height: 340
                    },
                    colors: ['#db3727', '#28c76f', '#ff9f43', '#00cfe8', '#ea5455', '#a8aaae', '#9c6ade',
                        '#f77eb9'
                    ],
                    stroke: {
                        width: 0
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: v => Math.round(v) + '%'
                    },
                    legend: {
                        position: 'bottom',
                        fontSize: '13px',
                        markers: {
                            radius: 12
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '72%',
                                labels: {
                                    show: true,
                                    value: {
                                        fontSize: '20px',
                                        fontWeight: 600,
                                        formatter: v => money(v)
                                    },
                                    total: {
                                        show: true,
                                        label: @json(__('Total')),
                                        fontSize: '14px',
                                        color: muted,
                                        formatter: w => money(w.globals.seriesTotals.reduce((a, b) => a + b,
                                            0))
                                    }
                                }
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: v => money(v)
                        }
                    },
                    responsive: [{
                        breakpoint: 992,
                        options: {
                            chart: {
                                height: 300
                            }
                        }
                    }]
                }).render();
            }

            /* ---------- 3. Weekly revenue area ---------- */
            const weekEl = document.querySelector('#weeklyTrendChart');
            if (weekEl) {
                new ApexCharts(weekEl, {
                    series: [{
                        name: @json(__('Revenue')),
                        data: @json($weekSales)
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        parentHeightOffset: 0
                    },
                    colors: ['#db3727'],
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: .4,
                            opacityTo: .05,
                            stops: [0, 100]
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    markers: {
                        size: 4,
                        strokeWidth: 2,
                        hover: {
                            size: 6
                        }
                    },
                    xaxis: {
                        categories: @json($weekLabels),
                        labels: {
                            style: {
                                colors: muted
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: muted
                            },
                            formatter: v => symbol + Number(v).toLocaleString()
                        }
                    },
                    grid: {
                        borderColor: gridColor,
                        strokeDashArray: 4,
                        padding: {
                            top: -10
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: v => money(v)
                        }
                    }
                }).render();
            }
        });
    </script>
@endpush
