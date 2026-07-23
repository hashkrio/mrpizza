<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginOtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /**
     * Show Login Page
     */
    public function login()
    {
        return view('auth.login');
    }

    /**
     * Login using Password
     */
    public function loginWithPassword(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $user = User::where($field, $request->login)->first();

        if (!$user) {
            return back()
                ->withErrors([
                    'login' => __('Invalid email/mobile or password.'),
                ])
                ->withInput();
        }

        if (!$user->is_active) {
            return back()
                ->withErrors([
                    'login' => __('Your account has been deactivated. Please contact the administrator.'),
                ])
                ->withInput();
        }

        if (
            Auth::attempt(
                [
                    $field => $request->login,
                    'password' => $request->password,
                ],
                $request->filled('remember'),
            )
        ) {
            $request->session()->regenerate();

            return $this->redirectUser();
        }

        return back()
            ->withErrors([
                'login' => __('Invalid email/mobile or password.'),
            ])
            ->withInput();
    }

    /**
     * Send OTP
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'login' => 'required',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $user = User::where($field, $request->login)->first();

        if (!$user) {
            return back()->withErrors([
                'login' => __('User not found.'),
            ]);
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'login' => __('Your account has been deactivated. Please contact the administrator.'),
            ]);
        }

        /**
         * Prevent resend within 60 seconds
         */
        if ($user->otp_sent_at && now()->diffInSeconds($user->otp_sent_at) < 60) {
            $remaining = 60 - now()->diffInSeconds($user->otp_sent_at);

            return back()
                ->with([
                    'showOtp' => true,
                    'login' => $request->login,
                ])
                ->withErrors([
                    'otp' => __('Please wait %d seconds before requesting another OTP.', $remaining),
                ]);
        }

        $otp = random_int(100000, 999999);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'otp_sent_at' => now(),
        ]);

        Mail::to($user->email)->send(new LoginOtpMail($user, $otp));

        return back()->with([
            'showOtp' => true,
            'login' => $request->login,
            'success' => __('OTP has been sent to your registered email.'),
        ]);
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'otp' => 'required|digits:6',
        ]);

        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'mobile';

        $user = User::where($field, $request->login)->first();

        if (!$user) {
            return back()->withErrors([
                'login' => __('User not found.'),
            ]);
        }

        if (!$user->is_active) {
            return back()
                ->with([
                    'showOtp' => true,
                    'login' => $request->login,
                ])
                ->withErrors([
                    'login' => __('Your account has been deactivated. Please contact the administrator.'),
                ]);
        }

        if (!$user->otp || !$user->otp_expires_at) {
            return back()
                ->with([
                    'showOtp' => true,
                    'login' => $request->login,
                ])
                ->withErrors([
                    'otp' => __('OTP not found. Please request a new OTP.'),
                ]);
        }

        if (now()->gt($user->otp_expires_at)) {
            return back()
                ->with([
                    'showOtp' => true,
                    'login' => $request->login,
                ])
                ->withErrors([
                    'otp' => __('OTP has expired. Please request a new OTP.'),
                ]);
        }

        if ($user->otp != $request->otp) {
            return back()
                ->with([
                    'showOtp' => true,
                    'login' => $request->login,
                ])
                ->withErrors([
                    'otp' => __('Invalid OTP.'),
                ]);
        }

        /**
         * Clear OTP
         */
        $user->update([
            'otp' => null,
            'otp_expires_at' => null,
            'otp_sent_at' => null,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return $this->redirectUser();
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * Redirect after login
     */
    private function redirectUser()
    {
        if (Auth::user()->role == 1) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended(route('home'));
    }
}
