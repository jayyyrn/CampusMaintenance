@extends('layouts.app')
@section('title', 'New Request')
@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">New Maintenance Request</h1>

    <form method="POST" action="{{ route('requests.store') }}" enctype="multipart/form-data"
          class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Title / Short Summary</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 outline-none"
                   placeholder="e.g., Aircon not cooling in Room 201">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                <select name="category" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <option value="">Select...</option>
                    @foreach(['electrical','carpentry','fabrication','aircon','plumbing','general'] as $c)
                        <option value="{{ $c }}" @selected(old('category') === $c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Priority</label>
                <select name="priority" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" @selected(old('priority') === $p || (!old('priority') && $p === 'medium'))>{{ ucfirst($p) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Location</label>
                <input type="text" name="location" value="{{ old('location') }}"
                       placeholder="e.g., Room 201"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Equipment (Optional)</label>
                <select name="equipment_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
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
            <label class="block text-sm font-semibold text-gray-700 mb-1">Problem Description</label>
            <textarea name="description" rows="5" required
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg outline-none focus:ring-2 focus:ring-orange-500"
                      placeholder="Describe the problem in detail...">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Photo Evidence (Optional)</label>
            <input type="file" name="photo_before" accept="image/*"
                   class="w-full px-4 py-2.5 border border-dashed border-gray-300 rounded-lg">
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Date Reported</label>
            <input type="text" value="{{ now()->format('M d, Y h:i A') }}" disabled
                   class="w-full px-4 py-2.5 bg-gray-100 border border-gray-200 rounded-lg text-gray-500">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-lg shadow-lg shadow-orange-500/30">
                SUBMIT REQUEST
            </button>
            <a href="{{ route('requests.index') }}" class="px-6 py-3 border border-gray-300 rounded-lg font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
        </div>
    </form>
@endsection