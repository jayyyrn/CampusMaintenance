<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CampusFix') — Campus Maintenance</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: false }">

<!-- Top Bar -->
<nav class="bg-gray-900 text-white shadow-lg">
    <div class="px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-white text-2xl">☰</button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-bold text-lg">
                <span class="bg-orange-500 rounded-lg w-8 h-8 flex items-center justify-center">🔧</span>
                CampusFix
            </a>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('notifications.index') }}" class="relative hover:text-orange-400 transition">
                🔔
                @php $unread = auth()->user()->notifications()->where('is_read', false)->count(); @endphp
                @if($unread > 0)
                    <span class="absolute -top-1 -right-2 bg-red-500 text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">{{ $unread }}</span>
                @endif
            </a>
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-semibold">{{ auth()->user()->full_name }}</div>
                    <div class="text-xs text-gray-400 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                </div>
                <div class="w-9 h-9 rounded-full bg-orange-500 flex items-center justify-center font-bold">
                    {{ strtoupper(substr(auth()->user()->full_name, 0, 1)) }}
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm text-gray-300 hover:text-red-400 transition">Logout</button>
            </form>
        </div>
    </div>
</nav>

<div class="flex">
    <!-- Sidebar -->
    <aside class="bg-white w-64 min-h-screen border-r border-gray-200 p-4 lg:block"
           :class="sidebarOpen ? 'block fixed z-40' : 'hidden'">
        @php $u = auth()->user(); @endphp
        <nav class="space-y-1">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                <span>🏠</span> Dashboard
            </a>
            <a href="{{ route('requests.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('requests.*') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                <span>📋</span> Requests
            </a>

            @if($u->isTechnician())
                <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('tasks.*') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span>🛠️</span> My Tasks
                </a>
            @endif

            <a href="{{ route('queue.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('queue.*') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                <span>⏱️</span> Public Queue
            </a>

            <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('inventory.index') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                <span>📦</span> Inventory
            </a>

            @if($u->isTechnician() || $u->isInventoryOfficer())
                <a href="{{ route('inventory.material_requests') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('inventory.material_requests') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span>📥</span> Material Requests
                </a>
            @endif

            <a href="{{ route('assistant.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('assistant.*') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                <span>🤖</span> AI Assistant
            </a>

            @if($u->isAdmin())
                <div class="pt-4 pb-2 px-4 text-xs font-bold text-gray-400 uppercase">Admin</div>
                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.users') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span>👥</span> Users
                </a>
                <a href="{{ route('admin.audit_logs') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg {{ request()->routeIs('admin.audit_logs') ? 'bg-orange-50 text-orange-600 font-semibold' : 'text-gray-700 hover:bg-gray-100' }}">
                    <span>📜</span> Audit Logs
                </a>
            @endif
        </nav>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-6">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">⚠️ {{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</div>

</body>
</html>