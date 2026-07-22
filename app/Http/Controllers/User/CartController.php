<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Addon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class CartController extends Controller
{
    public function cart()
    {
        $locale = app()->getLocale();
        $fallback = asset('assets/img/no-img-item.png');
        $symbol = currency_symbol($locale);

        $cart = session()->get('cart', []);
        $items = [];
        $total = 0;
        $dirty = false;

        // Collect category ids so we can offer matching addons per line
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

            // ---- Re-resolve addon prices for the current locale ----
            $addonList = [];
            $addonTotal = 0;
            $storedAddons = $row['addons'] ?? [];

            foreach ($storedAddons as $addonId => $addonRow) {
                $addon = Addon::query()->where('status', 1)->find($addonId);

                // Addon removed/disabled → drop it from the line
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

                if ((float) ($addonRow['price'] ?? -1) !== (float) $addonPrice) {
                    $cart[$key]['addons'][$addonId]['price'] = $addonPrice;
                    $dirty = true;
                }
                $cart[$key]['addons'][$addonId]['name'] = $addon->name;

                $addonTotal += $addonPrice;
                $addonList[] = [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => $addonPrice,
                ];
            }

            $qty = (int) $row['qty'];
            // Each unit = base price + its addons; multiply the whole thing by qty
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
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        if ($dirty) {
            session()->put('cart', $cart);
        }

        // Available addons grouped by category (only for categories in the cart)
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

        return view('users.cart', [
            'items' => $items,
            'total' => $total,
            'symbol' => $symbol,
            'fallback' => $fallback,
            'addonsByCategory' => $addonsByCategory,
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'id' => 'required|string',
            'size' => 'nullable|string',
            'qty' => 'nullable|integer|min:1',
        ]);

        try {
            $itemId = Crypt::decryptString($request->id);
        } catch (DecryptException $e) {
            return $this->fail(__('Invalid item.'));
        }

        $locale = app()->getLocale();
        $item = Item::query()->where('status', 1)->find($itemId);

        if (!$item) {
            return $this->fail(__('Item not found.'));
        }

        $size = $request->input('size');
        $qty = max(1, (int) $request->input('qty', 1));
        $price = $this->resolvePrice($item, $locale, $size);

        if ($price === null) {
            return $this->fail(__('This item is not available for purchase.'));
        }

        $key = $item->id . ($size ? '_' . $size : '');
        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $qty;
        } else {
            $cart[$key] = [
                'item_id' => $item->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'size' => $size,
                'price' => $price,
                'qty' => $qty,
                'image' => $item->image ? asset('public/storage/' . $item->image) : null,
                'addons' => [],
            ];
        }

        session()->put('cart', $cart);

        return $this->ok(__('Added to cart.'));
    }

    /**
     * Replace the full set of addons on a cart line with the given ids.
     */
    public function syncAddons(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'addon_ids' => 'nullable|array',
            'addon_ids.*' => 'integer',
        ]);

        $cart = session()->get('cart', []);
        $key = $request->key;

        if (!isset($cart[$key])) {
            return $this->fail(__('Item not in cart.'));
        }

        $locale = app()->getLocale();
        $categoryId = (int) ($cart[$key]['category_id'] ?? 0);
        $addonIds = $request->input('addon_ids', []);

        $newAddons = [];
        $addonTotal = 0;

        if (!empty($addonIds)) {
            $addons = Addon::query()->where('status', 1)->where('category_id', $categoryId)->whereIn('id', $addonIds)->get();

            foreach ($addons as $addon) {
                $addonPrice = $this->resolveAddonPrice($addon, $locale);
                if ($addonPrice === null) {
                    continue;
                }
                $newAddons[$addon->id] = [
                    'name' => $addon->name,
                    'price' => $addonPrice,
                ];
                $addonTotal += $addonPrice;
            }
        }

        $cart[$key]['addons'] = $newAddons;
        session()->put('cart', $cart);

        // Calculate line total for the modified item
        $basePrice = (float) ($cart[$key]['price'] ?? 0);
        $qty = (int) ($cart[$key]['qty'] ?? 1);
        $unitPrice = $basePrice + $addonTotal;
        $lineTotal = $unitPrice * $qty;

        // Recalculate overall grand total
        $overallTotal = 0;
        foreach ($cart as $row) {
            $itemBasePrice = (float) ($row['price'] ?? 0);
            $itemAddonTotal = 0;
            foreach ($row['addons'] ?? [] as $aRow) {
                $itemAddonTotal += (float) ($aRow['price'] ?? 0);
            }
            $overallTotal += ($itemBasePrice + $itemAddonTotal) * ((int) ($row['qty'] ?? 1));
        }

        return response()->json([
            'success' => true,
            'message' => __('Addons updated.'),
            'addon_total' => $addonTotal,
            'line_total' => $lineTotal,
            'cart_total' => $overallTotal,
            'count' => $this->cartCount(),
        ]);
    }
    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'qty' => 'required|integer|min:1',
        ]);

        $cart = session()->get('cart', []);

        if (!isset($cart[$request->key])) {
            return $this->fail(__('Item not in cart.'));
        }

        $cart[$request->key]['qty'] = max(1, (int) $request->qty);
        session()->put('cart', $cart);

        return $this->ok(__('Cart updated.'));
    }

    public function remove(Request $request)
    {
        $request->validate(['key' => 'required|string']);

        $cart = session()->get('cart', []);
        unset($cart[$request->key]);
        session()->put('cart', $cart);

        return $this->ok(__('Item removed.'));
    }

    public function clear()
    {
        session()->forget('cart');
        return $this->ok(__('Cart cleared.'));
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
        // Addons use a flat locale price (not sized)
        return $this->isValid($price) ? (float) $price : null;
    }

    private function isValid($v)
    {
        return $v !== null && $v !== '';
    }

    private function ok($message)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => $this->cartCount(),
        ]);
    }

    private function fail($message)
    {
        return response()->json(
            [
                'success' => false,
                'message' => $message,
            ],
            422,
        );
    }

    private function cartCount()
    {
        return count(session()->get('cart', []));
    }
}
