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
    const CODE_EXPIRY_MINUTES = 15;
    const MAX_ATTEMPTS        = 5;

    const SESSION_USER_ID     = 'pwd_reset.user_id';
    const SESSION_VERIFIED_AT = 'pwd_reset.verified_at';

    // ═══════════════════════════════════════════════════════════════
    // STEP 1 — Show username form
    // ═══════════════════════════════════════════════════════════════
    public function showEmailForm()
    {
        return view('auth.forgot-password');
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 1b — Look up user by username, send code to their registered email
    // ═══════════════════════════════════════════════════════════════
    public function sendCode(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:50',
        ]);

              $username = trim($request->username);
        $user     = User::where('username', $username)
                        ->where('status', 'active')
                        ->first();

        // Reject non-existent / inactive usernames with a generic message.
        // The message is deliberately vague ("invalid or inactive") so it does
        // not confirm whether a username exists in the system.
        if (!$user) {
            return back()
                ->with('error', 'Username not found or account is inactive. Please check your username and try again.')
                ->withInput();
        }

        // Require the account to have a real email on file
        if (!$user->email || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            return back()
                ->with('error', 'This account does not have a valid email address on file. Contact the administrator.')
                ->withInput();
        }

        // Generate a 6-digit code
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Store hashed code, keyed by user_id
        DB::table('password_reset_tokens')->updateOrInsert(
            ['user_id' => $user->user_id],
            [
                'token'      => Hash::make($code),
                'attempts'   => 0,
                'created_at' => now(),
            ]
        );

        // Send the code to the REGISTERED email (not user-typed)
        try {
            Mail::to($user->email)->send(
                new PasswordResetCodeMail($code, $user->full_name, self::CODE_EXPIRY_MINUTES)
            );
        } catch (\Throwable $e) {
            \Log::error('Password reset email failed: ' . $e->getMessage());
            return back()
                ->with('error', 'Could not send the code. Please try again later.')
                ->withInput();
        }

        // Store user_id + a masked version of their email for display
        session([
            self::SESSION_USER_ID    => $user->user_id,
            'pwd_reset.masked_email' => $this->maskEmail($user->email),
        ]);

        return redirect()->route('password.verify.form')
            ->with('status', 'We sent a 6-digit code to your registered email. Check your inbox (and spam).');
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 2 — Show code form
    // ═══════════════════════════════════════════════════════════════
    public function showCodeForm()
    {
        // Require a session that started the flow
        if (!session()->has('pwd_reset.masked_email')) {
            return redirect()->route('password.request')
                ->with('error', 'Please enter your username first.');
        }

        return view('auth.verify-code', [
            'maskedEmail' => session('pwd_reset.masked_email'),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 2b — Verify the code
    // ═══════════════════════════════════════════════════════════════
    public function verifyCode(Request $request)
    {
        $request->validate(['code' => 'required|digits:6']);

        $userId = session(self::SESSION_USER_ID);

        // If no user_id in session (failed enumeration OR session lost), fail silently
        if (!$userId) {
            return redirect()->route('password.request')
                ->with('error', 'The code is invalid or has expired. Please try again.');
        }

        $row = DB::table('password_reset_tokens')->where('user_id', $userId)->first();

        if (!$row) {
            return redirect()->route('password.request')
                ->with('error', 'No active reset request. Please start over.');
        }

        // Expiry
        if (now()->diffInMinutes($row->created_at) > self::CODE_EXPIRY_MINUTES) {
            DB::table('password_reset_tokens')->where('user_id', $userId)->delete();
            session()->forget([self::SESSION_USER_ID, 'pwd_reset.masked_email']);
            return redirect()->route('password.request')
                ->with('error', 'The code has expired. Please request a new one.');
        }

        // Attempts
        if ($row->attempts >= self::MAX_ATTEMPTS) {
            DB::table('password_reset_tokens')->where('user_id', $userId)->delete();
            session()->forget([self::SESSION_USER_ID, 'pwd_reset.masked_email']);
            return redirect()->route('password.request')
                ->with('error', 'Too many incorrect attempts. Please request a new code.');
        }

        // Compare hash
        if (!Hash::check($request->code, $row->token)) {
            DB::table('password_reset_tokens')
                ->where('user_id', $userId)
                ->increment('attempts');

            $remaining = self::MAX_ATTEMPTS - ($row->attempts + 1);

            return back()->withErrors([
                'code' => $remaining > 0
                    ? "Incorrect code. You have {$remaining} attempt(s) left."
                    : 'Too many incorrect attempts. Please request a new code.',
            ]);
        }

        // ✅ Correct — mark session as verified
        session([self::SESSION_VERIFIED_AT => now()->timestamp]);

        return redirect()->route('password.reset.form');
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 3 — Show new password form
    // ═══════════════════════════════════════════════════════════════
    public function showResetForm()
{
    $userId     = session(self::SESSION_USER_ID);
    $verifiedAt = session(self::SESSION_VERIFIED_AT);

    if (!$userId || !$verifiedAt
        || (now()->timestamp - $verifiedAt) > (self::CODE_EXPIRY_MINUTES * 60)) {
        session()->forget([self::SESSION_USER_ID, self::SESSION_VERIFIED_AT, 'pwd_reset.masked_email']);
        return redirect()->route('password.request')
            ->with('error', 'Your verification expired. Please start over.');
    }

    // Note: no variables passed. The Blade uses only session data + validation errors.
    return view('auth.reset-password');
}

    // ═══════════════════════════════════════════════════════════════
    // STEP 3b — Update password
    // ═══════════════════════════════════════════════════════════════
    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $userId     = session(self::SESSION_USER_ID);
        $verifiedAt = session(self::SESSION_VERIFIED_AT);

        if (!$userId || !$verifiedAt
            || (now()->timestamp - $verifiedAt) > (self::CODE_EXPIRY_MINUTES * 60)) {
            session()->forget([self::SESSION_USER_ID, self::SESSION_VERIFIED_AT, 'pwd_reset.masked_email']);
            return redirect()->route('password.request')
                ->with('error', 'Your verification expired. Please start over.');
        }

        $user = User::where('user_id', $userId)->where('status', 'active')->first();
        if (!$user) {
            session()->forget([self::SESSION_USER_ID, self::SESSION_VERIFIED_AT, 'pwd_reset.masked_email']);
            return redirect()->route('password.request')
                ->with('error', 'Account not found. Please start over.');
        }

        DB::beginTransaction();
        try {
            $user->update(['password' => $request->password]); // hashed cast handles hashing

            DB::table('password_reset_tokens')->where('user_id', $userId)->delete();

            session()->forget([self::SESSION_USER_ID, self::SESSION_VERIFIED_AT, 'pwd_reset.masked_email']);

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
    // RESEND — Generate and send a fresh code
    // ═══════════════════════════════════════════════════════════════
    public function resendCode()
    {
        $userId = session(self::SESSION_USER_ID);
        if (!$userId) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please start over.');
        }

        $user = User::where('user_id', $userId)->where('status', 'active')->first();
        if (!$user || !$user->email) {
            return redirect()->route('password.request')
                ->with('error', 'Account not found. Please start over.');
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['user_id' => $userId],
            [
                'token'      => Hash::make($code),
                'attempts'   => 0,
                'created_at' => now(),
            ]
        );

        try {
            Mail::to($user->email)->send(
                new PasswordResetCodeMail($code, $user->full_name, self::CODE_EXPIRY_MINUTES)
            );
        } catch (\Throwable $e) {
            \Log::error('Password reset resend failed: ' . $e->getMessage());
            return back()->with('error', 'Could not resend the code. Please try again.');
        }

        // Refresh masked email in session (no change, but safe)
        session(['pwd_reset.masked_email' => $this->maskEmail($user->email)]);

        return back()->with('status', 'A new code has been sent to your registered email.');
    }

    // ═══════════════════════════════════════════════════════════════
    // Helper — Mask an email address
    // ═══════════════════════════════════════════════════════════════
    private function maskEmail(string $email): string
    {
        [$name, $domain] = explode('@', $email, 2);

        if (strlen($name) <= 2) {
            $masked = substr($name, 0, 1) . '*';
        } else {
            $masked = substr($name, 0, 1)
                    . str_repeat('*', max(1, strlen($name) - 2))
                    . substr($name, -1);
        }

        return $masked . '@' . $domain;
    }
}