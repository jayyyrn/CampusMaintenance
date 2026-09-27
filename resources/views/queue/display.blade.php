<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Maintenance Queue — CampusFix</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html, body { background: #0f172a; }
        body { font-family: 'Inter', system-ui, sans-serif; color: #e2e8f0; }
        [x-cloak] { display: none !important; }
        @keyframes pulse-dot { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }
        .pulse-dot { animation: pulse-dot 1.6s ease-in-out infinite; }
        .scrollbar-thin::-webkit-scrollbar { width: 8px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.3); border-radius: 4px; }
    </style>
</head>
<body class="min-h-screen" style="background:#0f172a; color:#e2e8f0;">

<div x-data="publicQueue()" x-init="start()" class="min-h-screen flex flex-col">

    {{-- SIDEBAR --}}
    <div x-show="sidebarOpen" x-cloak x-transition.opacity
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/70 z-40"></div>

    <aside class="fixed top-0 left-0 h-full w-80 z-50 transform transition-transform duration-300 ease-out shadow-2xl"
           style="background:#1e293b; border-right:1px solid #334155;"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        <div class="flex flex-col h-full">
            <div class="p-6 flex items-center justify-between" style="border-bottom:1px solid #334155;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#6366f1,#14b8a6);">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="font-bold text-lg" style="color:#f1f5f9;">CampusFix</div>
                        <div class="text-xs" style="color:#64748b;">Campus Maintenance</div>
                    </div>
                </div>
                <button @click="sidebarOpen = false"
                        class="w-9 h-9 rounded-lg flex items-center justify-center transition"
                        style="color:#94a3b8;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto scrollbar-thin p-6 space-y-6">
                @auth
                    <div class="rounded-xl p-4" style="background:#0f172a; border:1px solid #334155;">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm text-white"
                                 style="background:linear-gradient(135deg,#6366f1,#14b8a6);">
                                {{ auth()->user()->initials }}
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold truncate" style="color:#f1f5f9;">{{ auth()->user()->full_name }}</div>
                                <div class="text-xs capitalize" style="color:#94a3b8;">{{ str_replace('_',' ', auth()->user()->role) }}</div>
                            </div>
                        </div>
                        <a href="{{ route('dashboard') }}"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-semibold transition text-sm text-white"
                           style="background:#4f46e5;">
                            Go to Dashboard →
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="w-full text-sm py-1 transition" style="color:#94a3b8;">
                                Log out
                            </button>
                        </form>
                    </div>
                @else
                    <div>
                        <div class="text-xs font-bold uppercase tracking-wider mb-3" style="color:#64748b;">
                            Sign in to submit a request
                        </div>
                        <a href="{{ route('login') }}"
                           class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-bold transition text-white"
                           style="background:#4f46e5; box-shadow:0 10px 25px rgba(79,70,229,0.3);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Log In
                        </a>
                        <p class="text-xs mt-3 leading-relaxed" style="color:#64748b;">
                            Browse the queue without an account. Log in to submit a new request.
                        </p>
                    </div>
                @endauth

                <div>
                    <div class="text-xs font-bold uppercase tracking-wider mb-3" style="color:#64748b;">Quick Links</div>
                    <nav class="space-y-1">
                        <a href="{{ url('/') }}"
                           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold"
                           style="background:#334155; color:#f1f5f9;">
                            Public Queue
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition"
                               style="color:#94a3b8;">
                                Dashboard
                            </a>
                        @endauth
                    </nav>
                </div>

                <div class="rounded-xl p-4 space-y-3 text-sm" style="background:#0f172a; border:1px solid #334155;">
                    <div class="flex items-center justify-between">
                        <span style="color:#94a3b8;">Active requests</span>
                        <span class="font-bold text-lg tabular-nums" style="color:#f1f5f9;" x-text="total"></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:#94a3b8;">Updated</span>
                        <span class="text-xs" style="color:#cbd5e1;" x-text="updatedAgo"></span>
                    </div>
                    <div class="flex items-center gap-2 pt-3" style="border-top:1px solid #334155;">
                        <span class="w-2 h-2 rounded-full pulse-dot" style="background:#34d399;"></span>
                        <span class="text-xs font-semibold" style="color:#34d399;">LIVE</span>
                    </div>
                </div>
            </div>

            <div class="p-4 text-center text-xs" style="border-top:1px solid #334155; color:#475569;">
                CampusFix &mdash; IPT
            </div>
        </div>
    </aside>

    {{-- MENU BUTTON --}}
    <button x-show="!sidebarOpen" x-cloak
            @click="sidebarOpen = true"
            class="fixed top-5 left-5 z-30 w-12 h-12 rounded-xl flex items-center justify-center transition"
            style="background:#1e293b; border:1px solid #334155; box-shadow:0 10px 25px rgba(0,0,0,0.3);">
        <svg class="w-6 h-6" style="color:#f1f5f9;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>

    {{-- HEADER --}}
    <header style="border-bottom:1px solid #334155; background:#1e293b;">
        <div class="px-8 py-8 flex items-center justify-between flex-wrap gap-6">
            <div class="flex items-center gap-5 pl-16">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center"
                     style="background:linear-gradient(135deg,#6366f1,#14b8a6); box-shadow:0 10px 25px rgba(99,102,241,0.3);">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-4xl font-black tracking-tight" style="color:#f1f5f9;">Public Maintenance Queue</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full pulse-dot" style="background:#34d399;"></span>
                            <span class="text-xs font-bold uppercase tracking-wider" style="color:#34d399;">Live</span>
                        </span>
                        <span style="color:#475569;">&middot;</span>
                        <span class="text-sm" style="color:#94a3b8;">
                            <span class="font-bold tabular-nums" style="color:#f1f5f9;" x-text="total"></span> active requests
                        </span>
                        <span style="color:#475569;">&middot;</span>
                        <span class="text-sm" style="color:#94a3b8;">Updated <span style="color:#cbd5e1;" x-text="updatedAgo"></span></span>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <div class="text-4xl font-black tabular-nums" style="color:#f1f5f9;" x-text="clock"></div>
                <div class="text-sm mt-1" style="color:#94a3b8;" x-text="date"></div>
            </div>
        </div>
    </header>

    {{-- LOADING --}}
    <div x-show="loading && categories.length === 0" x-cloak class="flex-1 flex items-center justify-center py-32">
        <div class="text-center">
            <div class="inline-block w-14 h-14 rounded-full animate-spin"
                 style="border:4px solid #1e293b; border-top-color:#6366f1;"></div>
            <p class="mt-6 text-lg" style="color:#64748b;">Loading queue…</p>
        </div>
    </div>

    {{-- EMPTY --}}
    <div x-show="!loading && categories.length === 0" x-cloak class="flex-1 flex items-center justify-center py-32">
        <div class="text-center">
            <div class="text-7xl mb-6">✨</div>
            <p class="text-3xl font-bold" style="color:#cbd5e1;">No active requests</p>
            <p class="mt-3" style="color:#64748b;">Everything is up to date.</p>
        </div>
    </div>

    {{-- GRID --}}
    <main x-show="categories.length > 0" class="flex-1 px-8 py-10">
        <div class="max-w-6xl mx-auto space-y-12">

            <template x-for="cat in categories" :key="cat.category">
                <section>

                    {{-- Category header --}}
                    <div class="flex items-center gap-4 mb-6">
                        <div class="w-3 h-3 rounded-full"
                             :style="`background:${categoryColor(cat.category)}`"></div>
                        <h2 class="text-3xl font-black tracking-tight capitalize" style="color:#f1f5f9;" x-text="cat.category"></h2>
                        <span class="text-sm font-bold tabular-nums" style="color:#64748b;" x-text="cat.count + ' active'"></span>
                        <div class="flex-1 h-px" style="background:#334155;"></div>
                    </div>

                    {{-- Cards --}}
                    <div class="space-y-4">
                        <template x-for="item in cat.items" :key="item.request_code">
                            <div @click="openDetail(item)"
                                 class="group rounded-2xl p-6 cursor-pointer transition-all duration-150"
                                 style="background:#1e293b; border:1px solid #334155;"
                                 onmouseover="this.style.borderColor='#475569'; this.style.transform='translateY(-2px)';"
                                 onmouseout="this.style.borderColor='#334155'; this.style.transform='translateY(0)';">

                                <div class="flex items-center gap-6">

                                    {{-- Queue number --}}
                                    <div class="shrink-0 w-20 h-20 rounded-2xl flex items-center justify-center text-4xl font-black tabular-nums"
                                         :style="`background:${priorityBg(item.priority)}; color:${priorityFg(item.priority)}; border:1px solid ${priorityBorder(item.priority)};`">
                                        <span x-text="item.queue_position || '—'"></span>
                                    </div>

                                    {{-- Body --}}
                                    <div class="flex-1 min-w-0">

                                        {{-- Row 1 --}}
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="font-mono text-xs" style="color:#64748b;" x-text="item.request_code"></span>
                                            <span style="color:#334155;">&middot;</span>

                                            <span class="text-xs font-black uppercase tracking-widest px-2.5 py-1 rounded"
                                                  :style="`background:${priorityBg(item.priority)}; color:${priorityFg(item.priority)};`"
                                                  x-text="item.priority"></span>

                                            <span class="text-xs font-black uppercase tracking-widest px-2.5 py-1 rounded"
                                                  :style="`background:${statusBg(item.status)}; color:${statusFg(item.status)};`"
                                                  x-text="item.status_label"></span>
                                        </div>

                                        {{-- Title --}}
                                        <h3 class="text-2xl font-bold leading-tight line-clamp-1" style="color:#f1f5f9;"
                                            x-text="item.title"></h3>

                                        {{-- Row 2 --}}
                                        <div class="mt-3 flex items-center gap-6 flex-wrap text-sm" style="color:#94a3b8;">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" style="color:#475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                                </svg>
                                                <span x-text="item.department"></span>
                                            </span>
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" style="color:#475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                <span x-text="item.location"></span>
                                            </span>
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" style="color:#475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                                <span x-text="item.teacher"></span>
                                            </span>
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4" style="color:#475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span x-text="formatDiffForHumans(item.date_reported)"></span>
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Arrow --}}
                                    <svg class="shrink-0 w-6 h-6 transition group-hover:translate-x-1"
                                         style="color:#475569;"
                                         onmouseover="this.style.color='#f1f5f9';"
                                         onmouseout="this.style.color='#475569';"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            </div>
                        </template>
                    </div>
                </section>
            </template>
        </div>
    </main>

    {{-- MODAL --}}
    <div x-show="detailOpen" x-cloak
         class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4"
         @keydown.escape.window="closeDetail()">

        <div class="rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto scrollbar-thin"
             style="background:#1e293b; border:1px solid #334155; box-shadow:0 25px 50px rgba(0,0,0,0.5);"
             @click.outside="closeDetail()">

            <div class="sticky top-0 px-6 py-5 flex items-start justify-between z-10"
                 style="background:#1e293b; border-bottom:1px solid #334155;">
                <div class="min-w-0">
                    <div class="font-mono text-xs" style="color:#64748b;" x-text="detail?.request_code || '—'"></div>
                    <h2 class="text-2xl font-bold mt-1 leading-tight" style="color:#f1f5f9;" x-text="detail?.title || 'Loading…'"></h2>
                </div>
                <button @click="closeDetail()"
                        class="shrink-0 ml-4 w-10 h-10 rounded-lg flex items-center justify-center transition"
                        style="color:#94a3b8;">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div x-show="detailLoading" class="p-16 text-center">
                <div class="inline-block w-10 h-10 rounded-full animate-spin"
                     style="border:4px solid #334155; border-top-color:#6366f1;"></div>
                <p class="mt-4" style="color:#64748b;">Loading…</p>
            </div>

            <div x-show="!detailLoading && detail" class="p-6 space-y-6">

                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-black uppercase tracking-widest px-3 py-1.5 rounded"
                          :style="`background:${priorityBg(detail?.priority)}; color:${priorityFg(detail?.priority)};`"
                          x-text="detail?.priority + ' priority'"></span>

                    <span class="text-xs font-black uppercase tracking-widest px-3 py-1.5 rounded"
                          :style="`background:${statusBg(detail?.status)}; color:${statusFg(detail?.status)};`"
                          x-text="detail?.status_label"></span>
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-widest mb-3" style="color:#64748b;">Problem</h3>
                    <p class="whitespace-pre-line leading-relaxed" style="color:#cbd5e1;" x-text="detail?.description"></p>
                </div>

                <div class="grid grid-cols-2 gap-x-8 gap-y-5 pt-4" style="border-top:1px solid #334155;">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest mb-1.5" style="color:#64748b;">Department</div>
                        <div class="font-semibold" style="color:#f1f5f9;" x-text="detail?.department"></div>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest mb-1.5" style="color:#64748b;">Room / Location</div>
                        <div class="font-semibold" style="color:#f1f5f9;" x-text="detail?.location"></div>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest mb-1.5" style="color:#64748b;">Reported by</div>
                        <div class="font-semibold" style="color:#f1f5f9;" x-text="detail?.teacher"></div>
                    </div>
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest mb-1.5" style="color:#64748b;">Reported</div>
                        <div class="font-semibold" style="color:#f1f5f9;" x-text="formatDiffForHumans(detail?.date_reported)"></div>
                    </div>
                </div>

                <div class="pt-6 text-center" style="border-top:1px solid #334155;">
                    <p class="text-xs" style="color:#475569;">Read-only view</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="px-8 py-6 text-center" style="border-top:1px solid #334155;">
        <p class="text-xs" style="color:#475569;">CampusFix &mdash; Integrative Programming &amp; Technologies</p>
    </footer>
