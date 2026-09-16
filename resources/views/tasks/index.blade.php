@extends('layouts.app')
@section('title', 'My Tasks')
@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Task Board</h1>
        <p class="text-slate-500 text-sm mt-1">Move your tasks through the workflow</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach([
            'pending'     => ['label' => 'Pending',     'accent' => 'slate'],
            'in_progress' => ['label' => 'In Progress', 'accent' => 'amber'],
            'for_review'  => ['label' => 'For Review',  'accent' => 'indigo'],
            'completed'   => ['label' => 'Completed',   'accent' => 'emerald'],
        ] as $key => $meta)
            @php
                $accent = $meta['accent'];
                $dot = match($accent) {
                    'amber'   => 'bg-amber-500',
                    'indigo'  => 'bg-brand-500',
                    'emerald' => 'bg-emerald-500',
                    default   => 'bg-slate-400',
                };
            @endphp
            <div class="card">
                <div class="p-4 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $dot }}"></span>
                            {{ $meta['label'] }}
                        </h2>
                        <span class="badge bg-slate-100 text-slate-600">{{ $board[$key]->count() }}</span>
                    </div>
                </div>
                <div class="p-3 space-y-3 min-h-[16rem] max-h-[70vh] overflow-y-auto">
                    @forelse($board[$key] as $t)
                        <a href="{{ route('tasks.show', $t->assignment_id) }}"
                           class="block p-3 border border-slate-200 rounded-lg bg-white hover:border-brand-400 hover:shadow-sm transition">
                            <div class="font-mono text-[11px] text-slate-500">{{ $t->request->request_code }}</div>
                            <div class="font-semibold text-slate-900 text-sm mt-1 line-clamp-2">{{ $t->request->title }}</div>
                            @if($t->request->location)
                                <div class="text-xs text-slate-500 mt-1">{{ $t->request->location }}</div>
                            @endif
                            <span class="inline-block mt-2 badge {{ $t->request->priorityColor() }}">
                                {{ ucfirst($t->request->priority) }}
                            </span>
                        </a>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-8">No tasks</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
@endsection