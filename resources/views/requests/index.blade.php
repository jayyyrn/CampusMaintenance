@extends('layouts.app')
@section('title', 'Requests')
@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Maintenance Requests</h1>
            <p class="text-slate-500 text-sm mt-1">Track, filter, and manage all maintenance concerns</p>
        </div>
        @if(auth()->user()->isTeacher())
            <a href="{{ route('requests.create') }}" class="btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Request
            </a>
        @endif
    </div>

    <form method="GET" class="card p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search code, title, location…"
               class="input sm:col-span-2 lg:col-span-2">
        <select name="status" class="input">
            <option value="">All Status</option>
            @foreach(['pending','review','assigned','in_progress','for_verification','completed','cancelled'] as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="category" class="input">
            <option value="">All Categories</option>
            @foreach(['electrical','carpentry','fabrication','aircon','plumbing','general'] as $c)
                <option value="{{ $c }}" @selected($category === $c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="btn-primary flex-1">Filter</button>
            @if($q || $status || $category)
                <a href="{{ route('requests.index') }}" class="btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3 hidden md:table-cell">Category</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 hidden lg:table-cell">Date</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($requests as $r)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 font-mono text-xs text-slate-500 whitespace-nowrap">{{ $r->request_code }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-900">{{ $r->title }}</div>
                                @if($r->location)
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $r->location }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-slate-600 capitalize">{{ $r->category }}</td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $r->priorityColor() }}">{{ ucfirst($r->priority) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="badge {{ $r->statusColor() }}">{{ $r->statusLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell text-slate-500 whitespace-nowrap">
                                {{ $r->date_reported->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('requests.show', $r->request_id) }}"
                                   class="text-brand-600 hover:text-brand-700 font-semibold text-sm whitespace-nowrap">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm">No requests found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($requests->hasPages())
        <div class="mt-4">{{ $requests->links() }}</div>
    @endif
@endsection