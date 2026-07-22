<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class HomeController extends Controller
{
    public function index()
    {
        if (Auth::check() && Auth::user()->role == 1) {
            return redirect()->route('admin.dashboard');
        }

        $categories = Category::query()->where('status', 1)->orderBy('name')->get();

        return view('users.home', compact('categories'));
    }

    public function menu(Request $request)
    {
        $activeCat = $request->category;

        $categories = Category::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('users.menu', [
            'categories' => $categories,
            'items' => $this->fetchMenuItems($activeCat),
            'activeCat' => $activeCat,
            'symbol' => currency_symbol(app()->getLocale()),
            'fallback' => asset('assets/img/no-img-item.png'),
        ]);
    }

    public function menuGo(Request $request)
    {
        session()->flash('preselect_category', $request->input('category'));

        return redirect()->route('menu');
    }

    public function menuItems(Request $request)
    {
        $categoryId = $request->query('category');

        $html = view('users.partials.menu-items', [
            'items' => $this->fetchMenuItems($categoryId),
            'symbol' => currency_symbol(app()->getLocale()),
            'fallback' => asset('assets/img/no-img-item.png'),
        ])->render();

        return response()->json(['html' => $html]);
    }

    private function fetchMenuItems($categoryId)
    {
        $locale = app()->getLocale();

        return Item::query()
            ->with('category:id,name')
            ->where('status', 1)
            ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
            ->orderBy('name')
            ->get()
            ->map(function ($item) use ($locale) {
                return [
                    'hash' => Crypt::encryptString($item->id),
                    'name' => $item->name,
                    'image' => $item->image ? asset('public/storage/' . $item->image) : null,
                    'category' => optional($item->category)->name,
                    'price' => $item->price[$locale] ?? null,
                ];
            });
    }

    public function itemDetail($id)
    {
        try {
            $realId = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            abort(404);
        }

        $locale = app()->getLocale();

        $item = Item::query()->with('category:id,name')->where('status', 1)->find($realId);

        if (!$item) {
            abort(404);
        }

        $related = Item::query()
            ->with('category:id,name')
            ->where('status', 1)
            ->where('category_id', $item->category_id)
            ->where('id', '!=', $item->id)
            ->orderBy('name')
            ->limit(4)
            ->get()
            ->map(function ($r) use ($locale) {
                return [
                    'hash' => Crypt::encryptString($r->id),
                    'name' => $r->name,
                    'image' => $r->image ? asset('public/storage/' . $r->image) : null,
                    'category' => optional($r->category)->name,
                    'price' => $r->price[$locale] ?? null,
                ];
            });

        $data = [
            'hash' => Crypt::encryptString($item->id),
            'name' => $item->name,
            'description' => $item->description,
            'image' => $item->image ? asset('public/storage/' . $item->image) : null,
            'category' => optional($item->category)->name,
            'price' => $item->price[$locale] ?? null,
        ];

        return view('users.item-detail', [
            'item' => $data,
            'related' => $related,
            'symbol' => currency_symbol($locale),
            'fallback' => asset('assets/img/no-img-item.png'),
        ]);
    }
}