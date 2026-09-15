<?php

namespace App\Models\Inventory;

use App\Models\Master\Item;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentLine extends Model
{
    protected $table = 'inventory_stock_adjustment_lines';

    protected $fillable = ['stock_adjustment_id', 'item_id', 'quantity', 'unit_cost'];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
    ];

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
