<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusFix — Verify Code</title>
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
                          d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Check your email</h1>
            <p class="text-slate-500 text-sm mt-2">
                If the account exists, we sent a 6-digit code to<br>
                <span class="font-semibold text-slate-700">{{ $maskedEmail }}</span>
            </p>
            <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-4 inline-block">
                💡 Check your <strong>Spam</strong> or <strong>Junk</strong> folder if you don't see it.
            </p>
        </div>

        @if (session('status'))
            <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm p-3 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

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

        <form method="POST" action="{{ route('password.verify') }}" class="space-y-4">
            @csrf

            <div>
                <label class="label" for="code">Verification Code</label>
                <input id="code" type="text" name="code"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       value="{{ old('code') }}"
                       required autofocus autocomplete="one-time-code"
                       class="input text-center text-2xl font-mono tracking-widest"
                       placeholder="000000"
                       oninput="this.value = this.value.replace(/\D/g, '');">
                <p class="text-xs text-slate-400 mt-2 text-center">Code expires in 15 minutes.</p>
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base">
                Verify Code
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-200">
            <form method="POST" action="{{ route('password.resend') }}" class="text-center">
                @csrf
                <button type="submit" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
                    Didn't get the code? Resend
                </button>
            </form>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('password.request') }}" class="text-sm text-slate-500 hover:text-slate-800 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Use a different username
            </a>
        </div>
    </div>

    <p class="text-center text-xs text-slate-400 mt-6">
        © {{ date('Y') }} CampusFix — Integrative Programming &amp; Technologies
    </p>
</div>

</body>
</html>