@extends('layouts.app')
@section('title', 'Users')
@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Manage Users</h1>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <div x-data="userManager()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase text-left">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Department</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($users as $u)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $u->full_name }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $u->username }}</td>
                            <td class="px-4 py-3 text-sm capitalize">{{ str_replace('_',' ',$u->role) }}</td>
                            <td class="px-4 py-3 text-sm">{{ $u->department->dept_name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button"
                                        @click="openEdit({
                                            id: {{ $u->user_id }},
                                            username: @js($u->username),
                                            full_name: @js($u->full_name),
                                            email: @js($u->email),
                                            role: @js($u->role),
                                            department_id: @js($u->department_id)
                                        })"
                                        class="text-xs font-semibold text-brand-600 hover:text-brand-700 px-2 py-1">Edit</button>

                                <button type="button"
                                        @click="openChangePassword({{ $u->user_id }}, @js($u->full_name))"
                                        class="text-xs font-semibold text-amber-600 hover:text-amber-700 px-2 py-1">Change Password</button>

                                <button type="button"
                                        @click="openResetPassword({{ $u->user_id }}, @js($u->full_name))"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 px-2 py-1">Reset Password</button>

                                <button type="button"
                                        @click="openDelete({{ $u->user_id }}, @js($u->full_name))"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 px-2 py-1">Delete</button>
                            </td>
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

        {{-- ───────────── Edit User Modal ───────────── --}}
        <div x-show="modal === 'edit'" x-cloak x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.outside="modal = null">
                <h3 class="font-bold text-gray-900 mb-4">Edit User</h3>
                <form :action="`/admin/users/${form.id}/update`" method="POST" class="space-y-3">
                    @csrf
                    <input type="text" name="username" x-model="form.username" placeholder="Username" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <input type="text" name="full_name" x-model="form.full_name" placeholder="Full Name" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <input type="email" name="email" x-model="form.email" placeholder="Email" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <select name="role" x-model="form.role" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                        @foreach(['teacher','coordinator','technician','lead_technician','inventory_officer','admin'] as $r)
                            <option value="{{ $r }}">{{ ucwords(str_replace('_',' ',$r)) }}</option>
                        @endforeach
                    </select>
                    <select name="department_id" x-model="form.department_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                        <option value="">No department</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->dept_id }}">{{ $d->dept_name }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="modal = null"
                                class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 rounded-lg">Cancel</button>
                        <button type="submit"
                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-lg">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ───────────── Change Password Modal ───────────── --}}
        <div x-show="modal === 'change-password'" x-cloak x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.outside="modal = null">
                <h3 class="font-bold text-gray-900 mb-1">Change Password</h3>
                <p class="text-sm text-gray-500 mb-4">Set a new password for <span class="font-semibold" x-text="targetName"></span>.</p>
                <form :action="`/admin/users/${form.id}/change-password`" method="POST" class="space-y-3">
                    @csrf
                    <input type="password" name="password" placeholder="New Password (min 6)" required minlength="6"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <input type="password" name="password_confirmation" placeholder="Confirm New Password" required minlength="6"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="modal = null"
                                class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 rounded-lg">Cancel</button>
                        <button type="submit"
                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-lg">Change Password</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ───────────── Reset Password Confirmation ───────────── --}}
        <div x-show="modal === 'reset-password'" x-cloak x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.outside="modal = null">
                <h3 class="font-bold text-gray-900 mb-1">Reset Password</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Reset the password for <span class="font-semibold" x-text="targetName"></span> back to the default
                    (<code class="bg-gray-100 px-1 rounded">admin123</code>)?
                </p>
                <form :action="`/admin/users/${form.id}/reset-password`" method="POST" class="flex gap-2">
                    @csrf
                    <button type="button" @click="modal = null"
                            class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 rounded-lg">Cancel</button>
                    <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 rounded-lg">Reset Password</button>
                </form>
            </div>
        </div>

        {{-- ───────────── Delete User Confirmation ───────────── --}}
        <div x-show="modal === 'delete'" x-cloak x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.outside="modal = null">
                <h3 class="font-bold text-gray-900 mb-1">Delete User</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to delete <span class="font-semibold" x-text="targetName"></span>?
                    This action cannot be undone.
                </p>
                <form :action="`/admin/users/${form.id}/delete`" method="POST" class="flex gap-2">
                    @csrf
                    <button type="button" @click="modal = null"
                            class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 rounded-lg">Cancel</button>
                    <button type="submit"
                            class="flex-1 bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 rounded-lg">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function userManager() {
            return {
                modal: null,
                targetName: '',
                form: {
                    id: null,
                    username: '',
                    full_name: '',
                    email: '',
                    role: '',
                    department_id: '',
                },
                openEdit(user) {
                    this.form = {
                        id: user.id,
                        username: user.username,
                        full_name: user.full_name,
                        email: user.email,
                        role: user.role,
                        department_id: user.department_id ?? '',
                    };
                    this.targetName = user.full_name;
                    this.modal = 'edit';
                },
                openChangePassword(id, name) {
                    this.form.id = id;
                    this.targetName = name;
                    this.modal = 'change-password';
                },
                openResetPassword(id, name) {
                    this.form.id = id;
                    this.targetName = name;
                    this.modal = 'reset-password';
                },
                openDelete(id, name) {
                    this.form.id = id;
                    this.targetName = name;
                    this.modal = 'delete';
                },
            }
        }
    </script>
@endsection