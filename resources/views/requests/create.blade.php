@extends('layouts.app')
@section('title', 'New Request')
@section('content')
    <div class="max-w-2xl">
        <div class="mb-6">
            <a href="{{ route('requests.index') }}" class="text-sm text-slate-500 hover:text-slate-800 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to requests
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-2">New Maintenance Request</h1>
        </div>

        @if($errors->any())
            <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-sm p-4 rounded-lg">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data"
              class="card p-6 space-y-5">
            @csrf

            <div>
                <label class="label" for="title">Title / Short Summary <span class="text-rose-500">*</span></label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" required
                       class="input" placeholder="e.g., Aircon not cooling in Room 201">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="category">Category <span class="text-rose-500">*</span></label>
                    <select id="category" name="category" required class="input">
                        <option value="">Select…</option>
                        @foreach(['electrical','carpentry','fabrication','aircon','plumbing','general'] as $c)
                            <option value="{{ $c }}" @selected(old('category') === $c)>{{ ucfirst($c) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="priority">Priority <span class="text-rose-500">*</span></label>
                    <select id="priority" name="priority" required class="input">
                        @foreach(['low','medium','high','urgent'] as $p)
                            <option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="location">Location</label>
                    <input id="location" type="text" name="location" value="{{ old('location') }}"
                           class="input" placeholder="e.g., Room 201">
                </div>
                <div>
                    <label class="label" for="equipment_id">Equipment (Optional)</label>
                    <select id="equipment_id" name="equipment_id" class="input">
                        <option value="">None</option>
                        @foreach($equipment as $e)
                            <option value="{{ $e->equipment_id }}" @selected(old('equipment_id') == $e->equipment_id)>
                                {{ $e->asset_no }} — {{ $e->equipment_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="label" for="description">Problem Description <span class="text-rose-500">*</span></label>
                <textarea id="description" name="description" rows="5" required minlength="10"
                          class="input resize-y"
                          placeholder="Describe the problem in detail (at least 10 characters)…">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="label" for="photo_before">Photo Evidence (Optional)</label>
                <input id="photo_before" type="file" name="photo_before" accept="image/*"
                       class="w-full px-4 py-2.5 border border-dashed border-slate-300 rounded-lg text-sm
                              file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-brand-50
                              file:text-brand-700 file:font-semibold hover:file:bg-brand-100">
                <p class="text-xs text-slate-400 mt-1">JPG, PNG, or WebP. Max 4MB.</p>
            </div>

            <div>
                <label class="label">Date Reported</label>
                <input type="text" value="{{ now()->format('M d, Y h:i A') }}" disabled
                       class="input bg-slate-100 text-slate-500 cursor-not-allowed">
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 py-3">Submit Request</button>
                <a href="{{ route('requests.index') }}" class="btn-secondary sm:w-auto">Cancel</a>
            </div>
        </form>
    </div>
@endsection