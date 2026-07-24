<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Supported filter keys.
     */
    protected array $ranges = ['today', 'week', 'month', 'last_month', 'year', 'all'];

    public function index(Request $request)
    {
        $range = $request->get('range', 'month');
        if (!in_array($range, $this->ranges, true)) {
            $range = 'month';
        }

        [$from, $to, $rangeLabel] = $this->resolveRange($range);

        $today = Carbon::today();
        $weekStart = Carbon::now()->subDays(6)->startOfDay();

        /*
        |--------------------------------------------------------------------
        | Reusable scoped query builder for the selected range
        |--------------------------------------------------------------------
        */
        $scoped = function ($query) use ($from, $to) {
            if ($from) {
                $query->where('orders.created_at', '>=', $from);
            }
            if ($to) {
                $query->where('orders.created_at', '<=', $to);
            }
            return $query;
        };

        /*
        |--------------------------------------------------------------------
        | 1. KPI counters — one pass over the orders table (range scoped)
        |--------------------------------------------------------------------
        | Orders are always created as "Confirmed" / "Paid" (COD only), so no
        | status filtering or status breakdown is needed anywhere below.
        */
        $statsQuery = Order::query();
        if ($from) {
            $statsQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $statsQuery->where('created_at', '<=', $to);
        }

        $stats = $statsQuery
            ->selectRaw(
                '
                COUNT(*)                                                              as total_orders,
                COALESCE(SUM(total), 0)                                               as total_sales,
                COALESCE(SUM(subtotal), 0)                                            as items_revenue,
                COALESCE(SUM(addons_total), 0)                                        as addons_revenue,
                SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END)                 as today_orders,
                COALESCE(SUM(CASE WHEN DATE(created_at) = ? THEN total ELSE 0 END), 0) as today_sales,
                SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END)                      as week_orders,
                COALESCE(SUM(CASE WHEN created_at >= ? THEN total ELSE 0 END), 0)     as week_sales,
                COUNT(DISTINCT user_id)                                               as buying_customers
            ',
                [$today->toDateString(), $today->toDateString(), $weekStart, $weekStart],
            )
            ->first();

        $totalOrders = (int) $stats->total_orders;

        // Users created inside the range
        $usersQuery = User::where('role', 0);
        if ($from) {
            $usersQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $usersQuery->where('created_at', '<=', $to);
        }

        $kpi = [
            'total_sales' => (float) $stats->total_sales,
            'today_sales' => (float) $stats->today_sales,
            'week_sales' => (float) $stats->week_sales,
            'items_revenue' => (float) $stats->items_revenue,
            'addons_revenue' => (float) $stats->addons_revenue,
            'total_orders' => $totalOrders,
            'today_orders' => (int) $stats->today_orders,
            'week_orders' => (int) $stats->week_orders,
            'buying_customers' => (int) $stats->buying_customers,
            'total_users' => User::where('role', 0)->count(),
            'range_users' => (clone $usersQuery)->count(),
            'new_users_today' => User::where('role', 0)->whereDate('created_at', $today)->count(),
            'total_items' => Item::count(),
            'active_items' => Item::where('status', 1)->count(),
            'total_categories' => Category::count(),
            'total_addons' => Addon::count(),
            'avg_order_value' => $totalOrders > 0 ? (float) $stats->total_sales / $totalOrders : 0.0,
        ];

        /*
        |--------------------------------------------------------------------
        | 2. Main trend chart — bucket size adapts to the selected range
        |--------------------------------------------------------------------
        */
        [$chartMonths, $chartSales, $chartOrders, $trendSubtitle] = $this->buildTrend($range, $from, $to);

        /*
        |--------------------------------------------------------------------
        | 3. Last 7 days revenue trend (always last 7 days, independent)
        |--------------------------------------------------------------------
        */
        $dailyRaw = Order::where('created_at', '>=', $weekStart)->selectRaw('DATE(created_at) as d, COALESCE(SUM(total), 0) as s')->groupBy('d')->get()->keyBy(fn($row) => (string) $row->d);

        $weekLabels = [];
        $weekSales = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $key = $day->toDateString();

            $weekLabels[] = $day->format('D');
            $weekSales[] = round((float) ($dailyRaw[$key]->s ?? 0), 2);
        }

        /*
        |--------------------------------------------------------------------
        | 4. Best selling items (from order_items, so it survives item edits)
        |--------------------------------------------------------------------
        */
        $topItems = OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')->when(true, $scoped)->selectRaw('order_items.item_name, SUM(order_items.qty) as qty_sold, SUM(order_items.total) as revenue')->groupBy('order_items.item_name')->orderByDesc('qty_sold')->limit(5)->get();

        $maxQtySold = (float) ($topItems->max('qty_sold') ?: 1);

        /*
        |--------------------------------------------------------------------
        | 5. Revenue by category
        |--------------------------------------------------------------------
        */
        $categoryRevenue = OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')->join('items', 'items.id', '=', 'order_items.item_id')->join('categories', 'categories.id', '=', 'items.category_id')->when(true, $scoped)->selectRaw('categories.name as category_name, SUM(order_items.total) as revenue, SUM(order_items.qty) as qty_sold')->groupBy('categories.id', 'categories.name')->orderByDesc('revenue')->get();

        $categoryLabels = $categoryRevenue->pluck('category_name')->all();
        $categorySeries = $categoryRevenue->map(fn($r) => round((float) $r->revenue, 2))->all();

        /*
        |--------------------------------------------------------------------
        | 6. Top customers by spend
        |--------------------------------------------------------------------
        */
        $topCustomersQuery = Order::query();
        if ($from) {
            $topCustomersQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $topCustomersQuery->where('created_at', '<=', $to);
        }

        $topCustomers = $topCustomersQuery->selectRaw('name, mobile, COUNT(*) as orders_count, SUM(total) as total_spent')->groupBy('name', 'mobile')->orderByDesc('total_spent')->limit(5)->get();

        /*
        |--------------------------------------------------------------------
        | 7. Recent orders
        |--------------------------------------------------------------------
        */
        $recentOrdersQuery = Order::with('user')->withCount('items');
        if ($from) {
            $recentOrdersQuery->where('created_at', '>=', $from);
        }
        if ($to) {
            $recentOrdersQuery->where('created_at', '<=', $to);
        }

        $recentOrders = $recentOrdersQuery->latest()->take(8)->get();

        return view('admin.dashboard', compact('kpi', 'range', 'rangeLabel', 'trendSubtitle', 'chartMonths', 'chartSales', 'chartOrders', 'weekLabels', 'weekSales', 'categoryLabels', 'categorySeries', 'categoryRevenue', 'topCustomers', 'topItems', 'maxQtySold', 'recentOrders'));
    }

    /**
     * Convert a range key into [from, to, label].
     */
    protected function resolveRange(string $range): array
    {
        return match ($range) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay(), __('Today')],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek(), __('This Week')],
            'last_month' => [Carbon::now()->subMonthNoOverflow()->startOfMonth(), Carbon::now()->subMonthNoOverflow()->endOfMonth(), __('Last Month')],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear(), __('This Year')],
            'all' => [null, null, __('All Time')],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth(), __('This Month')],
        };
    }

    /**
     * Build the main trend chart with a bucket size that suits the range.
     */
    protected function buildTrend(string $range, ?Carbon $from, ?Carbon $to): array
    {
        $labels = [];
        $sales = [];
        $orders = [];

        // --- Hourly buckets for "today" ---
        if ($range === 'today') {
            $rows = Order::whereBetween('created_at', [$from, $to])
                ->selectRaw('HOUR(created_at) as bucket, COALESCE(SUM(total),0) as s, COUNT(*) as c')
                ->groupBy('bucket')
                ->get()
                ->keyBy('bucket');

            for ($h = 0; $h < 24; $h++) {
                $labels[] = sprintf('%02d:00', $h);
                $sales[] = round((float) ($rows[$h]->s ?? 0), 2);
                $orders[] = (int) ($rows[$h]->c ?? 0);
            }

            return [$labels, $sales, $orders, __('Hourly performance for today')];
        }

        // --- Daily buckets for week / month / last month ---
        if (in_array($range, ['week', 'month', 'last_month'], true)) {
            $rows = Order::whereBetween('created_at', [$from, $to])
                ->selectRaw('DATE(created_at) as bucket, COALESCE(SUM(total),0) as s, COUNT(*) as c')
                ->groupBy('bucket')
                ->get()
                ->keyBy(fn($r) => (string) $r->bucket);

            $cursor = $from->copy()->startOfDay();
            $end = $to->copy()->startOfDay();

            while ($cursor->lte($end)) {
                $key = $cursor->toDateString();

                $labels[] = $cursor->format('d M');
                $sales[] = round((float) ($rows[$key]->s ?? 0), 2);
                $orders[] = (int) ($rows[$key]->c ?? 0);

                $cursor->addDay();
            }

            return [$labels, $sales, $orders, __('Daily performance for the selected period')];
        }

        // --- Monthly buckets for year / all time ---
        $monthFrom = $range === 'year' ? $from->copy() : Carbon::now()->subMonths(11)->startOfMonth();

        $monthTo = $range === 'year' ? $to->copy() : Carbon::now()->endOfMonth();

        $rows = Order::whereBetween('created_at', [$monthFrom, $monthTo])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bucket, COALESCE(SUM(total),0) as s, COUNT(*) as c")
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $cursor = $monthFrom->copy()->startOfMonth();
        $end = $monthTo->copy()->startOfMonth();

        while ($cursor->lte($end)) {
            $key = $cursor->format('Y-m');

            $labels[] = $cursor->format('M Y');
            $sales[] = round((float) ($rows[$key]->s ?? 0), 2);
            $orders[] = (int) ($rows[$key]->c ?? 0);

            $cursor->addMonthNoOverflow();
        }

        $subtitle = $range === 'year' ? __('Monthly performance this year') : __('Monthly performance over the last 12 months');

        return [$labels, $sales, $orders, $subtitle];
    }
}
