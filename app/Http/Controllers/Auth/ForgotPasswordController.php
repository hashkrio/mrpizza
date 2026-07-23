<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    /**
     * Show the "enter your email" form.
     */
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Generate a token and email the reset link.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withErrors(['email' => __('We could not find an account with that email address.')])
                ->withInput();
        }

        if (!$user->is_active) {
            return back()
                ->withErrors(['email' => __('Your account has been deactivated. Please contact the administrator.')])
                ->withInput();
        }

        // Throttle: one link per 60 seconds
        $existing = DB::table('password_reset_tokens')->where('email', $user->email)->first();

        if ($existing && now()->diffInSeconds($existing->created_at) < 60) {
            return back()
                ->withErrors(['email' => __('Please wait a minute before requesting another reset link.')])
                ->withInput();
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ],
        );

        $link = route('password.reset', ['token' => $token]) . '?email=' . urlencode($user->email);

        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $link));
        } catch (\Throwable $e) {
            Log::error('Reset mail failed: ' . $e->getMessage());

            return back()
                ->withErrors(['email' => __('We could not send the email. Please try again later.')])
                ->withInput();
        }

        return back()->with('success', __('A password reset link has been sent to your email.'));
    }

    /**
     * Show the "set a new password" form.
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * Validate the token and save the new password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()
                ->withErrors(['email' => __('This password reset link is invalid.')])
                ->withInput();
        }

        // Link valid for 60 minutes
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return redirect()
                ->route('password.request')
                ->withErrors(['email' => __('This password reset link has expired. Please request a new one.')]);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withErrors(['email' => __('We could not find an account with that email address.')])
                ->withInput();
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', __('Your password has been reset. Please log in.'));
    }
}