</div>

<script>
function publicQueue() {
    return {
        sidebarOpen: false,
        clock: '',
        date: '',
        now: Date.now(),
        total: 0,
        categories: [],
        loading: true,
        lastUpdated: null,
        tickInterval: null,
        pollInterval: null,
        detailOpen: false,
        detailLoading: false,
        detail: null,

        start() {
            this.tickInterval = setInterval(() => this.tick(), 1000);
            this.tick();
            this.fetchData();
            this.pollInterval = setInterval(() => this.fetchData(), 15000);
        },

        tick() {
            const d = new Date();
            this.clock = d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            this.date = d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
            this.now = d.getTime();
        },

        categoryColor(cat) {
            const map = {
                aircon:      '#22d3ee',
                electrical:  '#facc15',
                carpentry:   '#fb923c',
                fabrication: '#c084fc',
                plumbing:    '#60a5fa',
                general:     '#94a3b8',
            };
            return map[cat] || map.general;
        },

        priorityBg(p) {
            return ({
                urgent: 'rgba(244,63,94,0.15)',
                high:   'rgba(249,115,22,0.15)',
                medium: 'rgba(59,130,246,0.15)',
                low:    'rgba(71,85,105,0.4)',
            })[p] || 'rgba(71,85,105,0.4)';
        },
        priorityFg(p) {
            return ({
                urgent: '#fda4af',
                high:   '#fdba74',
                medium: '#93c5fd',
                low:    '#cbd5e1',
            })[p] || '#cbd5e1';
        },
        priorityBorder(p) {
            return ({
                urgent: 'rgba(244,63,94,0.4)',
                high:   'rgba(249,115,22,0.4)',
                medium: 'rgba(59,130,246,0.4)',
                low:    'rgba(71,85,105,0.6)',
            })[p] || 'rgba(71,85,105,0.6)';
        },

        statusBg(s) {
            return ({
                pending:          'rgba(51,65,85,0.8)',
                review:           'rgba(59,130,246,0.15)',
                assigned:         'rgba(168,85,247,0.15)',
                in_progress:      'rgba(249,115,22,0.15)',
                for_verification: 'rgba(234,179,8,0.15)',
            })[s] || 'rgba(51,65,85,0.8)';
        },
        statusFg(s) {
            return ({
                pending:          '#cbd5e1',
                review:           '#93c5fd',
                assigned:         '#d8b4fe',
                in_progress:      '#fdba74',
                for_verification: '#fde047',
            })[s] || '#cbd5e1';
        },

        async fetchData() {
            try {
                const res = await fetch('{{ route('queue.display.data') }}', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (!data.ok) return;
                this.total = data.total;
                this.categories = data.categories;
                this.lastUpdated = new Date(data.updated);
                this.loading = false;
                if (this.detailOpen && this.detail?.request_id) this.refreshDetail(this.detail.request_id);
            } catch (e) {}
        },

        async openDetail(item) {
            this.detailOpen = true;
            this.detailLoading = true;
            this.detail = { ...item };
            if (!item.request_id) { this.detailLoading = false; return; }
            try {
                const res = await fetch('{{ url('/queue/display') }}/' + item.request_id, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (!data.ok) throw new Error('Failed to load');
                this.detail = data.request;
            } catch (e) {}
            this.detailLoading = false;
        },

        async refreshDetail(id) {
            try {
                const res = await fetch('{{ url('/queue/display') }}/' + id, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.ok) this.detail = data.request;
            } catch (e) {}
        },

        closeDetail() {
            this.detailOpen = false;
            this.detail = null;
            this.detailLoading = false;
        },

        formatDate(iso) {
            if (!iso) return '—';
            const d = new Date(iso);
            return d.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });
        },

        formatDiffForHumans(iso) {
            if (!iso) return '—';
            const diff = Math.floor((this.now - new Date(iso).getTime()) / 1000);

            if (diff < 5)     return 'just now';
            if (diff < 60)    return diff + ' seconds ago';

            const mins = Math.floor(diff / 60);
            if (mins < 60)    return mins + (mins === 1 ? ' minute ago' : ' minutes ago');

            const hours = Math.floor(mins / 60);
            if (hours < 24)   return hours + (hours === 1 ? ' hour ago' : ' hours ago');

            const days = Math.floor(hours / 24);
            if (days < 30)    return days + (days === 1 ? ' day ago' : ' days ago');

            const months = Math.floor(days / 30);
            if (months < 12)  return months + (months === 1 ? ' month ago' : ' months ago');

            const years = Math.floor(months / 12);
            return years + (years === 1 ? ' year ago' : ' years ago');
        },

        get updatedAgo() {
            if (!this.lastUpdated) return '—';
            const secs = Math.floor((this.now - this.lastUpdated.getTime()) / 1000);
            if (secs < 5) return 'just now';
            if (secs < 60) return secs + 's ago';
            return Math.floor(secs / 60) + 'm ago';
        },
    }
}
</script>

</body>
</html>