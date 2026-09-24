<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $primaryKey = 'request_id';
    protected $fillable = [
    'request_code','teacher_id','equipment_id','department_id','category',
    'title','description','location','priority','status','queue_position',
    'photo_before','photo_after','date_reported','date_completed',
    'unit_no','tools_and_materials','estimated_budget','date_start','date_finish',
    'custom_category',
];
    protected $casts = [
        'date_reported'  => 'datetime',
        'date_completed' => 'datetime',
    ];

    public function teacher()    { return $this->belongsTo(User::class, 'teacher_id', 'user_id'); }
    public function department() { return $this->belongsTo(Department::class, 'department_id', 'dept_id'); }
    public function equipment()  { return $this->belongsTo(Equipment::class, 'equipment_id', 'equipment_id'); }
    public function assignments(){ return $this->hasMany(TaskAssignment::class, 'request_id', 'request_id'); }
    public function diagnoses()  { return $this->hasMany(Diagnosis::class, 'request_id', 'request_id'); }
    public function materialRequests() { return $this->hasMany(MaterialRequest::class, 'request_id', 'request_id'); }

    public function activeAssignment()
    {
        return $this->hasOne(TaskAssignment::class, 'request_id', 'request_id')
                    ->whereIn('status', ['pending','in_progress','for_review'])
                    ->latest('assigned_at');
    }

    // ✅ Order by priority: urgent → high → medium → low
    public function scopeByPriority($q)
    {
        return $q->orderByRaw("CASE priority 
                                WHEN 'urgent' THEN 1 
                                WHEN 'high'   THEN 2 
                                WHEN 'medium' THEN 3 
                                WHEN 'low'    THEN 4 
                                ELSE 5 END");
    }

    // ✅ Only active requests
    public function scopeActive($q)
    {
        return $q->whereNotIn('status', ['completed','cancelled']);
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'pending'          => 'bg-gray-100 text-gray-700',
            'review'           => 'bg-blue-100 text-blue-700',
            'assigned'         => 'bg-purple-100 text-purple-700',
            'in_progress'      => 'bg-orange-100 text-orange-700',
            'for_verification' => 'bg-yellow-100 text-yellow-800',
            'completed'        => 'bg-green-100 text-green-700',
            'cancelled'        => 'bg-red-100 text-red-700',
            default            => 'bg-gray-100 text-gray-700',
        };
    }

    public function priorityColor(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-red-100 text-red-700',
            'high'   => 'bg-orange-100 text-orange-700',
            'medium' => 'bg-blue-100 text-blue-700',
            'low'    => 'bg-gray-100 text-gray-600',
            default  => 'bg-gray-100 text-gray-600',
        };
    }

    public function statusLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->status ?? 'unknown'));
    }
}