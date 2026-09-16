@extends('layouts.app')
@section('title', 'Inventory')
@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Inventory — Stock</h1>

    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-4 flex gap-3">
        <input type="text" name="q" value="{{ $q }}" placeholder="Search item..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
        <button class="bg-gray-900 text-white px-5 py-2 rounded-lg font-semibold">Search</button>
    </form>

    @if($lowStock->count())
        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
            <h3 class="font-bold text-orange-800 mb-2">⚠️ Low Stock Alerts</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($lowStock as $i)
                    <span class="bg-white px-3 py-1 rounded-full text-sm text-orange-700 font-semibold">{{ $i->item_name }} — {{ $i->qty_on_hand }} left</span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase text-left">
                <tr>
                    <th class="px-4 py-3">Code</th>
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3 text-right">Min</th>
                    <th class="px-4 py-3">Unit</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @foreach($items as $i)
                    <tr class="{{ $i->isLowStock() ? 'bg-orange-50' : '' }}">
                        <td class="px-4 py-3 font-mono text-sm">{{ $i->item_code }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $i->item_name }}</td>
                        <td class="px-4 py-3 text-sm capitalize">{{ $i->category }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $i->isLowStock() ? 'text-orange-600' : '' }}">{{ $i->qty_on_hand }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $i->min_stock_level }}</td>
                        <td class="px-4 py-3 text-sm">{{ $i->unit }}</td>
                        <td class="px-4 py-3 text-right">
                            @if(auth()->user()->isInventoryOfficer())
                                <form method="POST" action="{{ route('inventory.stock_in') }}" class="flex gap-1">
                                    @csrf
                                    <input type="hidden" name="item_id" value="{{ $i->item_id }}">
                                    <input type="number" name="quantity" min="1" value="1" class="w-16 px-2 py-1 border rounded text-sm">
                                    <button class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded">+ Stock In</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>
@endsection