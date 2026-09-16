@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Manage Users</h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase text-left">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Department</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($users as $u)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $u->full_name }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $u->username }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ str_replace('_',' ',$u->role) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $u->department->dept_name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-gray-900 mb-4">Add New User</h2>
            <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="username" placeholder="Username" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                <input type="text" name="full_name" placeholder="Full Name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                <select name="role" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    @foreach(['teacher','coordinator','technician','lead_technician','inventory_officer','admin'] as $r)
                        <option value="{{ $r }}">{{ ucwords(str_replace('_',' ',$r)) }}</option>
                    @endforeach
                </select>
                <select name="department_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <option value="">No department</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->dept_id }}">{{ $d->dept_name }}</option>
                    @endforeach
                </select>
                <input type="password" name="password" placeholder="Password" required minlength="6" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-lg">Create User</button>
            </form>
        </div>
    </div>
@endsection