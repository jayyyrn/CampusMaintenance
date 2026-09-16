<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $primaryKey = 'user_id';
    protected $fillable = ['username','full_name','email','password','role','department_id','status'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['password' => 'hashed'];

    public function department() { return $this->belongsTo(Department::class, 'department_id', 'dept_id'); }
    public function requests()   { return $this->hasMany(MaintenanceRequest::class, 'teacher_id', 'user_id'); }
    public function tasks()      { return $this->hasMany(TaskAssignment::class, 'technician_id', 'user_id'); }
    public function notifications() { return $this->hasMany(Notification::class, 'user_id', 'user_id'); }

    public function isTeacher()          { return $this->role === 'teacher'; }
    public function isTechnician()       { return in_array($this->role, ['technician','lead_technician']); }
    public function isSupervisor()       { return in_array($this->role, ['lead_technician','coordinator','admin']); }
    public function isInventoryOfficer() { return $this->role === 'inventory_officer'; }  // ✅ admin removed
    public function isAdmin()            { return $this->role === 'admin'; }
    public function isCoordinator()      { return $this->role === 'coordinator'; }
}