<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'item_id';
    protected $fillable = ['item_code','item_name','category','unit','qty_on_hand','min_stock_level','unit_cost','location'];

    public function materialRequests() { return $this->hasMany(MaterialRequest::class, 'item_id', 'item_id'); }
    public function transactions()     { return $this->hasMany(StockTransaction::class, 'item_id', 'item_id'); }

    public function isLowStock(): bool { return $this->qty_on_hand <= $this->min_stock_level; }
}