@extends('layouts.app')
@section('title', 'My Tasks')
@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Task Board</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'for_review' => 'For Review', 'completed' => 'Completed'] as $key => $label)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <div class="p-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-gray-900">{{ $label }}</h2>
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded-full font-semibold">{{ $board[$key]->count() }}</span>
                    </div>
                </div>
                <div class="p-3 space-y-3 min-h-64">
                    @forelse($board[$key] as $t)
                        <a href="{{ route('tasks.show', $t->assignment_id) }}"
                           class="block p-3 border border-gray-200 rounded-lg hover:border-orange-400 hover:shadow-sm transition">
                            <div class="font-mono text-xs text-gray-500">{{ $t->request->request_code }}</div>
                            <div class="font-semibold text-gray-900 text-sm mt-1">{{ $t->request->title }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $t->request->location }}</div>
                            <span class="inline-block mt-2 px-2 py-0.5 rounded text-xs {{ $t->request->priorityColor() }}">{{ ucfirst($t->request->priority) }}</span>
                        </a>
                    @empty
                        <p class="text-xs text-gray-400 text-center py-6">No tasks</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
@endsection