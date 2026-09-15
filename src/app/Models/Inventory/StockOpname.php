<?php

namespace App\Models\Inventory;

use App\Models\Core\Company;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpname extends Model
{
    use SoftDeletes;

    protected $table = 'inventory_stock_opnames';

    protected $fillable = ['company_id', 'number', 'warehouse_id', 'count_date', 'status'];

    protected $casts = ['count_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(StockOpnameLine::class);
    }
}
