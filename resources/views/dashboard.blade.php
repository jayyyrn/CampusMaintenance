@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    @php $u = auth()->user(); @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Welcome back, {{ $u->full_name }} 👋</h1>
        <p class="text-slate-500 text-sm mt-1">{{ now()->format('l, F d, Y') }}</p>
    </div>

    {{-- ═══════════ TEACHER ═══════════ --}}
    @if($u->isTeacher())
        <a href="{{ route('requests.create') }}" class="btn-primary w-full py-4 text-base mb-6 justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Submit New Request
        </a>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <x-stat-card label="My Requests" value="{{ $data['stats']['total'] ?? 0 }}" color="slate" />
            <x-stat-card label="Pending"     value="{{ $data['stats']['pending'] ?? 0 }}" color="amber" />
            <x-stat-card label="Completed"   value="{{ $data['stats']['completed'] ?? 0 }}" color="emerald" />
        </div>

        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Recent Requests</h2>
        <div class="card divide-y divide-slate-100">
            @forelse($data['recent'] ?? [] as $r)
                <a href="{{ route('requests.show', $r->request_id) }}"
                   class="flex items-center justify-between p-4 hover:bg-slate-50 transition">
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900 truncate">{{ $r->title }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            <span class="font-mono">{{ $r->request_code }}</span>
                            @if($r->location) · {{ $r->location }} @endif
                        </div>
                    </div>
                    <span class="badge {{ $r->statusColor() }} shrink-0 ml-4">{{ $r->statusLabel() }}</span>
                </a>
            @empty
                <div class="p-8 text-center text-slate-400 text-sm">No requests yet.</div>
            @endforelse
        </div>
    @endif

    {{-- ═══════════ TECHNICIAN / LEAD ═══════════ --}}
    @if($u->isTechnician())
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card label="Pending"     value="{{ $data['stats']['pending'] ?? 0 }}"     color="slate" />
            <x-stat-card label="In Progress" value="{{ $data['stats']['in_progress'] ?? 0 }}" color="amber" />
            <x-stat-card label="For Review"  value="{{ $data['stats']['for_review'] ?? 0 }}"  color="indigo" />
            <x-stat-card label="Completed"   value="{{ $data['stats']['completed'] ?? 0 }}"   color="emerald" />
        </div>

        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">My Active Tasks</h2>
        <div class="card divide-y divide-slate-100">
            @forelse($data['tasks'] ?? [] as $t)
                <a href="{{ route('tasks.show', $t->assignment_id) }}"
                   class="flex items-center justify-between p-4 hover:bg-slate-50 transition">
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900 truncate">{{ $t->request->title }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            <span class="font-mono">{{ $t->request->request_code }}</span>
                            @if($t->request->location) · {{ $t->request->location }} @endif
                        </div>
                    </div>
                    <span class="badge {{ $t->statusColor() }} shrink-0 ml-4">
                        {{ ucwords(str_replace('_',' ',$t->status)) }}
                    </span>
                </a>
            @empty
                <div class="p-8 text-center text-slate-400 text-sm">No tasks assigned.</div>
            @endforelse
        </div>
    @endif

    {{-- ═══════════ INVENTORY OFFICER ═══════════ --}}
    @if($u->isInventoryOfficer())
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <x-stat-card label="Total Items"        value="{{ $data['stats']['total_items'] ?? 0 }}"      color="slate" />
            <x-stat-card label="Low Stock"          value="{{ $data['stats']['low_stock'] ?? 0 }}"        color="rose" />
            <x-stat-card label="Pending Requests"   value="{{ $data['stats']['pending_requests'] ?? 0 }}" color="amber" />
        </div>

        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Low Stock Alerts</h2>
        <div class="card divide-y divide-slate-100 mb-6">
            @forelse($data['low_stock'] ?? [] as $i)
                <div class="flex items-center justify-between p-4 bg-amber-50">
                    <div class="font-semibold text-amber-900">{{ $i->item_name }}</div>
                    <div class="text-sm font-bold text-amber-700">{{ $i->qty_on_hand }} {{ $i->unit }} left</div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-sm">All items are well-stocked.</div>
            @endforelse
        </div>
    @endif

    {{-- ═══════════ ADMIN / COORDINATOR ═══════════ --}}
    @if($u->isAdmin() || $u->isCoordinator())
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card label="Total Requests" value="{{ $data['stats']['total_requests'] ?? 0 }}"  color="slate" />
            <x-stat-card label="Active"         value="{{ $data['stats']['active_requests'] ?? 0 }}" color="indigo" />
            <x-stat-card label="Completed"      value="{{ $data['stats']['completed'] ?? 0 }}"       color="emerald" />
            <x-stat-card label="Low Stock"      value="{{ $data['stats']['low_stock'] ?? 0 }}"       color="rose" />
        </div>

        <h2 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Recent Requests</h2>
        <div class="card divide-y divide-slate-100">
            @forelse($data['recent'] ?? [] as $r)
                <a href="{{ route('requests.show', $r->request_id) }}"
                   class="flex items-center justify-between p-4 hover:bg-slate-50 transition">
                    <div class="min-w-0">
                        <div class="font-semibold text-slate-900 truncate">{{ $r->title }}</div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            <span class="font-mono">{{ $r->request_code }}</span>
                            @if($r->teacher) · {{ $r->teacher->full_name }} @endif
                            @if($r->department) · {{ $r->department->dept_name }} @endif
                        </div>
                    </div>
                    <span class="badge {{ $r->statusColor() }} shrink-0 ml-4">{{ $r->statusLabel() }}</span>
                </a>
            @empty
                <div class="p-8 text-center text-slate-400 text-sm">No requests yet.</div>
            @endforelse
        </div>
    @endif
@endsection