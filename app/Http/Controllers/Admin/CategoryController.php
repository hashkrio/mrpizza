<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index');
    }

    /**
     * DataTable Ajax
     */
    public function data(Request $request)
    {
        $query = Category::with('creator')->select('categories.*');

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->addColumn('image_col', function ($category) {
                if ($category->image) {
                    return '<img src="' . asset('public/storage/' . $category->image) . '" class="rounded" style="width:44px;height:44px;object-fit:cover;">';
                }

                return '<img src="'.asset('assets/img/no-img-item.png').'" class="rounded" style="width:44px;height:44px;object-fit:cover;">';
            })

            ->editColumn('status', function ($category) {
                $statusClass = $category->status ? 'bg-label-success' : 'bg-label-danger';
                $statusText = $category->status ? __('Active') : __('Inactive');

                return '<span class="badge ' . $statusClass . '">' . $statusText . '</span>';
            })

            ->addColumn('created_by', function ($category) {
                return optional($category->creator)->name ?? '-';
            })

            ->addColumn('action', function ($row) {
                return '
                    <a href="' .
                    route('admin.categories.edit', $row->id) .
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

            // Allow ordering
            ->orderColumn('name', 'name $1')
            ->orderColumn('status', 'status $1')

            ->rawColumns(['image_col', 'status', 'action'])
            ->toJson();
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'max:255', Rule::unique('categories')],
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        $category = new Category();
        $category->name = $request->name;
        $category->status = $request->status;
        $category->created_by = Auth::user()->id;

        if ($request->hasFile('image')) {
            $category->image = $request->file('image')->store('categories', 'public');
        }

        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Category created successfully.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,svg|max:2048',
        ]);

        $category->name = $request->name;
        $category->status = $request->status;

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $category->image = $request->file('image')->store('categories', 'public');
        }

        $category->save();

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }
    /**
     * Delete
     */
    public function destroy(Category $category): JsonResponse
    {
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully.',
        ]);
    }
}
