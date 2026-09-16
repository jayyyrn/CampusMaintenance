<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $primaryKey = 'mat_req_id';
    protected $fillable = [
        'request_id','technician_id','item_id','quantity_requested','quantity_released',
        'quantity_returned','status','approved_by','released_by','requested_at',
        'approved_at','released_at','notes'
    ];
    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at'  => 'datetime',
        'released_at'  => 'datetime',
    ];

    public function request()    { return $this->belongsTo(MaintenanceRequest::class, 'request_id', 'request_id'); }
    public function technician() { return $this->belongsTo(User::class, 'technician_id', 'user_id'); }
    public function item()       { return $this->belongsTo(Inventory::class, 'item_id', 'item_id'); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by', 'user_id'); }
    public function releaser()   { return $this->belongsTo(User::class, 'released_by', 'user_id'); }
}