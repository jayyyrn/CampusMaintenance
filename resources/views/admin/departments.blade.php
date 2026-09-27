@extends('layouts.app')
@section('title', 'Departments')
@section('content')
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Manage Departments</h1>

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

    <div x-data="deptManager()" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- List --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase text-left">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Users</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($departments as $d)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $d->dept_name }}</td>
                            <td class="px-4 py-3 text-sm font-mono">{{ $d->dept_code }}</td>
                            <td class="px-4 py-3 text-sm">{{ $d->users_count }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <button type="button"
                                        @click="openEdit({
                                            id: {{ $d->dept_id }},
                                            dept_name: @js($d->dept_name),
                                            dept_code: @js($d->dept_code)
                                        })"
                                        class="text-xs font-semibold text-brand-600 hover:text-brand-700 px-2 py-1">Edit</button>

                                <button type="button"
                                        @click="openDelete({{ $d->dept_id }}, @js($d->dept_name))"
                                        class="text-xs font-semibold text-rose-600 hover:text-rose-700 px-2 py-1">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-8 text-gray-400">No departments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Add --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-bold text-gray-900 mb-4">Add Department</h2>
            <form method="POST" action="{{ route('admin.departments.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="dept_name" placeholder="Department Name" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                <input type="text" name="dept_code" placeholder="Code (e.g., IT, ENG)" required maxlength="20"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono uppercase">
                <button class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-lg">
                    Create Department
                </button>
            </form>
        </div>

        {{-- Edit Modal --}}
        <div x-show="modal === 'edit'" x-cloak x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.outside="modal = null">
                <h3 class="font-bold text-gray-900 mb-4">Edit Department</h3>
                <form :action="`/admin/departments/${form.id}/update`" method="POST" class="space-y-3">
                    @csrf
                    <input type="text" name="dept_name" x-model="form.dept_name" placeholder="Department Name" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg">
                    <input type="text" name="dept_code" x-model="form.dept_code" placeholder="Code" required maxlength="20"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono uppercase">
                    <div class="flex gap-2 pt-2">
                        <button type="button" @click="modal = null"
                                class="flex-1 border border-gray-300 text-gray-700 font-semibold py-2.5 rounded-lg">Cancel</button>
                        <button type="submit"
                                class="flex-1 bg-orange-500 hover:bg-orange-600 text-white font-bold py-2.5 rounded-lg">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete Modal --}}
        <div x-show="modal === 'delete'" x-cloak x-transition
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6" @click.outside="modal = null">
                <h3 class="font-bold text-gray-900 mb-1">Delete Department</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Are you sure you want to delete <span class="font-semibold" x-text="targetName"></span>?
                </p>
                <form :action="`/admin/departments/${form.id}/delete`" method="POST" class="flex gap-2">
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
        function deptManager() {
            return {
                modal: null,
                targetName: '',
                form: { id: null, dept_name: '', dept_code: '' },
                openEdit(d) {
                    this.form = { id: d.id, dept_name: d.dept_name, dept_code: d.dept_code };
                    this.modal = 'edit';
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