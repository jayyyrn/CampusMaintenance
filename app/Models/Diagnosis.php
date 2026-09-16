<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Diagnosis extends Model
{
    protected $primaryKey = 'diagnosis_id';
    protected $fillable = [
        'request_id','technician_id','findings','recommended_action',
        'materials_needed','diagnosis_result','solution_steps',
        'is_verified','verified_by','verified_at'
    ];
    protected $casts = [
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function request()    { return $this->belongsTo(MaintenanceRequest::class, 'request_id', 'request_id'); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id', 'user_id'); }
    public function verifier()   { return $this->belongsTo(User::class, 'verified_by', 'user_id'); }
}