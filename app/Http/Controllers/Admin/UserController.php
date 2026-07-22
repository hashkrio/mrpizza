<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Show Users page
     */
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * DataTable Ajax
     */
    public function data(Request $request)
    {
        $query = User::select('users.*')
            ->where('role', 0)
            ->where('id', '!=', Auth::user()->id);

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->addColumn('image_col', function ($user) {
                if ($user->profile_image) {
                    return '<img src="' . asset('public/storage/profile/' . $user->profile_image) . '"  style="width:44px;height:44px;object-fit:cover;border-radius: 50% !important;">';
                }
                return '<img src="' . asset('/assets/img/avatar.png') . '"  style="width:44px;height:44px;object-fit:cover;border-radius: 50% !important;">';
            })

            ->editColumn('created_at', function ($row) {
                return $row->created_at->format('d M Y h:i A');
            })

            ->editColumn('is_active', function ($user) {
                $checked = $user->is_active ? 'checked' : '';

                return '
                    <label class="switch switch-primary"> <input type="checkbox" class="switch-input toggleStatus" data-id="' .
                    $user->id .
                    '" ' .
                    $checked .
                    '>
                        <span class="switch-toggle-slider">
                            <span class="switch-on"></span>
                            <span class="switch-off"></span>
                        </span>
                    </label>
                ';
            })

            ->addColumn('action', function ($row) {
                return '
                    <button type="button" class="btn btn-sm btn-text-secondary rounded-pill deleteRow" data-id="' .
                    $row->id .
                    '">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                ';
            })

            ->orderColumn('name', 'name $1')
            ->orderColumn('email', 'email $1')
            ->orderColumn('mobile', 'mobile $1')
            ->orderColumn('is_active', 'is_active $1')
            ->orderColumn('created_at', 'created_at $1')

            ->rawColumns(['image_col', 'is_active', 'action'])
            ->toJson();
    }

    /**
     * Toggle Active / Inactive
     */
    public function toggleStatus(Request $request, User $user)
    {
        $user->is_active = $user->is_active ? 0 : 1;
        $user->save();

        return response()->json([
            'success' => true,
            'is_active' => $user->is_active,
            'message' => $user->is_active ? __('User activated successfully.') : __('User deactivated successfully.'),
        ]);
    }

    /**
     * Delete User
     */
    public function destroy(User $user)
    {
        if ($user->profile_image && Storage::disk('public')->exists('profile/' . $user->profile_image)) {
            Storage::disk('public')->delete('profile/' . $user->profile_image);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => __('User has been deleted.'),
        ]);
    }
}
