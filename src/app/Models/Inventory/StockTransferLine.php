<?php

namespace App\Models\Inventory;

use App\Models\Master\Item;
use Illuminate\Database\Eloquent\Model;

class StockTransferLine extends Model
{
    protected $table = 'inventory_stock_transfer_lines';

    protected $fillable = ['stock_transfer_id', 'item_id', 'quantity', 'batch_number'];

    protected $casts = ['quantity' => 'decimal:4'];

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
