@extends('layouts.app')
@section('title', 'Public Queue')
@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Public Maintenance Queue</h1>
    <p class="text-gray-500 mb-6">See active requests in order. Check this before submitting a new one to avoid duplicates.</p>

    @foreach($grouped as $category => $items)
        <div class="mb-6">
            <h2 class="text-sm font-bold text-gray-500 uppercase mb-2 flex items-center gap-2">
                <span class="capitalize">{{ $category }}</span>
                <span class="bg-gray-200 text-gray-700 px-2 py-0.5 rounded-full text-xs">{{ $items->count() }}</span>
            </h2>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y">
                @foreach($items as $r)
                    <div class="flex items-center gap-4 p-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center font-bold">
                            #{{ $r->queue_position }}
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">{{ $r->title }}</div>
                            <div class="text-xs text-gray-500">
                                {{ $r->request_code }} • {{ $r->location }} •
                                by {{ $r->teacher->full_name ?? '—' }} •
                                {{ $r->date_reported->diffForHumans() }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block px-3 py-1 rounded-full text-xs font-semibold {{ $r->statusColor() }}">{{ $r->statusLabel() }}</span>
                            <span class="block mt-1 px-2 py-0.5 rounded text-xs {{ $r->priorityColor() }}">{{ ucfirst($r->priority) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection