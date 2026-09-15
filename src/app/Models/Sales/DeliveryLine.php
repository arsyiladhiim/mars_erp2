<?php

namespace App\Models\Sales;

use App\Models\Master\Item;
use Illuminate\Database\Eloquent\Model;

class DeliveryLine extends Model
{
    protected $table = 'sales_delivery_lines';

    protected $fillable = ['delivery_id', 'sales_order_line_id', 'item_id', 'quantity', 'batch_number'];

    protected $casts = ['quantity' => 'decimal:4'];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function salesOrderLine()
    {
        return $this->belongsTo(SalesOrderLine::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
