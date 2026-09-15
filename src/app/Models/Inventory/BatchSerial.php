<?php

namespace App\Models\Inventory;

use App\Models\Master\Item;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;

class BatchSerial extends Model
{
    protected $table = 'inventory_batch_serials';

    protected $fillable = [
        'item_id', 'warehouse_id', 'type', 'batch_number', 'serial_number',
        'quantity', 'manufacture_date', 'expiry_date', 'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'manufacture_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
