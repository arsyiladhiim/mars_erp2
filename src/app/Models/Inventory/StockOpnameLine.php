<?php

namespace App\Models\Inventory;

use App\Models\Master\Item;
use Illuminate\Database\Eloquent\Model;

class StockOpnameLine extends Model
{
    protected $table = 'inventory_stock_opname_lines';

    protected $fillable = [
        'stock_opname_id', 'item_id', 'system_quantity', 'physical_quantity', 'variance_quantity',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:4',
        'physical_quantity' => 'decimal:4',
        'variance_quantity' => 'decimal:4',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
