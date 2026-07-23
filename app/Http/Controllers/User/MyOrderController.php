<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;

class MyOrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->with(['items.addons'])
            ->where('user_id', auth()->id())
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->latest()
            ->paginate(5);

        return view('users.order', [
            'orders' => $orders,
        ]);
    }
}
