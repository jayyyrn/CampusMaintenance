@extends('layouts.app')
@section('title', 'Notifications')
@section('content')
    @php $unreadCount = auth()->user()->notifications()->where('is_read', false)->count(); @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
            <p class="text-slate-500 text-sm mt-1">
                {{ $unreadCount > 0 ? "$unreadCount unread" : 'All caught up' }}
            </p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.mark_read') }}">
                @csrf
                <button class="btn-secondary text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    <div class="card overflow-hidden">
        @forelse($notifications as $n)
            @php
                $iconBg = match($n->type) {
                    'success' => 'bg-emerald-100 text-emerald-600',
                    'warning' => 'bg-amber-100 text-amber-600',
                    'danger'  => 'bg-rose-100 text-rose-600',
                    default   => 'bg-brand-100 text-brand-600',
                };
                $iconPath = match($n->type) {
                    'success' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
                    'warning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
                    'danger'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
                    default   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                };
                $href = $n->link ?? route('notifications.index');
                $isUnread = !$n->is_read;
            @endphp

            {{-- WHOLE ROW is clickable --}}
            <a href="{{ $href }}"
               class="flex items-start gap-4 p-5 border-b border-slate-100 last:border-0
                      hover:bg-slate-50 active:bg-slate-100 transition
                      {{ $isUnread ? 'bg-brand-50/40 border-l-4 border-l-brand-500' : '' }}">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 {{ $iconBg }}">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $iconPath !!}
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3">
                        <div class="font-semibold text-slate-900 text-base leading-snug">
                            {{ $n->title }}
                        </div>
                        @if($isUnread)
                            <span class="shrink-0 w-2.5 h-2.5 rounded-full bg-brand-500 mt-1.5" title="Unread"></span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">{{ $n->message }}</p>
                    <div class="text-xs text-slate-400 mt-2 flex items-center gap-3">
                        <span>{{ $n->created_at->diffForHumans() }}</span>
                        @if($n->link)
                            <span class="text-brand-600 font-semibold">Click to view →</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="p-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-3">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <p class="text-slate-500 font-medium">No notifications yet</p>
                <p class="text-sm text-slate-400 mt-1">You'll see alerts here when things happen.</p>
            </div>
        @endforelse
    </div>
@endsection