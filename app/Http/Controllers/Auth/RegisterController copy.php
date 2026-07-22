<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    

    /**
     * Show register form.
     */
    public function register()
    {
        return view('auth.register');
    }

    /**
     * Register a new user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'mobile'   => ['required', 'string', 'max:20'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms'    => ['accepted'],
        ], [
            'terms.accepted' => __('You must agree to the privacy policy & terms.'),
        ]);

        User::create([
            'name'     => $request->name,
            'mobile'   => $request->mobile,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 0, // Default User
        ]);

        return redirect()->route('login')->with('success', __('Registration successful. Please login.'));
    }
}
