<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusFix — Login</title>
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
                          d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">CampusFix</h1>
            <p class="text-slate-500 text-sm mt-1">Campus Maintenance &amp; Inventory</p>
        </div>

        @if($errors->any())
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-sm p-3 rounded-lg">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label" for="username">Username</label>
                <input id="username" type="text" name="username" value="{{ old('username') }}"
                       required autofocus autocomplete="username"
                       class="input" placeholder="Enter your username">
            </div>
            <div>
                <label class="label" for="password">Password</label>
                <input id="password" type="password" name="password" required
                       autocomplete="current-password"
                       class="input" placeholder="Enter your password">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600 select-none">
                <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                Remember me
            </label>

            <button type="submit" class="btn-primary w-full py-3 text-base">
                Sign in
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-200 text-center">
            <p class="text-xs font-semibold text-slate-500 mb-2 uppercase tracking-wide">Demo Accounts</p>
            <p class="text-xs text-slate-400 leading-relaxed">
                <span class="font-mono text-slate-500">admin</span> ·
                <span class="font-mono text-slate-500">teacher1</span> ·
                <span class="font-mono text-slate-500">tech1</span> ·
                <span class="font-mono text-slate-500">lead1</span> ·
                <span class="font-mono text-slate-500">inventory1</span>
            </p>
            <p class="text-xs text-slate-400 mt-1">Password: <span class="font-mono">password123</span></p>
        </div>
    </div>

    <p class="text-center text-xs text-slate-400 mt-6">
        © {{ date('Y') }} CampusFix — Integrative Programming &amp; Technologies
    </p>
</div>

</body>
</html>