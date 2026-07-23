<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Addon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function index()
    {
        if (!auth()->check()) {
            session(['url.intended' => route('checkout')]);
        }
        $locale = app()->getLocale();
        $fallback = asset('assets/img/no-img-item.png');
        $symbol = currency_symbol($locale);

        [$items, $total, $addonsByCategory] = $this->buildCart($locale, $fallback);

        if (empty($items)) {
            return redirect()->route('cart');
        }

        return view('users.checkout', [
            'items' => $items,
            'total' => $total,
            'symbol' => $symbol,
            'fallback' => $fallback,
            'addonsByCategory' => $addonsByCategory,
        ]);
    }

    public function saveNote(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'note' => 'nullable|string|max:500',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$request->key])) {
            return response()->json(['success' => false, 'message' => __('Item not in cart.')], 422);
        }

        $cart[$request->key]['note'] = trim((string) $request->note);
        session()->put('cart', $cart);

        return response()->json(['success' => true, 'message' => __('Note saved.')]);
    }

    /**
     * Place the order.
     */
    public function checkout_place(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'mobile' => 'required|string|max:40',
            'email' => 'nullable|email|max:150',
            'address' => 'required|string|max:500',
            'note' => 'nullable|string|max:1000',
            'payment_method' => 'nullable|string|max:30',
        ]);

        $locale = app()->getLocale();
        $fallback = asset('assets/img/no-img-item.png');

        [$items, $total] = $this->buildCart($locale, $fallback);

        if (empty($items)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => __('Your cart is empty.'),
                ],
                422,
            );
        }

        $itemsTotal = 0;
        $addonsTotal = 0;

        foreach ($items as $row) {
            $itemsTotal += $row['price'] * $row['qty'];
            $addonsTotal += $row['addon_total'] * $row['qty'];
        }

        $subtotal = $itemsTotal + $addonsTotal;
        $discount = 0;
        $deliveryCharge = 0;
        $tax = 0;
        $grandTotal = $subtotal - $discount + $deliveryCharge + $tax;

        $order = DB::transaction(function () use ($request, $items, $locale, $itemsTotal, $addonsTotal, $subtotal, $discount, $deliveryCharge, $tax, $grandTotal) {
            $order = Order::create([
                // 'order_no'         => $this->generateOrderNo(),
                'user_id' => auth()->id(),
                'name' => $request->name,
                'mobile' => $request->mobile,
                'email' => $request->email,
                'address' => $request->address,
                'order_note' => trim((string) $request->note) ?: null,
                'items_total' => $itemsTotal,
                'addons_total' => $addonsTotal,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_charge' => $deliveryCharge,
                'tax' => $tax,
                'total' => $grandTotal,
                'lang' => $locale,
                'currency_symbol' => currency_symbol($locale),
                'currency_code' => function_exists('currency_code') ? currency_code($locale) : null,
                'payment_method' => $request->payment_method ?: 'cod',
                'payment_status' => 'Paid',
                'status' => 'Confirmed',
            ]);

            foreach ($items as $row) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $row['item_id'],
                    'item_name' => $row['name'],
                    'size' => $row['size'],
                    'qty' => $row['qty'],
                    'item_price' => $row['price'],
                    'total' => $row['line_total'],
                    'lang' => $locale,
                ]);

                foreach ($row['addons'] as $addon) {
                    OrderAddon::create([
                        'order_id' => $order->id,
                        'order_item_id' => $orderItem->id,
                        'addon_id' => $addon['id'],
                        'addon_name' => $addon['name'],
                        'addon_price' => $addon['price'],
                        'lang' => $locale,
                    ]);
                }
            }

            return $order;
        });

        session()->forget('cart');

        $order->load('items.addons');

        if ($order->email) {
            try {
                Mail::to($order->email)->send(new OrderPlacedMail($order));
            } catch (\Throwable $e) {
                Log::error('Order mail failed: ' . $e->getMessage());
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Your order has been placed!'),
                'order_no' => $order->order_no,
                'redirect' => route('menu'),
            ]);
        }

        return redirect()->route('menu')->with('success', __('Your order has been placed!'));
    }

    // private function generateOrderNo()
    // {
    //     do {
    //         $no = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    //     } while (Order::where('order_no', $no)->exists());

    //     return $no;
    // }

    /**
     * Build cart lines with re-resolved (locale-aware) prices + addons.
     */
    private function buildCart($locale, $fallback)
    {
        $cart = session()->get('cart', []);
        $items = [];
        $total = 0;
        $dirty = false;
        $categoryIds = [];

        foreach ($cart as $key => $row) {
            $item = Item::query()
                ->where('status', 1)
                ->find($row['item_id'] ?? null);

            if (!$item) {
                unset($cart[$key]);
                $dirty = true;
                continue;
            }

            $price = $this->resolvePrice($item, $locale, $row['size'] ?? null);

            if ($price === null) {
                unset($cart[$key]);
                $dirty = true;
                continue;
            }

            if ((float) $row['price'] !== (float) $price) {
                $cart[$key]['price'] = $price;
                $dirty = true;
            }

            $cart[$key]['name'] = $item->name;
            $cart[$key]['image'] = $item->image ? asset('public/storage/' . $item->image) : null;
            $cart[$key]['category_id'] = $item->category_id;

            $addonList = [];
            $addonTotal = 0;

            foreach ($row['addons'] ?? [] as $addonId => $addonRow) {
                $addon = Addon::query()->where('status', 1)->find($addonId);

                if (!$addon) {
                    unset($cart[$key]['addons'][$addonId]);
                    $dirty = true;
                    continue;
                }

                $addonPrice = $this->resolveAddonPrice($addon, $locale);
                if ($addonPrice === null) {
                    unset($cart[$key]['addons'][$addonId]);
                    $dirty = true;
                    continue;
                }

                $cart[$key]['addons'][$addonId]['name'] = $addon->name;
                $cart[$key]['addons'][$addonId]['price'] = $addonPrice;

                $addonTotal += $addonPrice;
                $addonList[] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => $addonPrice,
                ];
            }

            $qty = (int) $row['qty'];
            $unitPrice = $price + $addonTotal;
            $lineTotal = $unitPrice * $qty;
            $total += $lineTotal;

            $categoryIds[] = $item->category_id;

            $items[] = [
                'key' => $key,
                'item_id' => $item->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'size' => $row['size'] ?? null,
                'price' => $price,
                'qty' => $qty,
                'image' => $cart[$key]['image'] ?? $fallback,
                'addons' => $addonList,
                'addon_total' => $addonTotal,
                'line_total' => $lineTotal,
                'note' => $row['note'] ?? '',
            ];
        }

        if ($dirty) {
            session()->put('cart', $cart);
        }

        $addonsByCategory = [];
        if (!empty($categoryIds)) {
            $addons = Addon::query()->where('status', 1)->whereIn('category_id', array_unique($categoryIds))->orderBy('name')->get();

            foreach ($addons as $addon) {
                $addonPrice = $this->resolveAddonPrice($addon, $locale);
                if ($addonPrice === null) {
                    continue;
                }
                $addonsByCategory[$addon->category_id][] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => $addonPrice,
                ];
            }
        }

        return [$items, $total, $addonsByCategory];
    }

    private function resolvePrice($item, $locale, $size)
    {
        $price = $item->price[$locale] ?? null;

        if (is_array($price)) {
            if ($size && isset($price[$size]) && $this->isValid($price[$size])) {
                return (float) $price[$size];
            }
            return null;
        }

        return $this->isValid($price) ? (float) $price : null;
    }

    private function resolveAddonPrice($addon, $locale)
    {
        $price = $addon->price[$locale] ?? null;
        return $this->isValid($price) ? (float) $price : null;
    }

    private function isValid($v)
    {
        return $v !== null && $v !== '';
    }
}
