@extends('layouts.app')
@section('title', 'Task Detail')
@section('content')
    @php
        $u = auth()->user();
        $existingDiagnosis = $task->request->diagnoses->first();
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <div class="text-xs font-mono text-slate-500">
                {{ $task->request->request_code }}
                @if($task->request->location) · {{ $task->request->location }} @endif
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ $task->request->title }}</h1>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <span class="badge {{ $task->statusColor() }}">
                    {{ ucwords(str_replace('_',' ',$task->status)) }}
                </span>
                <span class="badge {{ $task->request->priorityColor() }}">
                    {{ ucfirst($task->request->priority) }}
                </span>
                <span class="badge bg-slate-100 text-slate-700 capitalize">
                    {{ $task->request->category }}
                </span>
            </div>
        </div>
        <a href="{{ route('tasks.index') }}" class="btn-ghost">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- LEFT: Details + Photos + Diagnosis -->
        <div class="lg:col-span-2 space-y-6">

            {{-- Problem Description --}}
            <div class="card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Problem Description</h2>
                <p class="text-slate-700 whitespace-pre-line leading-relaxed">{{ $task->request->description }}</p>
                <div class="mt-3 text-sm text-slate-500">
                    Reported by <span class="font-semibold text-slate-700">{{ $task->request->teacher->full_name ?? '—' }}</span>
                    on {{ $task->request->date_reported->format('M d, Y h:i A') }}
                </div>
            </div>

            {{-- Photo Evidence — shows BEFORE and AFTER --}}
            @if($task->request->photo_before || $task->request->photo_after)
                <div class="card p-6">
                    <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Photo Evidence</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($task->request->photo_before)
                            <div>
                                <div class="text-xs font-semibold text-slate-500 uppercase mb-2">Before</div>
                                <img src="{{ Storage::url($task->request->photo_before) }}"
                                     alt="Before repair"
                                     class="rounded-lg border border-slate-200 w-full h-56 object-cover cursor-pointer hover:opacity-90 transition"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                        @endif
                        @if($task->request->photo_after)
                            <div>
                                <div class="text-xs font-semibold text-emerald-600 uppercase mb-2">After ✓</div>
                                <img src="{{ Storage::url($task->request->photo_after) }}"
                                     alt="After repair"
                                     class="rounded-lg border border-emerald-200 w-full h-56 object-cover cursor-pointer hover:opacity-90 transition"
                                     onclick="window.open(this.src, '_blank')">
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Record Diagnosis --}}
            <div class="card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Record Diagnosis</h2>
                <form method="POST" action="{{ route('tasks.diagnosis', $task->assignment_id) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="label" for="findings">Findings <span class="text-rose-500">*</span></label>
                        <textarea id="findings" name="findings" rows="3" required minlength="5"
                                  class="input resize-y"
                                  placeholder="What did you find?">{{ old('findings', $existingDiagnosis->findings ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="label" for="recommended_action">Recommended Action</label>
                        <input id="recommended_action" type="text" name="recommended_action"
                               value="{{ old('recommended_action', $existingDiagnosis->recommended_action ?? '') }}"
                               class="input" placeholder="e.g., Replace faulty capacitor">
                    </div>
                    <div>
                        <label class="label" for="materials_needed">Materials Needed</label>
                        <input id="materials_needed" type="text" name="materials_needed"
                               value="{{ old('materials_needed', $existingDiagnosis->materials_needed ?? '') }}"
                               class="input" placeholder="e.g., 1x capacitor 25µF">
                    </div>
                    <div>
                        <label class="label" for="diagnosis_result">Diagnosis Result</label>
                        <textarea id="diagnosis_result" name="diagnosis_result" rows="2"
                                  class="input resize-y"
                                  placeholder="Summary of diagnosis">{{ old('diagnosis_result', $existingDiagnosis->diagnosis_result ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="label" for="solution_steps">
                            Step-by-Step Solution
                            <span class="text-xs text-slate-400 font-normal">(added to AI knowledge base)</span>
                        </label>
                        <textarea id="solution_steps" name="solution_steps" rows="5"
                                  class="input resize-y font-mono text-sm"
                                  placeholder="1. Turn off power&#10;2. Open the casing&#10;3. Replace capacitor&#10;4. Test">{{ old('solution_steps', $existingDiagnosis->solution_steps ?? '') }}</textarea>
                    </div>
                    <button class="btn-primary">Save Diagnosis</button>
                </form>
            </div>
        </div>

        <!-- RIGHT: Status + Materials -->
        <div class="space-y-6">

            {{-- Update Status --}}
            <div class="card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Update Status</h2>
                <form method="POST" action="{{ route('tasks.update', $task->assignment_id) }}"
                      enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <select name="status" required class="input">
                        @foreach(['pending','in_progress','for_review','completed'] as $s)
                            <option value="{{ $s }}" @selected($task->status === $s)>
                                {{ ucwords(str_replace('_',' ',$s)) }}
                            </option>
                        @endforeach
                    </select>
                    <textarea name="notes" rows="2" class="input resize-y"
                              placeholder="Notes (optional)">{{ $task->notes }}</textarea>

                    {{-- After-photo upload --}}
                    <div>
                        <label class="label" for="photo_after">After-Repair Photo</label>
                        <input id="photo_after" type="file" name="photo_after" accept="image/*"
                               class="w-full px-3 py-2 border border-dashed border-slate-300 rounded-lg text-sm
                                      file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-brand-50
                                      file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                        <p class="text-xs text-slate-400 mt-1">Upload after completing the repair.</p>
                    </div>

                    <button class="btn-primary w-full">Update</button>
                </form>
            </div>

            {{-- Request Materials --}}
            <div class="card p-6">
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-3">Request Materials</h2>
                <form method="POST" action="{{ route('inventory.request') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="request_id" value="{{ $task->request_id }}">
                    <select name="item_id" required class="input">
                        <option value="">Select item…</option>
                        @foreach($items as $i)
                            <option value="{{ $i->item_id }}">
                                {{ $i->item_name }} ({{ $i->qty_on_hand }} {{ $i->unit }})
                            </option>
                        @endforeach
                    </select>
                    <input type="number" name="quantity" min="1" value="1" required class="input">
                    <button class="btn-primary w-full">Request Material</button>
                </form>
            </div>
        </div>
    </div>
@endsection