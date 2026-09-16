<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $primaryKey = 'equipment_id';
    protected $fillable = ['asset_no','equipment_name','category','location','department_id','status'];
    public function department() { return $this->belongsTo(Department::class, 'department_id', 'dept_id'); }
    public function requests()   { return $this->hasMany(MaintenanceRequest::class, 'equipment_id', 'equipment_id'); }
}