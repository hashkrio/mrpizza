<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Category;
use App\Support\LangHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class AddonController extends Controller
{
    public function index()
    {
        return view('admin.addons.index');
    }

    /**
     * DataTable Ajax
     */
    public function data(Request $request)
    {
        $query = Addon::with('category')->select('addons.*');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('category_name', function ($addon) {
                return optional($addon->category)->name ?? '-';
            })
            ->addColumn('price_col', function ($addon) {
                $prices = $addon->price;

                if (empty($prices) || !is_array($prices)) {
                    return '<span class="text-muted">—</span>';
                }

                $html = [];

                foreach ($prices as $locale => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }

                    $label = e(LangHelper::localeName($locale));

                    $html[] =
                        '<div class="mb-1">
                        <span class="badge bg-label-primary">' .
                        $label .
                        ': ' .
                        e($value) .
                        '</span>
                    </div>';
                }

                return !empty($html) ? implode('', $html) : '<span class="text-muted">—</span>';
            })
            ->editColumn('status', function ($addon) {
                $statusClass = $addon->status ? 'bg-label-success' : 'bg-label-danger';
                $statusText = $addon->status ? __('Active') : __('Inactive');
                return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                    <a href="' .
                    route('admin.addons.edit', $row->id) .
                    '" class="btn btn-sm btn-text-secondary rounded-pill me-1">
                        <i class="icon-base ti tabler-pencil"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-text-secondary rounded-pill deleteRow" data-id="' .
                    $row->id .
                    '">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                ';
            })
            ->orderColumn('name', 'name $1')
            ->orderColumn('status', 'status $1')
            ->rawColumns(['price_col', 'status', 'action'])
            ->toJson();
    }

    public function create()
    {
        $categories = Category::query()->where('status', 1)->orderBy('name')->get();
        $locales = LangHelper::localesWithNames();

        return view('admin.addons.create', compact('categories', 'locales'));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            $locales = LangHelper::availableLocales();
            $this->validateAddon($request, $locales);

            $addon = new Addon();
            $addon->category_id = $request->category_id;
            $addon->name = $request->name;
            $addon->status = $request->status;

            if (Auth::check()) {
                $addon->created_by = Auth::id();
            }

            $addon->price = $this->buildPriceArray($request, $locales);
            $addon->save();

            return redirect()->route('admin.addons.index')->with('success', __('Addon created successfully.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to create addon: ') . $e->getMessage());
        }
    }

    public function edit(Addon $addon)
    {
        $categories = Category::query()->where('status', 1)->orderBy('name')->get();
        $locales = LangHelper::localesWithNames();

        return view('admin.addons.edit', compact('addon', 'categories', 'locales'));
    }

    public function update(Request $request, Addon $addon): RedirectResponse
    {
        try {
            $locales = LangHelper::availableLocales();
            $this->validateAddon($request, $locales);

            $addon->category_id = $request->category_id;
            $addon->name = $request->name;
            $addon->status = $request->status;
            $addon->price = $this->buildPriceArray($request, $locales);
            $addon->save();

            return redirect()->route('admin.addons.index')->with('success', __('Addon updated successfully.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('Failed to update addon: ') . $e->getMessage());
        }
    }

    public function destroy(Addon $addon): JsonResponse
    {
        try {
            $addon->forceDelete();

            return response()->json([
                'success' => true,
                'message' => __('Addon deleted successfully.'),
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => __('Failed to delete addon.'),
                ],
                500,
            );
        }
    }

    /**
     * Validation shared by store/update.
     */
    private function validateAddon(Request $request, array $locales): array
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:255',
            'status' => 'required|integer',
            'price' => 'nullable|array',
        ];

        foreach ($locales as $loc) {
            $rules["price.$loc"] = 'nullable|numeric|min:0';
        }

        return $request->validate($rules);
    }

    /**
     * Build the price JSON structure by locale (short codes as keys).
     */
    private function buildPriceArray(Request $request, array $locales): array
    {
        $out = [];
        $input = $request->input('price', []);

        foreach ($locales as $loc) {
            $val = $input[$loc] ?? null;
            $out[$loc] = $val !== null && $val !== '' ? (float) $val : null;
        }

        return $out;
    }
}
