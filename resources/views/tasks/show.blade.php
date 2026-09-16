@extends('layouts.app')
@section('title', 'Task Detail')
@section('content')
    <div class="mb-6">
        <div class="font-mono text-sm text-gray-500">{{ $task->request->request_code }} • {{ $task->request->location }}</div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $task->request->title }}</h1>
        <span class="inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold {{ $task->statusColor() }}">{{ ucwords(str_replace('_',' ',$task->status)) }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold mb-3">Problem Description</h2>
                <p class="text-gray-700 whitespace-pre-line">{{ $task->request->description }}</p>
                <div class="text-sm text-gray-500 mt-3">Reported by {{ $task->request->teacher->full_name ?? '—' }}</div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold mb-3">Record Diagnosis</h2>
                <form method="POST" action="{{ route('tasks.diagnosis', $task->assignment_id) }}" class="space-y-3">
                    @csrf
                    <textarea name="findings" rows="3" required placeholder="What did you find?" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg"></textarea>
                    <input type="text" name="recommended_action" placeholder="Recommended action" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <input type="text" name="materials_needed" placeholder="Materials needed" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <textarea name="diagnosis_result" rows="2" placeholder="Diagnosis result" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg"></textarea>
                    <textarea name="solution_steps" rows="4" placeholder="Step-by-step solution (this will go to AI knowledge base)" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg"></textarea>
                    <button class="bg-orange-500 hover:bg-orange-600 text-white font-bold px-5 py-2.5 rounded-lg">Save Diagnosis</button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold mb-3">Update Status</h2>
                <form method="POST" action="{{ route('tasks.update', $task->assignment_id) }}" class="space-y-3">
                    @csrf
                    <select name="status" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                        @foreach(['pending','in_progress','for_review','completed'] as $s)
                            <option value="{{ $s }}" @selected($task->status === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                    <textarea name="notes" rows="2" placeholder="Notes (optional)" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">{{ $task->notes }}</textarea>
                    <button class="w-full bg-gray-900 text-white font-bold py-2.5 rounded-lg">Update</button>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-bold mb-3">Request Materials</h2>
                <form method="POST" action="{{ route('inventory.request') }}" class="space-y-3">
                    @csrf
                    <input type="hidden" name="request_id" value="{{ $task->request_id }}">
                    <select name="item_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                        <option value="">Select item...</option>
                        @foreach(\App\Models\Inventory::orderBy('item_name')->get() as $i)
                            <option value="{{ $i->item_id }}">{{ $i->item_name }} ({{ $i->qty_on_hand }} {{ $i->unit }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="quantity" min="1" value="1" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-lg">Request Material</button>
                </form>
            </div>
        </div>
    </div>
@endsection