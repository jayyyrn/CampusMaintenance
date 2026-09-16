<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TaskAssignment extends Model
{
    protected $primaryKey = 'assignment_id';
    protected $fillable = ['request_id','technician_id','assigned_by','status','assigned_at','started_at','completed_at','notes'];
    protected $casts = [
        'assigned_at'  => 'datetime',
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function request()    { return $this->belongsTo(MaintenanceRequest::class, 'request_id', 'request_id'); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id', 'user_id'); }
    public function assigner()   { return $this->belongsTo(User::class, 'assigned_by', 'user_id'); }

    public function statusColor(): string {
        return match($this->status) {
            'pending'     => 'bg-gray-100 text-gray-700',
            'in_progress' => 'bg-orange-100 text-orange-700',
            'for_review'  => 'bg-yellow-100 text-yellow-800',
            'completed'   => 'bg-green-100 text-green-700',
            default       => 'bg-gray-100 text-gray-700',
        };
    }
}