@extends('layouts.app')
@section('title', 'My Tasks')
@section('content')
<div x-data="taskBoard()" x-init="init()">

    <div class="page-header">
        <div class="min-w-0">
            <h1 class="page-title">Task Board</h1>
            <p class="page-subtitle">Drag tasks between columns to update their status</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        @php
            $columns = [
                'pending'     => ['label' => 'Pending',     'accent' => 'slate'],
                'in_progress' => ['label' => 'In Progress', 'accent' => 'amber'],
                'for_review'  => ['label' => 'For Review',  'accent' => 'indigo'],
                'completed'   => ['label' => 'Completed',   'accent' => 'emerald'],
            ];
            $dotMap = [
                'amber'   => 'bg-amber-500',
                'indigo'  => 'bg-brand-500',
                'emerald' => 'bg-emerald-500',
                'slate'   => 'bg-slate-400',
            ];
        @endphp

        @foreach($columns as $key => $meta)
            <div class="card">
                <div class="p-4 border-b border-slate-100">
                    <div class="flex items-center justify-between">
                        <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $dotMap[$meta['accent']] }}"></span>
                            {{ $meta['label'] }}
                        </h2>
                        <span class="badge bg-slate-100 text-slate-600"
                              x-text="counts['{{ $key }}']">{{ $board[$key]->count() }}</span>
                    </div>
                </div>

                <div class="p-3 space-y-3 min-h-[16rem] max-h-[70vh] overflow-y-auto"
                     data-column="{{ $key }}"
                     x-ref="column_{{ $key }}">

                    @forelse($board[$key] as $t)
                        <div data-task-id="{{ $t->assignment_id }}"
                             data-status="{{ $key }}"
                             class="task-card block p-3 border border-slate-200 rounded-lg bg-white
                                    hover:border-brand-400 hover:shadow-sm transition cursor-grab
                                    active:cursor-grabbing select-none">
                            <div class="font-mono text-[11px] text-slate-500">
                                {{ $t->request->request_code }}
                            </div>
                            <div class="font-semibold text-slate-900 text-sm mt-1 line-clamp-2">
                                {{ $t->request->title }}
                            </div>
                            @if($t->request->location)
                                <div class="text-xs text-slate-500 mt-1">{{ $t->request->location }}</div>
                            @endif
                            <span class="inline-block mt-2 {{ $t->request->priorityColor() }}">
                                {{ ucfirst($t->request->priority) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-8"
                           data-empty="{{ $key }}">No tasks</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- Toast for errors --}}
    <div x-show="error" x-cloak x-transition
         class="fixed bottom-4 right-4 z-50 bg-rose-600 text-white px-4 py-3 rounded-lg shadow-lg text-sm">
        <span x-text="error"></span>
    </div>
</div>

<script type="module">
import Sortable from 'sortablejs';

window.taskBoard = function () {
    return {
        error: '',
        counts: {
            pending:     {{ $board['pending']->count() }},
            in_progress: {{ $board['in_progress']->count() }},
            for_review:  {{ $board['for_review']->count() }},
            completed:   {{ $board['completed']->count() }},
        },

        init() {
            const self = this;
            const csrf = document.querySelector('meta[name=csrf-token]').content;

            ['pending','in_progress','for_review','completed'].forEach(col => {
                const el = this.$refs['column_' + col];
                if (!el) return;

                new Sortable(el, {
                    group: 'tasks',
                    animation: 150,
                    ghostClass: 'opacity-40',
                    dragClass: 'shadow-lg',
                    forceFallback: false,
                    onEnd: async (evt) => {
                        const taskId   = evt.item.dataset.taskId;
                        const fromCol  = evt.from.dataset.column;
                        const toCol    = evt.to.dataset.column;

                        // Same column — no-op
                        if (fromCol === toCol) return;

                        // Optimistic UI
                        evt.item.dataset.status = toCol;
                        self.counts[fromCol] = Math.max(0, self.counts[fromCol] - 1);
                        self.counts[toCol]   = self.counts[toCol] + 1;
                        self.refreshEmptyStates();

                        try {
                            const res = await fetch(`/tasks/${taskId}/move`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ status: toCol })
                            });

                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            const data = await res.json();
                            if (!data.ok) throw new Error(data.error || 'Failed');
                        } catch (e) {
                            // Revert
                            evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex] ?? null);
                            evt.item.dataset.status = fromCol;
                            self.counts[fromCol] = self.counts[fromCol] + 1;
                            self.counts[toCol]   = Math.max(0, self.counts[toCol] - 1);
                            self.refreshEmptyStates();

                            self.error = 'Could not move task: ' + e.message;
                            setTimeout(() => self.error = '', 3500);
                        }
                    }
                });
            });

            this.refreshEmptyStates();
        },

        refreshEmptyStates() {
            document.querySelectorAll('[data-column]').forEach(col => {
                const key   = col.dataset.column;
                const empty = col.querySelector('[data-empty="' + key + '"]');
                const hasTasks = col.querySelectorAll('[data-task-id]').length > 0;

                if (hasTasks && empty) empty.remove();
                if (!hasTasks && !empty) {
                    const p = document.createElement('p');
                    p.className = 'text-xs text-slate-400 text-center py-8';
                    p.dataset.empty = key;
                    p.textContent = 'No tasks';
                    col.appendChild(p);
                }
            });
        }
    };
};
</script>
@endsection