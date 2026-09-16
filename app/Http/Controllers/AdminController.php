<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Department, AuditLog};

class AdminController extends Controller
{
    public function users()
    {
        $users = User::with('department')->orderBy('full_name')->paginate(20);
        $departments = Department::all();
        return view('admin.users', compact('users', 'departments'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'username'      => 'required|string|max:50|unique:users,username',
            'full_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email',
            'role'          => 'required|in:teacher,coordinator,technician,lead_technician,inventory_officer,admin',
            'department_id' => 'nullable|exists:departments,dept_id',
            'password'      => 'required|string|min:6',
        ]);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        audit('CREATE_USER', 'user', $user->user_id, $data['username']);

        return back()->with('success', 'User created successfully.');
    }

    public function auditLogs()
    {
        $logs = AuditLog::with('user')->latest()->paginate(50);
        return view('admin.audit_logs', compact('logs'));
    }
}