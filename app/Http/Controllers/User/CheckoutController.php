<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Addon;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();
        $fallback = asset('assets/img/no-img-item.png');
        $symbol = currency_symbol($locale);

        [$items, $total, $addonsByCategory] = $this->buildCart($locale, $fallback);

        // Cart empty → send them back to the menu
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

    /**
     * Save a per-item note (AJAX, no reload).
     */
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
   public function place(Request $request)
{
    $request->validate([
        'name'    => 'required|string|max:120',
        'mobile'  => 'required|string|max:40',
        'email'   => 'nullable|email|max:150',
        'address' => 'required|string|max:500',
        'note'    => 'nullable|string|max:1000',   // single order note
    ]);

    $locale   = app()->getLocale();
    $fallback = asset('assets/img/no-img-item.png');

    [$items, $total] = $this->buildCart($locale, $fallback);

    if (empty($items)) {
        return redirect()->route('cart')->with('error', __('Your cart is empty.'));
    }

    $orderNote = trim((string) $request->note);

    // TODO: persist the order to your orders table here.
    // $order = Order::create([
    //     'name' => $request->name, 'mobile' => $request->mobile,
    //     'email' => $request->email, 'address' => $request->address,
    //     'note' => $orderNote,
    //     'total' => $total, 'locale' => $locale, 'items' => $items,
    // ]);

    session()->forget('cart');

    return redirect()->route('menu')->with('success', __('Your order has been placed!'));
}

    /**
     * Build cart lines with re-resolved (locale-aware) prices + addons.
     * Returns [items, total, addonsByCategory].
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

            // Re-resolve addons
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

        // Addons grouped by category (only categories present in cart)
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
