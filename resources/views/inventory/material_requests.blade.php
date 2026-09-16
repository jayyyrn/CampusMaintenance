@extends('layouts.app')
@section('title', 'Material Requests')
@section('content')
    @php $u = auth()->user(); @endphp
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Material Requests</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase text-left">
                <tr>
                    <th class="px-4 py-3">Request</th>
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3 text-right">Qty</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Requested By</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($requests as $r)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $r->request->request_code ?? '—' }}</td>
                        <td class="px-4 py-3 font-semibold">{{ $r->item->item_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">{{ $r->quantity_requested }} {{ $r->item->unit ?? '' }}</td>
                        <td class="px-4 py-3"><span class="text-xs bg-gray-100 px-2 py-1 rounded capitalize">{{ $r->status }}</span></td>
                        <td class="px-4 py-3 text-sm">{{ $r->technician->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($r->status === 'pending' && $u->isSupervisor())
                                <form method="POST" action="{{ route('inventory.approve', $r->mat_req_id) }}" class="inline">
                                    @csrf
                                    <button class="text-xs bg-blue-500 text-white px-3 py-1.5 rounded font-semibold hover:bg-blue-600">Approve</button>
                                </form>
                            @endif
                            @if($r->status === 'approved' && $u->isInventoryOfficer())
                                <form method="POST" action="{{ route('inventory.release', $r->mat_req_id) }}" class="inline">
                                    @csrf
                                    <button class="text-xs bg-orange-500 text-white px-3 py-1.5 rounded font-semibold hover:bg-orange-600">Release</button>
                                </form>
                            @endif
                            @if($r->status === 'released' && ($u->isTechnician() || $u->isInventoryOfficer()))
                                <form method="POST" action="{{ route('inventory.return', $r->mat_req_id) }}" class="inline-flex gap-1">
                                    @csrf
                                    <input type="number" name="quantity" min="1" value="1" class="w-16 px-2 py-1 border rounded text-sm">
                                    <button class="text-xs bg-gray-700 text-white px-2 py-1 rounded">Return</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-gray-400">No material requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection