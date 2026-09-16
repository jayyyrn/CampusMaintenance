@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        <form method="POST" action="{{ route('notifications.mark_read') }}">
            @csrf
            <button class="text-sm text-gray-600 hover:text-gray-900">Mark all read</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y">
        @forelse($notifications as $n)
            <div class="p-4 flex items-start gap-3 {{ $n->is_read ? 'opacity-60' : '' }}">
                <div class="text-xl">
                    @if($n->type === 'success') ✅
                    @elseif($n->type === 'warning') ⚠️
                    @elseif($n->type === 'danger') 🚨
                    @else ℹ️ @endif
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-gray-900">{{ $n->title }}</div>
                    <div class="text-sm text-gray-600">{{ $n->message }}</div>
                    <div class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                </div>
                @if($n->link)
                    <a href="{{ $n->link }}" class="text-xs text-orange-500 hover:text-orange-700 font-semibold">View →</a>
                @endif
            </div>
        @empty
            <div class="p-8 text-center text-gray-400">No notifications.</div>
        @endforelse
    </div>
@endsection