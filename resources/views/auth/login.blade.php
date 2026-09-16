<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusFix — Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-orange-500 to-orange-700 p-4">
    <div class="bg-white rounded-2xl shadow-2xl p-10 w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-20 h-20 mx-auto bg-orange-100 rounded-full flex items-center justify-center mb-4">
                <span class="text-4xl">🔧</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Campus Maintenance</h1>
            <p class="text-gray-500 text-sm mt-1">Sign in to continue</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-100 text-red-700 p-3 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" name="remember" class="rounded"> Remember me
            </label>
            <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-lg transition shadow-lg shadow-orange-500/30">
                LOG IN
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-400">
            <p class="font-semibold mb-2">Demo accounts (password: password123)</p>
            <p>admin • teacher1 • tech1 • lead1 • inventory1</p>
        </div>
    </div>
</body>
</html>