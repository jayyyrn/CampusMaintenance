<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, DashboardController, RequestController, TaskController,
    InventoryController, AiAssistantController, QueueController,
    NotificationController, AdminController
};

Route::get('/', fn() => redirect()->route('login'));

// Auth
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout',[AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Requests
    Route::get('/requests',         [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create',  [RequestController::class, 'create'])->name('requests.create')->middleware('role:teacher');
    Route::post('/requests',        [RequestController::class, 'store'])->name('requests.store')->middleware('role:teacher');
    Route::get('/requests/{id}',    [RequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{id}/assign', [RequestController::class, 'assign'])->name('requests.assign')->middleware('role:coordinator,lead_technician,admin');
    Route::post('/requests/{id}/status', [RequestController::class, 'updateStatus'])->name('requests.status')->middleware('role:coordinator,lead_technician,admin,technician');
    Route::post('/diagnoses/{id}/verify', [RequestController::class, 'verifyDiagnosis'])->name('diagnoses.verify')->middleware('role:lead_technician,admin');

    // Tasks (technician board)
    Route::get('/tasks',            [TaskController::class, 'index'])->name('tasks.index')->middleware('role:technician,lead_technician');
    Route::get('/tasks/{id}',       [TaskController::class, 'show'])->name('tasks.show')->middleware('role:technician,lead_technician');
    Route::post('/tasks/{id}',      [TaskController::class, 'update'])->name('tasks.update')->middleware('role:technician,lead_technician');
    Route::post('/tasks/{id}/diagnosis', [TaskController::class, 'saveDiagnosis'])->name('tasks.diagnosis')->middleware('role:technician,lead_technician');
    Route::post('/tasks/{id}/move', [TaskController::class, 'move'])->name('tasks.move')->middleware('role:technician,lead_technician');

    // Inventory
    Route::get('/inventory',                 [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/material-requests',[InventoryController::class, 'materialRequests'])->name('inventory.material_requests');
    Route::post('/inventory/request',        [InventoryController::class, 'requestMaterial'])->name('inventory.request')->middleware('role:technician,lead_technician');
    Route::post('/inventory/{id}/approve',   [InventoryController::class, 'approveMaterial'])->name('inventory.approve')->middleware('role:lead_technician,inventory_officer,admin');
    Route::post('/inventory/{id}/release',   [InventoryController::class, 'releaseMaterial'])->name('inventory.release')->middleware('role:inventory_officer,admin');
    Route::post('/inventory/{id}/return',    [InventoryController::class, 'returnMaterial'])->name('inventory.return')->middleware('role:technician,lead_technician,inventory_officer,admin');
    Route::post('/inventory/stock-in',       [InventoryController::class, 'stockIn'])->name('inventory.stock_in')->middleware('role:inventory_officer,admin');
    Route::get('/inventory/transactions',    [InventoryController::class, 'transactions'])->name('inventory.transactions')->middleware('role:inventory_officer,admin');

    // AI Assistant
    Route::get('/assistant',       [AiAssistantController::class, 'index'])->name('assistant.index');
    Route::post('/assistant/ask',  [AiAssistantController::class, 'ask'])->name('assistant.ask');

    // Public queue
    Route::get('/queue', [QueueController::class, 'index'])->name('queue.index');

    // Notifications
    Route::get('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read',[NotificationController::class, 'markRead'])->name('notifications.mark_read');
    Route::post('/notifications/{id}/read',[NotificationController::class, 'markRead'])->name('notifications.read');

    // Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users',                    [AdminController::class, 'users'])->name('admin.users');
        Route::post('/admin/users',                   [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::post('/admin/users/{id}/update',       [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/admin/users/{id}/delete',       [AdminController::class, 'deleteUser'])->name('admin.users.delete');
        Route::post('/admin/users/{id}/change-password', [AdminController::class, 'changePassword'])->name('admin.users.change_password');
        Route::post('/admin/users/{id}/reset-password',  [AdminController::class, 'resetPassword'])->name('admin.users.reset_password');
        Route::get('/admin/audit-logs',               [AdminController::class, 'auditLogs'])->name('admin.audit_logs');
    });
});