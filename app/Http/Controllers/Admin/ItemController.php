<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Support\LangHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ItemController extends Controller
{
    public function index()
    {
        return view('admin.items.index');
    }

    /**
     * DataTable Ajax
     */
    public function data(Request $request)
    {
        $query = Item::with(['category', 'creator'])->select('items.*');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('image_col', function ($item) {
                if ($item->image) {
                    return '<img src="' . asset('public/storage/' . $item->image) . '" class="rounded" style="width:44px;height:44px;object-fit:cover;">';
                }
                return '<img src="'.asset('assets/img/no-img-item.png').'" class="rounded" style="width:44px;height:44px;object-fit:cover;">';
            })
            ->addColumn('category_name', function ($item) {
                return optional($item->category)->name ?? '-';
            })
         ->addColumn('price_col', function ($item) {
    $prices = $item->price;

    if (empty($prices) || !is_array($prices)) {
        return '<span class="text-muted">—</span>';
    }

    $html = [];

    foreach ($prices as $locale => $value) {

        $label = e(LangHelper::localeName($locale));

        // Simple price (no sizes)
        if (!is_array($value)) {
            if ($value === null || $value === '') {
                continue;
            }

            $html[] = '<div class="mb-1">
                <span class="badge bg-label-primary">'
                    . $label . ': ' . e($value) .
                '</span>
            </div>';

            continue;
        }

        // Size-wise prices
        $priceList = [];

        foreach ($value as $size => $price) {
            if ($price === null || $price === '') {
                continue;
            }

            $priceList[] = e($price) . ' (' . e(ucfirst($size)) . ')';
        }

        if (empty($priceList)) {
            continue;
        }

        $html[] = '<div class="mb-1">
            <span class="badge bg-label-primary">'
                . $label . ': ' . implode(', ', $priceList) .
            '</span>
        </div>';
    }

    return !empty($html)
        ? implode('', $html)
        : '<span class="text-muted">—</span>';
})
            ->editColumn('status', function ($item) {
                $statusClass = $item->status ? 'bg-label-success' : 'bg-label-danger';
                $statusText  = $item->status ? __('Active') : __('Inactive');
                return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' . route('admin.items.edit', $row->id) . '" class="btn btn-sm btn-text-secondary rounded-pill me-1">
                        <i class="icon-base ti tabler-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-text-secondary rounded-pill deleteRow" data-id="' . $row->id . '">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                ';
            })
            ->orderColumn('name', 'name $1')
            ->orderColumn('status', 'status $1')
            ->rawColumns(['image_col', 'price_col', 'status', 'action'])
            ->toJson();
    }

    public function create()
    {
        $categories = Category::query()->where('status', 1)->orderBy('name')->get();
        $locales = LangHelper::localesWithNames();

        return view('admin.items.create', compact('categories', 'locales'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $locales = LangHelper::availableLocales();
            $this->validateItem($request, $locales);

            $item = new Item();
            $item->category_id = $request->category_id;
            $item->name        = $request->name;
            $item->description = $request->description;
            $item->status      = $request->status;
            $item->has_sizes   = $request->boolean('has_sizes');
            $item->created_by  = Auth::user()->id;

            $item->sizes = $item->has_sizes
                ? array_values(array_filter($request->input('sizes', [])))
                : null;

            $item->price = $this->buildPriceArray($request, $locales, $item->has_sizes, $item->sizes);

            if ($request->hasFile('image')) {
                $item->image = $request->file('image')->store('items', 'public');
            }

            $item->save();

            return redirect()->route('admin.items.index')->with('success', __('Item created successfully.'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', __('Failed to create item: ') . $e->getMessage());
        }
    }

    public function edit(Item $item)
    {
        $categories = Category::query()->where('status', 1)->orderBy('name')->get();
        $locales = LangHelper::localesWithNames();

        return view('admin.items.edit', compact('item', 'categories', 'locales'));
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        try {
            $locales = LangHelper::availableLocales();
            $this->validateItem($request, $locales);

            $item->category_id = $request->category_id;
            $item->name        = $request->name;
            $item->description = $request->description;
            $item->status      = $request->status;
            $item->has_sizes   = $request->boolean('has_sizes');

            $item->sizes = $item->has_sizes
                ? array_values(array_filter($request->input('sizes', [])))
                : null;

            $item->price = $this->buildPriceArray($request, $locales, $item->has_sizes, $item->sizes);

            if ($request->hasFile('image')) {
                if ($item->image) {
                    Storage::disk('public')->delete($item->image);
                }
                $item->image = $request->file('image')->store('items', 'public');
            }

            $item->save();

            return redirect()->route('admin.items.index')->with('success', __('Item updated successfully.'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', __('Failed to update item: ') . $e->getMessage());
        }
    }

    public function destroy(Item $item): JsonResponse
    {
        try {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }

            $item->forceDelete();

            return response()->json([
                'success' => true,
                'message' => __('Item deleted successfully.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to delete item.'),
            ], 500);
        }
    }

    /**
     * Validation shared by store/update.
     */
    private function validateItem(Request $request, array $locales): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|integer',
            'has_sizes'   => 'nullable|integer',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
            'sizes'       => 'nullable|array',
            'sizes.*'     => 'nullable|string|max:50',
            'price'       => 'nullable|array',
        ];

        $hasSizes = $request->boolean('has_sizes');

        foreach ($locales as $loc) {
            if ($hasSizes) {
                $rules["price.$loc"]   = 'nullable|array';
                $rules["price.$loc.*"] = 'nullable|numeric|min:0';
            } else {
                $rules["price.$loc"]   = 'nullable|numeric|min:0';
            }
        }

        return $request->validate($rules);
    }

    /**
     * Build the price JSON depending on whether sizes are enabled.
     * Array keys stay as short locale codes (e.g. "en", "pt").
     */
    private function buildPriceArray(Request $request, array $locales, bool $hasSizes, ?array $sizes): array
    {
        $out = [];
        $input = $request->input('price', []);

        foreach ($locales as $loc) {
            if ($hasSizes) {
                $sizePrices = [];
                $locPrices = is_array($input[$loc] ?? null) ? $input[$loc] : [];

                foreach (($sizes ?? []) as $size) {
                    $key = strtolower($size);
                    $val = $locPrices[$key] ?? ($locPrices[$size] ?? null);
                    if ($val !== null && $val !== '') {
                        $sizePrices[$key] = (float) $val;
                    }
                }
                $out[$loc] = $sizePrices;
            } else {
                $val = $input[$loc] ?? null;
                $out[$loc] = ($val !== null && $val !== '') ? (float) $val : null;
            }
        }

        return $out;
    }
}