<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Department, AuditLog};

class AdminController extends Controller
{
    /**
     * Default password used when Admin clicks "Reset Password".
     * Hashed automatically by the User model's `password => hashed` cast.
     */
    private const DEFAULT_PASSWORD = 'admin123';

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

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'username'      => 'required|string|max:50|unique:users,username,' . $user->user_id . ',user_id',
            'full_name'     => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'role'          => 'required|in:teacher,coordinator,technician,lead_technician,inventory_officer,admin',
            'department_id' => 'nullable|exists:departments,dept_id',
        ]);

        $user->update($data);

        audit('UPDATE_USER', 'user', $user->user_id, $user->username);

        return back()->with('success', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        // Safety: Admin cannot delete their own account
        if (auth()->id() === $user->user_id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $username = $user->username;
        $userId   = $user->user_id;
        $user->delete();

        audit('DELETE_USER', 'user', $userId, $username);

        return back()->with('success', 'User deleted successfully.');
    }

    public function changePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        // The User model casts `password => hashed`, so plain string is auto-hashed.
        $user->password = $request->password;
        $user->save();

        audit('CHANGE_PASSWORD', 'user', $user->user_id, $user->username);

        return back()->with('success', 'Password changed successfully.');
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);

        // The User model casts `password => hashed`, so plain string is auto-hashed.
        $user->password = self::DEFAULT_PASSWORD;
        $user->save();

        audit('RESET_PASSWORD', 'user', $user->user_id, $user->username);

        return back()->with('success', 'Password reset to default successfully.');
    }

    public function auditLogs()
    {
        $logs = AuditLog::with('user')->latest()->paginate(50);
        return view('admin.audit_logs', compact('logs'));
    }
}