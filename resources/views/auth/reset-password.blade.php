<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusFix — Reset Password</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-800 via-slate-900 to-brand-900 p-4">

<div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-2xl p-8 sm:p-10">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-brand-500 to-accent-500 flex items-center justify-center mb-4 shadow-lg shadow-brand-500/30">
                <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Set a new password</h1>
            <p class="text-slate-500 text-sm mt-2">
                ✓ Code verified for<br>
                <span class="font-semibold text-slate-700">{{ $email }}</span>
            </p>
        </div>

        @if (session('error'))
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-sm p-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-sm p-3 rounded-lg">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf

            <div>
                <label class="label" for="password">New Password</label>
                <input id="password" type="password" name="password"
                       required autofocus autocomplete="new-password"
                       minlength="6"
                       class="input" placeholder="At least 6 characters">
            </div>

            <div>
                <label class="label" for="password_confirmation">Confirm New Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation"
                       required autocomplete="new-password"
                       minlength="6"
                       class="input" placeholder="Re-enter new password">
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base">
                Reset Password
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-slate-500 hover:text-slate-800 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Login
            </a>
        </div>
    </div>

    <p class="text-center text-xs text-slate-400 mt-6">
        © {{ date('Y') }} CampusFix — Integrative Programming &amp; Technologies
    </p>
</div>

</body>
</html>