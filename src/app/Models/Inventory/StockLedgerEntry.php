<?php

namespace App\Models\Inventory;

use App\Models\Master\Item;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;

class StockLedgerEntry extends Model
{
    public $timestamps = false;

    protected $table = 'inventory_stock_ledger';

    protected $fillable = [
        'item_id', 'warehouse_id', 'movement_type', 'source_type', 'source_id',
        'quantity_in', 'quantity_out', 'unit_cost', 'balance_quantity', 'balance_value',
        'batch_number', 'movement_date', 'created_at',
    ];

    protected $casts = [
        'quantity_in' => 'decimal:4',
        'quantity_out' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'balance_quantity' => 'decimal:4',
        'balance_value' => 'decimal:2',
        'movement_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function source()
    {
        return $this->morphTo();
    }
}
