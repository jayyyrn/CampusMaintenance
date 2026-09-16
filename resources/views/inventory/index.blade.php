@extends('layouts.app')
@section('title', 'Inventory')
@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Inventory</h1>
        <p class="text-slate-500 text-sm mt-1">Track materials, spare parts, and consumables</p>
    </div>

    <form method="GET" class="card p-4 mb-4 flex gap-3">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search item name, code, or category…" class="input flex-1">
        <button class="btn-primary">Search</button>
        @if($q)
            <a href="{{ route('inventory.index') }}" class="btn-secondary">Clear</a>
        @endif
    </form>

    @if($lowStock->count())
        <div class="mb-4 p-4 rounded-xl bg-amber-50 border border-amber-200">
            <h3 class="font-bold text-amber-900 text-sm mb-2 flex items-center gap-2">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $lowStock->count() }} item(s) low on stock
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($lowStock as $i)
                    <span class="bg-white border border-amber-200 px-3 py-1 rounded-full text-xs font-semibold text-amber-800">
                        {{ $i->item_name }} — {{ $i->qty_on_hand }} left
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3 hidden md:table-cell">Category</th>
                        <th class="px-4 py-3 text-right">On Hand</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Min</th>
                        <th class="px-4 py-3 hidden lg:table-cell">Unit</th>
                        @if(auth()->user()->isInventoryOfficer())
                            <th class="px-4 py-3 text-right">Stock In</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $i)
                        <tr class="{{ $i->isLowStock() ? 'bg-amber-50/50' : '' }} hover:bg-slate-50/70 transition">
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $i->item_code }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $i->item_name }}</td>
                            <td class="px-4 py-3 hidden md:table-cell text-slate-600 capitalize">{{ $i->category }}</td>
                            <td class="px-4 py-3 text-right font-bold {{ $i->isLowStock() ? 'text-amber-600' : 'text-slate-800' }}">
                                {{ $i->qty_on_hand }}
                            </td>
                            <td class="px-4 py-3 text-right text-slate-500 hidden sm:table-cell">{{ $i->min_stock_level }}</td>
                            <td class="px-4 py-3 hidden lg:table-cell text-slate-500">{{ $i->unit }}</td>
                            @if(auth()->user()->isInventoryOfficer())
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('inventory.stock_in') }}" class="inline-flex items-center gap-1">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $i->item_id }}">
                                        <input type="number" name="quantity" min="1" value="1" class="w-16 px-2 py-1 border border-slate-300 rounded text-sm">
                                        <button class="btn-primary text-xs py-1 px-2">+</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($items->hasPages())
        <div class="mt-4">{{ $items->links() }}</div>
    @endif
@endsection