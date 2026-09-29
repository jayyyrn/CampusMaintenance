<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusFix — Forgot Password</title>
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
                          d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Forgot your password?</h1>
            <p class="text-slate-500 text-sm mt-2">
                Enter your <strong>username</strong>. We'll send a 6-digit code to the email registered to your account.
            </p>
        </div>

        @if (session('error'))
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-sm p-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-sm p-3 rounded-lg">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label class="label" for="username">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}"
                       required autofocus autocomplete="username"
                       class="input" placeholder="Enter your username">
                <p class="text-xs text-slate-400 mt-2">
                    This is the same username you use to log in.
                </p>
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base">
                Send Verification Code
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