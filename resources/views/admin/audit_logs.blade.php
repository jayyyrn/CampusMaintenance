@extends('layouts.app')
@section('title', 'Audit Logs')
@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Audit Trail</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase text-left">
                <tr>
                    <th class="px-4 py-3">When</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Entity</th>
                    <th class="px-4 py-3">Details</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm">
                @foreach($logs as $l)
                    <tr>
                        <td class="px-4 py-3 text-gray-500">{{ $l->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $l->user->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono">{{ $l->action }}</td>
                        <td class="px-4 py-3">{{ $l->entity_type }}{{ $l->entity_id ? ' #'.$l->entity_id : '' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ Str::limit($l->details, 60) }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $l->ip_address }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection