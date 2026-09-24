@extends('layouts.app')
@section('title', 'New Request')
@section('content')

<div x-data="requestForm()" x-init="init()" class="max-w-2xl">
    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('requests.index') }}" class="text-sm text-slate-500 hover:text-slate-800 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to requests
            </a>
            <h1 class="text-2xl font-bold text-slate-900 mt-2">New Maintenance Request</h1>
        </div>

        <button type="button" @click="scanOpen = true" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            📷 Scan Paper Form
        </button>
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

        {{-- Hidden fields set by scanner or defaults --}}
        <input type="hidden" name="unit_no"             x-model="f.unit_no">
        <input type="hidden" name="tools_and_materials" x-model="f.tools_and_materials">
        <input type="hidden" name="estimated_budget"    x-model="f.estimated_budget">
        <input type="hidden" name="date_start"          x-model="f.date_start">
        <input type="hidden" name="date_finish"         x-model="f.date_finish">
        {{-- Priority is intentionally hidden — always 'medium' for new requests --}}
        <input type="hidden" name="priority" value="medium">

        <div>
            <label class="label" for="title">Title / Short Summary <span class="text-rose-500">*</span></label>
            <input id="title" type="text" name="title" x-model="f.title" value="{{ old('title') }}" required
                   class="input" placeholder="e.g., Aircon not cooling in Room 201">
        </div>

        {{-- CATEGORY with Other option --}}
        <div>
            <label class="label" for="category">Category <span class="text-rose-500">*</span></label>
            <select id="category" name="category" x-model="f.category" required class="input">
                <option value="">Select…</option>
                @foreach(['electrical','carpentry','fabrication','aircon','plumbing','general'] as $c)
                    <option value="{{ $c }}" @selected(old('category') === $c)>{{ ucfirst($c) }}</option>
                @endforeach
                <option value="other" @selected(old('category') === 'other')>Other (specify below)</option>
            </select>
        </div>

        <div x-show="f.category === 'other'" x-cloak>
            <label class="label" for="custom_category">
                Specify Category <span class="text-rose-500">*</span>
            </label>
            <input id="custom_category" type="text" name="custom_category" x-model="f.custom_category"
                   value="{{ old('custom_category') }}"
                   class="input" placeholder="e.g., painting, welding, glass repair…">
        </div>

        <div>
            <label class="label" for="location">Location</label>
            <input id="location" type="text" name="location" x-model="f.location" value="{{ old('location') }}"
                   class="input" placeholder="e.g., Room 201">
        </div>

        <div>
            <label class="label" for="description">Problem Description <span class="text-rose-500">*</span></label>
            <textarea id="description" name="description" x-model="f.description" rows="5" required minlength="10"
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

    {{-- SCANNER MODAL --}}
    <div x-show="scanOpen" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
         @keydown.escape.window="scanOpen = false">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
             @click.outside="scanOpen = false">
            <div class="flex items-center justify-between p-5 border-b border-slate-200">
                <h2 class="font-bold text-lg text-slate-900">📷 Scan Paper Form</h2>
                <button @click="scanOpen = false" type="button" class="btn-ghost">✕</button>
            </div>

            <div class="p-6 space-y-4">
                <template x-if="!scanImage">
                    <div>
                        <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                            <svg class="w-12 h-12 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="font-semibold text-slate-700">Take a photo or upload</span>
                            <span class="text-xs text-slate-400 mt-1">JPG / PNG / WebP — up to 8 MB</span>
                            <input type="file" accept="image/*" capture="environment" class="hidden" @change="handleUpload($event)">
                        </label>
                        <p class="text-xs text-slate-500 mt-3 text-center">
                            Tip: lay the paper flat, good lighting, no shadows.
                        </p>
                    </div>
                </template>

                <template x-if="scanImage">
                    <div class="space-y-3">
                        <img :src="scanImage" class="w-full rounded-lg border border-slate-200 max-h-64 object-contain bg-slate-50">

                        <div class="flex gap-2">
                            <button type="button" @click="resetScan()" class="btn-secondary flex-1">Choose Another</button>
                            <button type="button" @click="runScan()" :disabled="scanLoading" class="btn-primary flex-1">
                                <span x-show="!scanLoading">Extract Fields</span>
                                <span x-show="scanLoading">Reading…</span>
                            </button>
                        </div>

                        <template x-if="scanError">
                            <div class="bg-rose-50 border border-rose-200 text-rose-700 text-sm p-3 rounded-lg" x-text="scanError"></div>
                        </template>

                        <template x-if="scanResult">
                            <div class="space-y-2">
                                <div class="text-sm font-semibold text-slate-700">Extracted fields — review, then apply:</div>
                                <template x-for="(val, key) in scanResult" :key="key">
                                    <div class="flex items-start gap-3 text-sm border border-slate-200 rounded-lg p-2">
                                        <span class="w-40 text-slate-500 capitalize shrink-0" x-text="key.replace(/_/g, ' ')"></span>
                                        <span class="flex-1 text-slate-800" x-text="val || '—'"></span>
                                    </div>
                                </template>
                                <button type="button" @click="applyScan()" class="btn-primary w-full mt-2">✓ Apply to Form</button>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function requestForm() {
    return {
        f: {
            title: '', description: '', category: '', custom_category: '',
            location: '', unit_no: '', tools_and_materials: '',
            estimated_budget: '', date_start: '', date_finish: '',
        },
        scanOpen: false, scanImage: null, scanFile: null,
        scanLoading: false, scanError: '', scanResult: null,

        init() {},

        resetScan() {
            this.scanImage = null; this.scanFile = null;
            this.scanResult = null; this.scanError = '';
        },

        handleUpload(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.scanFile = file;
            this.scanImage = URL.createObjectURL(file);
            this.scanResult = null;
            this.scanError = '';
        },

        async runScan() {
            if (!this.scanFile) return;
            this.scanLoading = true; this.scanError = ''; this.scanResult = null;
            const fd = new FormData();
            fd.append('image', this.scanFile);
            fd.append('_token', document.querySelector('meta[name=csrf-token]').content);
            try {
                const res = await fetch('{{ route('requests.scan') }}', {
                    method: 'POST', body: fd, headers: { 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.error || 'Scan failed');
                this.scanResult = data.fields;
            } catch (err) { this.scanError = err.message; }
            this.scanLoading = false;
        },

        applyScan() {
            if (!this.scanResult) return;
            const r = this.scanResult;

            if (r.description) this.f.title = r.description;
            if (r.unit_no)     this.f.location = 'Unit ' + r.unit_no;

            // Category: if the AI's guess matches an enum, use it.
            // Otherwise set it to 'other' and put the raw text in custom_category.
            const validCats = ['electrical','carpentry','fabrication','aircon','plumbing','general'];
            const cat = (r.category || '').toLowerCase().trim();
            if (validCats.includes(cat)) {
                this.f.category = cat;
                this.f.custom_category = '';
            } else if (cat) {
                this.f.category = 'other';
                this.f.custom_category = r.category;
            } else {
                this.f.category = 'general';
            }

            // Priority is no longer set by teacher — hidden, always 'medium'
            // Supervisor-only fields — stored in hidden inputs, will appear on show page
            if (r.unit_no)             this.f.unit_no = r.unit_no;
            if (r.tools_and_materials) this.f.tools_and_materials = r.tools_and_materials;
            if (r.estimated_budget)    this.f.estimated_budget = r.estimated_budget;
            if (r.date_start)          this.f.date_start = r.date_start;
            if (r.date_finish)         this.f.date_finish = r.date_finish;

            this.scanOpen = false;
        },
    }
}
</script>

@endsection