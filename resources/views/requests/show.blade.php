@extends('layouts.app')
@section('title', 'Request Details')
@section('content')
    @php $u = auth()->user(); @endphp

    <div class="mb-6 flex items-start justify-between">
        <div>
            <div class="text-sm text-gray-500 font-mono">{{ $req->request_code }}</div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $req->title }}</h1>
            <div class="mt-2 flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $req->statusColor() }}">{{ $req->statusLabel() }}</span>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $req->priorityColor() }}">{{ ucfirst($req->priority) }}</span>
                <span class="text-xs text-gray-500 capitalize">{{ $req->category }}</span>
            </div>
        </div>
        <a href="{{ route('requests.index') }}" class="text-gray-500 hover:text-gray-700">← Back</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold text-gray-900 mb-3">Description</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $req->description }}</p>

                <dl class="grid grid-cols-2 gap-4 mt-6 text-sm">
                    <div><dt class="text-gray-500">Reported by</dt><dd class="font-semibold">{{ $req->teacher->full_name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Department</dt><dd class="font-semibold">{{ $req->department->dept_name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Location</dt><dd class="font-semibold">{{ $req->location ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Equipment</dt><dd class="font-semibold">{{ $req->equipment->equipment_name ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Date Reported</dt><dd class="font-semibold">{{ $req->date_reported->format('M d, Y h:i A') }}</dd></div>
                    <div><dt class="text-gray-500">Queue Position</dt><dd class="font-semibold">#{{ $req->queue_position ?: '—' }}</dd></div>
                </dl>

                @if($req->photo_before)
                    <div class="mt-4">
                        <div class="text-sm text-gray-500 mb-1">Photo evidence:</div>
                        <img src="{{ Storage::url($req->photo_before) }}" class="rounded-lg max-h-64">
                    </div>
                @endif
            </div>

            <!-- Assignments -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold text-gray-900 mb-3">Task Assignments</h2>
                @forelse($req->assignments as $a)
                    <div class="border border-gray-200 rounded-lg p-4 mb-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-semibold">{{ $a->technician->full_name ?? '—' }}</div>
                                <div class="text-xs text-gray-500">
                                    Assigned by {{ $a->assigner->full_name ?? '—' }} • {{ $a->assigned_at->format('M d, Y') }}
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $a->statusColor() }}">
                                {{ ucwords(str_replace('_',' ',$a->status)) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">No assignment yet.</p>
                @endforelse
            </div>

            <!-- Diagnoses -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold text-gray-900 mb-3">Diagnosis Records</h2>
                @forelse($req->diagnoses as $d)
                    <div class="border border-gray-200 rounded-lg p-4 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="text-sm font-semibold">{{ $d->technician->full_name ?? '—' }}</div>
                            @if($d->is_verified)
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">✓ Verified</span>
                            @else
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Pending verification</span>
                            @endif
                        </div>
                        <div class="text-sm space-y-2">
                            <div><strong>Findings:</strong> {{ $d->findings }}</div>
                            @if($d->recommended_action) <div><strong>Action:</strong> {{ $d->recommended_action }}</div> @endif
                            @if($d->materials_needed)   <div><strong>Materials:</strong> {{ $d->materials_needed }}</div> @endif
                            @if($d->solution_steps)     <div><strong>Steps:</strong><pre class="whitespace-pre-wrap text-xs bg-gray-50 p-2 rounded mt-1">{{ $d->solution_steps }}</pre></div> @endif
                        </div>

                        @if(!$d->is_verified && $u->isSupervisor())
                            <form method="POST" action="{{ route('diagnoses.verify', $d->diagnosis_id) }}" class="mt-3">
                                @csrf
                                <button class="text-xs bg-green-500 text-white px-3 py-1.5 rounded font-semibold hover:bg-green-600">Verify Diagnosis</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">No diagnosis recorded yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Right: actions -->
        <div class="space-y-6">
            @if($u->isSupervisor() && $req->status !== 'completed')
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-3">Assign Technician</h3>
                    <form method="POST" action="{{ route('requests.assign', $req->request_id) }}" class="space-y-3">
                        @csrf
                        <select name="technician_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                            <option value="">Select technician...</option>
                            @foreach(\App\Models\User::whereIn('role',['technician','lead_technician'])->where('status','active')->get() as $t)
                                <option value="{{ $t->user_id }}">{{ $t->full_name }} ({{ $t->role }})</option>
                            @endforeach
                        </select>
                        <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-lg">Assign</button>
                    </form>
                </div>
            @endif

            @if($u->isSupervisor())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-3">Update Status</h3>
                    <form method="POST" action="{{ route('requests.status', $req->request_id) }}" class="space-y-3">
                        @csrf
                        <select name="status" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                            @foreach(['pending','review','assigned','in_progress','for_verification','completed','cancelled'] as $s)
                                <option value="{{ $s }}" @selected($req->status === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                        </select>
                        <button class="w-full bg-gray-900 text-white font-bold py-2.5 rounded-lg">Update</button>
                    </form>
                </div>
            @endif

            <!-- Materials -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-900 mb-3">Materials Used</h3>
                @forelse($req->materialRequests as $m)
                    <div class="text-sm border-b py-2 last:border-0">
                        <div class="font-semibold">{{ $m->item->item_name ?? '—' }}</div>
                        <div class="text-xs text-gray-500">
                            Requested: {{ $m->quantity_requested }} {{ $m->item->unit ?? '' }} •
                            Released: {{ $m->quantity_released }} •
                            Returned: {{ $m->quantity_returned }}
                        </div>
                        <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $m->status }}</span>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm">No materials requested.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection