<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $primaryKey = 'dept_id';
    protected $fillable = ['dept_name','dept_code'];
    public function users()    { return $this->hasMany(User::class, 'department_id', 'dept_id'); }
    public function requests() { return $this->hasMany(MaintenanceRequest::class, 'department_id', 'dept_id'); }
}