@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    @php $u = auth()->user(); @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Welcome, {{ $u->full_name }} 👋</h1>
        <p class="text-gray-500">{{ now()->format('l, F d, Y') }}</p>
    </div>

    {{-- TEACHER DASHBOARD --}}
    @if($u->isTeacher())
        <a href="{{ route('requests.create') }}" class="block bg-orange-500 hover:bg-orange-600 text-white text-center font-bold py-4 rounded-xl shadow-lg shadow-orange-500/30 mb-6 transition">
            + SUBMIT REQUEST
        </a>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-gray-900">{{ $data['stats']['total'] }}</div>
                <div class="text-sm text-gray-500 mt-1">My Requests</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-orange-500">{{ $data['stats']['pending'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Pending</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-green-500">{{ $data['stats']['completed'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Completed</div>
            </div>
        </div>

        <h2 class="text-sm font-bold text-gray-500 uppercase mb-3">Recent Requests</h2>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y">
            @forelse($data['recent'] as $r)
                <a href="{{ route('requests.show', $r->request_id) }}" class="flex items-center justify-between p-4 hover:bg-gray-50">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $r->title }}</div>
                        <div class="text-xs text-gray-500">{{ $r->request_code }} • {{ $r->location ?? 'N/A' }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $r->statusColor() }}">{{ $r->statusLabel() }}</span>
                </a>
            @empty
                <div class="p-6 text-center text-gray-400">No requests yet.</div>
            @endforelse
        </div>
    @endif

    {{-- TECHNICIAN DASHBOARD --}}
    @if($u->isTechnician())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-gray-900">{{ $data['stats']['pending'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Pending</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-orange-500">{{ $data['stats']['in_progress'] }}</div>
                <div class="text-sm text-gray-500 mt-1">In Progress</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-yellow-500">{{ $data['stats']['for_review'] }}</div>
                <div class="text-sm text-gray-500 mt-1">For Review</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-green-500">{{ $data['stats']['completed'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Completed</div>
            </div>
        </div>

        <h2 class="text-sm font-bold text-gray-500 uppercase mb-3">My Tasks</h2>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y">
            @forelse($data['tasks'] as $t)
                <a href="{{ route('tasks.show', $t->assignment_id) }}" class="flex items-center justify-between p-4 hover:bg-gray-50">
                    <div>
                        <div class="font-semibold text-gray-900">{{ $t->request->title }}</div>
                        <div class="text-xs text-gray-500">{{ $t->request->request_code }} • {{ $t->request->location }}</div>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $t->statusColor() }}">{{ ucwords(str_replace('_',' ',$t->status)) }}</span>
                </a>
            @empty
                <div class="p-6 text-center text-gray-400">No tasks assigned.</div>
            @endforelse
        </div>
    @endif

    {{-- INVENTORY DASHBOARD --}}
    @if($u->isInventoryOfficer())
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-gray-900">{{ $data['stats']['total_items'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Total Items</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-red-500">{{ $data['stats']['low_stock'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Low Stock</div>
            </div>
            <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                <div class="text-3xl font-bold text-orange-500">{{ $data['stats']['pending_requests'] }}</div>
                <div class="text-sm text-gray-500 mt-1">Pending Requests</div>
            </div>
        </div>

        <h2 class="text-sm font-bold text-gray-500 uppercase mb-3">Low Stock Alerts</h2>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y mb-6">
            @forelse($data['low_stock'] as $i)
                <div class="flex items-center justify-between p-4 bg-orange-50">
                    <div class="font-semibold text-orange-700">{{ $i->item_name }}</div>
                    <div class="text-sm font-bold text-orange-700">{{ $i->qty_on_hand }} {{ $i->unit }} left</div>
                </div>
            @empty
                <div class="p-6 text-center text-gray-400">All items are well stocked.</div>
            @endforelse
        </div>
    @endif

 {{-- ADMIN / COORDINATOR --}}
@if($u->isAdmin() || $u->role === 'coordinator')
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-gray-900">{{ $data['stats']['total_requests'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 mt-1">Total Requests</div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-orange-500">{{ $data['stats']['active_requests'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 mt-1">Active</div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-green-500">{{ $data['stats']['completed'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 mt-1">Completed</div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
            <div class="text-3xl font-bold text-red-500">{{ $data['stats']['low_stock'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 mt-1">Low Stock</div>
        </div>
    </div>

    <h2 class="text-sm font-bold text-gray-500 uppercase mb-3">Recent Requests</h2>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y">
        @forelse($data['recent'] ?? [] as $r)
            <a href="{{ route('requests.show', $r->request_id) }}" class="flex items-center justify-between p-4 hover:bg-gray-50">
                <div>
                    <div class="font-semibold text-gray-900">{{ $r->title }}</div>
                    <div class="text-xs text-gray-500">
                        {{ $r->request_code }} • {{ $r->teacher->full_name ?? '' }} • {{ $r->department->dept_name ?? '' }}
                    </div>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $r->statusColor() }}">{{ $r->statusLabel() }}</span>
            </a>
        @empty
            <div class="p-6 text-center text-gray-400">No requests yet.</div>
        @endforelse
    </div>
@endif
@endsection