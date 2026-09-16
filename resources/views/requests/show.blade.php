@extends('layouts.app')
@section('title', 'Request Details')
@section('content')
    @php $u = auth()->user(); @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="text-xs font-mono text-slate-500">{{ $req->request_code }}</div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $req->title }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="badge {{ $req->statusColor() }}">{{ $req->statusLabel() }}</span>
                <span class="badge {{ $req->priorityColor() }}">{{ ucfirst($req->priority) }}</span>
                <span class="badge bg-slate-100 text-slate-700 capitalize">{{ $req->category }}</span>
                @if($req->queue_position)
                    <span class="badge bg-brand-50 text-brand-700">Queue #{{ $req->queue_position }}</span>
                @endif
            </div>
        </div>
        <a href="{{ route('requests.index') }}" class="btn-ghost">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left column -->
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Description</h2>
                <p class="text-slate-700 whitespace-pre-line leading-relaxed">{{ $req->description }}</p>

                <dl class="grid grid-cols-2 gap-x-4 gap-y-4 mt-6 text-sm">
                    <div>
                        <dt class="text-slate-500">Reported by</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $req->teacher->full_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Department</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $req->department->dept_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Location</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $req->location ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Equipment</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $req->equipment->equipment_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Date Reported</dt>
                        <dd class="font-semibold text-slate-900 mt-0.5">{{ $req->date_reported->format('M d, Y h:i A') }}</dd>
                    </div>
                    @if($req->date_completed)
                        <div>
                            <dt class="text-slate-500">Date Completed</dt>
                            <dd class="font-semibold text-emerald-700 mt-0.5">{{ $req->date_completed->format('M d, Y h:i A') }}</dd>
                        </div>
                    @endif
                </dl>

                @if($req->photo_before)
                    <div class="mt-6">
                        <div class="text-sm text-slate-500 mb-2">Before</div>
                        <img src="{{ Storage::url($req->photo_before) }}" class="rounded-lg max-h-64 border border-slate-200">
                    </div>
                @endif
                @if($req->photo_after)
                    <div class="mt-4">
                        <div class="text-sm text-slate-500 mb-2">After</div>
                        <img src="{{ Storage::url($req->photo_after) }}" class="rounded-lg max-h-64 border border-slate-200">
                    </div>
                @endif
            </div>

            <!-- Assignments -->
            <div class="card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Task Assignments</h2>
                @forelse($req->assignments as $a)
                    <div class="border border-slate-200 rounded-lg p-4 mb-3 last:mb-0 bg-slate-50/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold text-slate-900">{{ $a->technician->full_name ?? '—' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Assigned by {{ $a->assigner->full_name ?? '—' }} • {{ $a->assigned_at->format('M d, Y') }}
                                </div>
                            </div>
                            <span class="badge {{ $a->statusColor() }}">{{ ucwords(str_replace('_',' ',$a->status)) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm">No technician assigned yet.</p>
                @endforelse
            </div>

            <!-- Diagnoses -->
            <div class="card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Diagnosis Records</h2>
                @forelse($req->diagnoses as $d)
                    <div class="border border-slate-200 rounded-lg p-4 mb-3 last:mb-0">
                        <div class="flex items-center justify-between mb-3">
                            <div class="text-sm font-semibold text-slate-900">{{ $d->technician->full_name ?? '—' }}</div>
                            @if($d->is_verified)
                                <span class="badge bg-emerald-100 text-emerald-700">✓ Verified</span>
                            @else
                                <span class="badge bg-amber-100 text-amber-800">Pending verification</span>
                            @endif
                        </div>
                        <div class="text-sm space-y-2 text-slate-700">
                            <div><span class="font-semibold text-slate-500">Findings:</span> {{ $d->findings }}</div>
                            @if($d->recommended_action)
                                <div><span class="font-semibold text-slate-500">Action:</span> {{ $d->recommended_action }}</div>
                            @endif
                            @if($d->materials_needed)
                                <div><span class="font-semibold text-slate-500">Materials:</span> {{ $d->materials_needed }}</div>
                            @endif
                            @if($d->solution_steps)
                                <div>
                                    <span class="font-semibold text-slate-500">Steps:</span>
                                    <pre class="whitespace-pre-wrap text-xs bg-slate-50 border border-slate-200 p-3 rounded-lg mt-1 font-mono">{{ $d->solution_steps }}</pre>
                                </div>
                            @endif
                        </div>

                        @if(!$d->is_verified && $u->isSupervisor())
                            <form method="POST" action="{{ route('diagnoses.verify', $d->diagnosis_id) }}" class="mt-3">
                                @csrf
                                <button class="btn-primary text-xs py-1.5 px-3">Verify Diagnosis</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-400 text-sm">No diagnosis recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Right column -->
        <div class="space-y-6">
            @if($u->isSupervisor() && !in_array($req->status, ['completed','cancelled']))
                <div class="card p-6">
                    <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Assign Technician</h3>
                    <form method="POST" action="{{ route('requests.assign', $req->request_id) }}" class="space-y-3">
                        @csrf
                        <select name="technician_id" required class="input">
                            <option value="">Select technician…</option>
                            @foreach(\App\Models\User::whereIn('role',['technician','lead_technician'])->where('status','active')->orderBy('full_name')->get() as $t)
                                <option value="{{ $t->user_id }}">{{ $t->full_name }}</option>
                            @endforeach
                        </select>
                        <button class="btn-primary w-full">Assign</button>
                    </form>
                </div>

                <div class="card p-6">
                    <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Update Status</h3>
                    <form method="POST" action="{{ route('requests.status', $req->request_id) }}" class="space-y-3">
                        @csrf
                        <select name="status" required class="input">
                            @foreach(['pending','review','assigned','in_progress','for_verification','completed','cancelled'] as $s)
                                <option value="{{ $s }}" @selected($req->status === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        <button class="btn-primary w-full">Update Status</button>
                    </form>
                </div>
            @endif

            <div class="card p-6">
                <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Materials Used</h3>
                @forelse($req->materialRequests as $m)
                    <div class="text-sm border-b border-slate-100 py-3 last:border-0">
                        <div class="flex items-center justify-between">
                            <div class="font-semibold text-slate-800">{{ $m->item->item_name ?? '—' }}</div>
                            <span class="badge bg-slate-100 text-slate-700 capitalize">{{ $m->status }}</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            Requested {{ $m->quantity_requested }} {{ $m->item->unit ?? '' }}
                            @if($m->quantity_released) · Released {{ $m->quantity_released }} @endif
                            @if($m->quantity_returned) · Returned {{ $m->quantity_returned }} @endif
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm">No materials requested.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection