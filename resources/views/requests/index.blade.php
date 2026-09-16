@extends('layouts.app')
@section('title', 'Requests')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Maintenance Requests</h1>
        @if(auth()->user()->isTeacher())
            <a href="{{ route('requests.create') }}" class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-5 py-2.5 rounded-lg shadow-lg shadow-orange-500/30">+ New Request</a>
        @endif
    </div>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex flex-wrap gap-3">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search..." class="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg">
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">All Status</option>
            @foreach(['pending','review','assigned','in_progress','for_verification','completed','cancelled'] as $s)
                <option value="{{ $s }}" @selected($status === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="category" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">All Categories</option>
            @foreach(['electrical','carpentry','fabrication','aircon','plumbing','general'] as $c)
                <option value="{{ $c }}" @selected($category === $c)>{{ ucfirst($c) }}</option>
            @endforeach
        </select>
        <button class="bg-gray-900 text-white px-5 py-2 rounded-lg font-semibold">Filter</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-left text-xs font-bold text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Priority</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($requests as $r)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $r->request_code }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-900">{{ $r->title }}</div>
                            <div class="text-xs text-gray-500">{{ $r->location }}</div>
                        </td>
                        <td class="px-4 py-3 capitalize text-sm">{{ $r->category }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $r->priorityColor() }}">{{ ucfirst($r->priority) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $r->statusColor() }}">{{ $r->statusLabel() }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $r->date_reported->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('requests.show', $r->request_id) }}" class="text-orange-500 hover:text-orange-700 font-semibold text-sm">View →</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-8 text-gray-400">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $requests->links() }}</div>
@endsection