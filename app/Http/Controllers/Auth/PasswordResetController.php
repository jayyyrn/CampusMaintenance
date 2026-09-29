<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    /** Code expiry in minutes */
    const CODE_EXPIRY_MINUTES = 15;

    /** Max wrong attempts before code is invalidated */
    const MAX_ATTEMPTS = 5;

    /** Session key holding the email being verified */
    const SESSION_EMAIL = 'pwd_reset.email';

    /** Session key holding the timestamp when code was verified */
    const SESSION_VERIFIED_AT = 'pwd_reset.verified_at';

    // ═══════════════════════════════════════════════════════════════
    // STEP 1 — Show email form
    // ═══════════════════════════════════════════════════════════════
    public function showEmailForm()
    {
        return view('auth.forgot-password');
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 1b — Send code to email
    // ═══════════════════════════════════════════════════════════════
    public function sendCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = strtolower(trim($request->email));
        $user  = User::where('email', $email)->where('status', 'active')->first();

        // Always return the same message (prevents user enumeration)
        if (!$user) {
            return redirect()->route('password.verify.form')
                ->with('status', 'If that email exists, we sent a code.');
        }

        // Generate a 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store hashed code, invalidating any previous code for this email
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($code),
                'attempts'   => 0,
                'created_at' => now(),
            ]
        );

        // Send the email
        try {
            Mail::to($email)->send(
                new PasswordResetCodeMail($code, $user->full_name, self::CODE_EXPIRY_MINUTES)
            );
        } catch (\Throwable $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());
            return back()->with('error', 'Could not send the code. Please check your email address and try again.');
        }

        // Store email in session for the next step
        session([self::SESSION_EMAIL => $email]);

        return redirect()->route('password.verify.form')
            ->with('status', "We sent a 6-digit code to {$email}. Check your inbox (and spam).");
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 2 — Show code input form
    // ═══════════════════════════════════════════════════════════════
    public function showCodeForm(Request $request)
    {
        // If no email in session, redirect back to Step 1
        if (!session(self::SESSION_EMAIL)) {
            return redirect()->route('password.request')
                ->with('error', 'Please enter your email first.');
        }

        return view('auth.verify-code', [
            'email' => session(self::SESSION_EMAIL),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 2b — Verify the code
    // ═══════════════════════════════════════════════════════════════
    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $email = session(self::SESSION_EMAIL);
        if (!$email) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please start over.');
        }

        $row = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$row) {
            return redirect()->route('password.request')
                ->with('error', 'No active reset request. Please start over.');
        }

        // Expiry check
        if (now()->diffInMinutes($row->created_at) > self::CODE_EXPIRY_MINUTES) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            session()->forget(self::SESSION_EMAIL);
            return redirect()->route('password.request')
                ->with('error', 'The code has expired. Please request a new one.');
        }

        // Attempts check (invalidated after 5 wrong)
        if ($row->attempts >= self::MAX_ATTEMPTS) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            session()->forget(self::SESSION_EMAIL);
            return redirect()->route('password.request')
                ->with('error', 'Too many incorrect attempts. Please request a new code.');
        }

        // Compare hashes
        if (!Hash::check($request->code, $row->token)) {
            DB::table('password_reset_tokens')->where('email', $email)
                ->increment('attempts');

            $remaining = self::MAX_ATTEMPTS - ($row->attempts + 1);

            return back()->withErrors([
                'code' => $remaining > 0
                    ? "Incorrect code. You have {$remaining} attempt(s) left."
                    : 'Too many incorrect attempts. Please request a new code.',
            ]);
        }

        // ✅ Code correct — mark session as verified
        session([
            self::SESSION_VERIFIED_AT => now()->timestamp,
        ]);

        return redirect()->route('password.reset.form');
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 3 — Show new password form
    // ═══════════════════════════════════════════════════════════════
    public function showResetForm(Request $request)
    {
        $email      = session(self::SESSION_EMAIL);
        $verifiedAt = session(self::SESSION_VERIFIED_AT);

        // Must have passed Step 2 within the last 15 minutes
        if (!$email || !$verifiedAt || (now()->timestamp - $verifiedAt) > (self::CODE_EXPIRY_MINUTES * 60)) {
            session()->forget([self::SESSION_EMAIL, self::SESSION_VERIFIED_AT]);
            return redirect()->route('password.request')
                ->with('error', 'Your verification expired. Please start over.');
        }

        return view('auth.reset-password', [
            'email' => $email,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 3b — Update password
    // ═══════════════════════════════════════════════════════════════
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $email      = session(self::SESSION_EMAIL);
        $verifiedAt = session(self::SESSION_VERIFIED_AT);

        // Must have passed Step 2
        if (!$email || !$verifiedAt || (now()->timestamp - $verifiedAt) > (self::CODE_EXPIRY_MINUTES * 60)) {
            session()->forget([self::SESSION_EMAIL, self::SESSION_VERIFIED_AT]);
            return redirect()->route('password.request')
                ->with('error', 'Your verification expired. Please start over.');
        }

        $user = User::where('email', $email)->where('status', 'active')->first();
        if (!$user) {
            session()->forget([self::SESSION_EMAIL, self::SESSION_VERIFIED_AT]);
            return redirect()->route('password.request')
                ->with('error', 'Account not found. Please start over.');
        }

        DB::beginTransaction();
        try {
            $user->update(['password' => $request->password]); // hashed cast handles hashing

            // Invalidate the code
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            // Clear session flags
            session()->forget([self::SESSION_EMAIL, self::SESSION_VERIFIED_AT]);

            audit('PASSWORD_RESET', 'user', $user->user_id, $user->username);

            DB::commit();

            return redirect()->route('login')
                ->with('status', 'Your password has been reset. Please log in with your new password.');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Password reset failed: ' . $e->getMessage());
            return back()->with('error', 'Could not reset password. Please try again.');
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // RESEND — Generate and send a new code
    // ═══════════════════════════════════════════════════════════════
    public function resendCode(Request $request)
    {
        $email = session(self::SESSION_EMAIL);
        if (!$email) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please start over.');
        }

        $user = User::where('email', $email)->where('status', 'active')->first();
        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Account not found. Please start over.');
        }

        // Generate a fresh code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token'      => Hash::make($code),
                'attempts'   => 0,
                'created_at' => now(),
            ]
        );

        try {
            Mail::to($email)->send(
                new PasswordResetCodeMail($code, $user->full_name, self::CODE_EXPIRY_MINUTES)
            );
        } catch (\Throwable $e) {
            \Log::error('Password reset resend failed: ' . $e->getMessage());
            return back()->with('error', 'Could not resend the code. Please try again.');
        }

        return back()->with('status', 'A new code has been sent to your email.');
    }
}