<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $primaryKey = 'transaction_id';
    protected $fillable = ['item_id','transaction_type','quantity','reference_no','request_id','performed_by','supplier','notes'];

    public function item()      { return $this->belongsTo(Inventory::class, 'item_id', 'item_id'); }
    public function performer() { return $this->belongsTo(User::class, 'performed_by', 'user_id'); }
}