<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CampusFix') — Campus Maintenance</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak]{display:none!important;}</style>
</head>
<body class="bg-slate-50" x-data="{ sidebarOpen: false }">

    {{-- ═══════════ HEADER — sticky at top, spans FULL WIDTH ═══════════ --}}
    <header class="sticky top-0 z-50 h-16 bg-slate-800 text-white shadow-md">
        <div class="h-full px-4 sm:px-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden w-10 h-10 -ml-1 flex items-center justify-center rounded-lg hover:bg-slate-700"
                        aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 font-bold text-lg">
                    <span class="bg-gradient-to-br from-brand-500 to-accent-500 rounded-lg w-9 h-9 flex items-center justify-center shadow">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                        </svg>
                    </span>
                    <span class="hidden sm:inline">CampusFix</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                @php $unread = auth()->user()->unreadNotificationsCount(); @endphp

                <a href="{{ route('notifications.index') }}"
                   class="relative flex items-center gap-2 h-11 px-3 rounded-lg hover:bg-slate-700">
                    <span class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unread > 0)
                            <span class="absolute -top-1 -right-1 bg-rose-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 ring-2 ring-slate-800">
                                {{ $unread > 99 ? '99+' : $unread }}
                            </span>
                        @endif
                    </span>
                    <span class="hidden md:inline text-sm">Notifications</span>
                </a>

                <div class="hidden sm:flex items-center gap-3 pl-3 border-l border-slate-700">
                    <div class="text-right leading-tight">
                        <div class="text-sm font-semibold">{{ auth()->user()->full_name }}</div>
                        <div class="text-xs text-slate-400 capitalize">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-500 to-accent-500 flex items-center justify-center font-bold text-sm">
                        {{ auth()->user()->initials }}
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="h-11 px-3 text-sm text-slate-300 hover:text-white hover:bg-rose-600 rounded-lg">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- ═══════════ BODY — flex row: sidebar + main ═══════════ --}}
    <div class="flex">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
             class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

        {{-- ═══════════ SIDEBAR — sticky, sits right below header ═══════════ --}}
        <aside class="w-64 shrink-0 bg-white border-r border-slate-200
                      sticky top-16 h-[calc(100vh-4rem)] overflow-y-auto
                      z-40 transition-transform duration-200
                      -translate-x-full lg:translate-x-0
                      fixed lg:sticky left-0"
               :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
            <nav class="p-4 space-y-1">
                @php $u = auth()->user(); @endphp
                <x-nav-link route="dashboard" icon="home">Dashboard</x-nav-link>
                <x-nav-link route="requests.index" icon="clipboard">Requests</x-nav-link>

                @if($u->isTechnician())
                    <x-nav-link route="tasks.index" icon="wrench">My Tasks</x-nav-link>
                @endif

                <x-nav-link route="queue.index" icon="clock">Public Queue</x-nav-link>
                <x-nav-link route="inventory.index" icon="cube">Inventory</x-nav-link>

                @if($u->isTechnician() || $u->isInventoryOfficer())
                    <x-nav-link route="inventory.material_requests" icon="inbox">Material Requests</x-nav-link>
                @endif

                <x-nav-link route="assistant.index" icon="sparkles">AI Assistant</x-nav-link>

                @if($u->isAdmin())
                    <div class="pt-4 pb-2 px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Admin</div>
                    <x-nav-link route="admin.users" icon="users">Users</x-nav-link>
                    <x-nav-link route="admin.audit_logs" icon="document">Audit Logs</x-nav-link>
                @endif
            </nav>
        </aside>

        {{-- ═══════════ MAIN CONTENT — flex-1, sits to the right of sidebar ═══════════ --}}
        <main class="flex-1 min-w-0 p-4 sm:p-6">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl text-sm font-medium">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-xl text-sm font-medium">
                    ⚠️ {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

</body>
</html>