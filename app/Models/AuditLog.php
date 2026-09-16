<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $primaryKey = 'log_id';
    protected $fillable = ['user_id','action','entity_type','entity_id','details','ip_address'];
    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }
}