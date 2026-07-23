<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.orders.index');
    }

    public function data(Request $request)
    {
        $query = Order::query()->select('orders.*');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($order) {
                return $order->created_at->format('d M Y, h:i A');
            })
            ->addColumn('customer', function ($order) {
                return e($order->name) . '<br><small class="text-muted">' . e($order->mobile) . '</small>';
            })
            ->addColumn('total', function ($order) {
                return $order->currency_symbol . number_format($order->total, 2);
            })
            ->addColumn('payment', function ($order) {
                $badge = $order->payment_status === 'Paid' ? 'bg-label-success' : 'bg-label-warning';
                return '<span class="badge ' . $badge . '">' . e(ucfirst($order->payment_status)) . '</span>' . '<br><small class="text-muted">' . e(strtoupper($order->payment_method)) . '</small>';
            })
            ->addColumn('status_badge', function ($order) {
                $map = [
                    'Pending' => 'bg-label-warning',
                    'Confirmed' => 'bg-label-success',
                    'Delivered' => 'bg-label-success',
                    'Cancelled' => 'bg-label-danger',
                ];
                $class = $map[$order->status] ?? 'bg-label-secondary';
                return '<span class="badge ' . $class . '">' . e(ucfirst($order->status)) . '</span>';
            })
            ->addColumn('action', function ($order) {
                return '<a href="' .
                    route('admin.orders.show', $order->id) .
                    '" class="btn icon-base btn-icon btn-text-secondary">
                            <i class="icon-base ti tabler-eye"></i>
                        </a>';
            })
            ->filterColumn('customer', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                        ->orWhere('mobile', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['customer', 'payment', 'status_badge', 'action'])
            ->make(true);
    }
    public function show(Order $order)
    {
        $order->load('items.addons');

        return view('admin.orders.show', ['order' => $order]);
    }
}
