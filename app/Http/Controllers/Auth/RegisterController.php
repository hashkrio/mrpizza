<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterOtpMail;
use App\Mail\RegistrationSuccessMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     * Show Register Page
     */
    public function register()
    {
        return view('auth.register');
    }

    /**
     * Send / Resend Registration OTP
     */
    /**
     * Send OTP (first time OR "Submit & Send OTP Again" — full re-validation)
     */
    public function sendOtp(Request $request)
    {
        $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'mobile' => ['required', 'string', 'max:20'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'confirmed', Password::defaults()],
                'terms' => ['accepted'],
            ],
            ['terms.accepted' => __('You must accept Terms & Conditions.')],
        );

        if (User::where('email', $request->email)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['email' => __('This email is already registered.')]);
        }

        if (User::where('mobile', $request->mobile)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['mobile' => __('This mobile number is already registered.')]);
        }

        // Did the user change any locked field since last OTP?
        $prev = session('register_data');
        $dataChanged = !$prev || $prev['name'] !== $request->name || $prev['mobile'] !== $request->mobile || $prev['email'] !== $request->email;

        // Enforce cooldown ONLY when nothing changed (pure resend of same data)
        if (!$dataChanged && session()->has('register_otp_sent_at')) {
            $elapsed = now()->diffInSeconds(session('register_otp_sent_at'), true);
            if ($elapsed < 60) {
                $remaining = 60 - $elapsed;
                return back()
                    ->withInput()
                    ->with([
                        'showOtp' => true,
                        'remaining' => $remaining,
                    ])
                    ->withErrors([
                        'otp' => __('Please wait %d seconds before requesting another OTP.', $remaining),
                    ]);
            }
        }

        $this->issueOtp($request);

        return back()->with([
            'showOtp' => true,
            'remaining' => 60,
            'success' => $dataChanged ? __('Details updated. A new OTP has been sent.') : __('OTP has been sent successfully.'),
        ]);
    }

    /**
     * Issue / store a fresh OTP and email it
     */
    private function issueOtp(Request $request): void
    {
        $otp = random_int(100000, 999999);

        session([
            'register_data' => [
                'name'           => $request->name,
                'mobile'         => $request->mobile,
                'email'          => $request->email,
                'password'       => Hash::make($request->password),
                'plain_password' => $request->password, 
                'role'           => 0,
            ],
            'register_otp'            => $otp,
            'register_otp_expires_at' => now()->addMinutes(10),
            'register_otp_sent_at'     => now(),
        ]);

        Mail::to($request->email)->send(new RegisterOtpMail($request->name, $otp));
    }

    /**
     * Verify Registration OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        // Registration session exists?
        if (!session()->has('register_data')) {
            return redirect()
                ->route('register')
                ->withErrors(['otp' => __('Registration session expired. Please register again.')]);
        }

        $data = session('register_data');

        // OTP expired? (10 minutes)
        if (now()->gt(session('register_otp_expires_at'))) {
            $this->clearOtpSession();

            return redirect()
                ->route('register')
                ->withErrors(['otp' => __('OTP has expired. Please request a new OTP.')]);
        }

        // OTP mismatch?
        if ($request->otp != session('register_otp')) {
            $remaining = 0;

            if (session()->has('register_otp_sent_at')) {
                $elapsed = now()->diffInSeconds(session('register_otp_sent_at'), true);
                $remaining = max(0, 60 - $elapsed);
            }

            return back()
                ->withInput()
                ->with([
                    'showOtp' => true,
                    'remaining' => $remaining,
                ])
                ->withErrors(['otp' => 'Invalid OTP.']);
        }

        // Final duplicate email check
        if (User::where('email', $data['email'])->exists()) {
            $this->clearOtpSession();

            return redirect()
                ->route('register')
                ->withErrors(['email' => __('This email is already registered.')]);
        }

        // Final duplicate mobile check
        if (User::where('mobile', $data['mobile'])->exists()) {
            $this->clearOtpSession();

            return redirect()
                ->route('register')
                ->withErrors(['mobile' => __('This mobile number is already registered.')]);
        }

    // Create User
    $user = User::create([
        'name'     => $data['name'],
        'mobile'   => $data['mobile'],
        'email'    => $data['email'],
        'password' => $data['password'],
        'role'     => $data['role'],
    ]);

    // Send Registration Success Email with details + plain password
    Mail::to($user->email)->send(
        new \App\Mail\RegistrationSuccessMail($user, $data['plain_password'])
    );

    $this->clearOtpSession();

    return redirect()->route('login')->with('success', __('Registration completed successfully. Please login.'));
    }

    /**
     * Clear OTP related session keys
     */
    private function clearOtpSession(): void
    {
        session()->forget(['register_data', 'register_otp', 'register_otp_expires_at', 'register_otp_sent_at']);
    }
}
