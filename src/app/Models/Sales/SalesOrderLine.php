<?php

namespace App\Models\Sales;

use App\Models\Finance\TaxCode;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use Illuminate\Database\Eloquent\Model;

class SalesOrderLine extends Model
{
    protected $table = 'sales_order_lines';

    protected $fillable = [
        'sales_order_id', 'item_id', 'description', 'quantity', 'uom_id',
        'unit_price', 'discount_percent', 'tax_code_id', 'line_total', 'delivered_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:3',
        'line_total' => 'decimal:2',
        'delivered_quantity' => 'decimal:4',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class);
    }

    public function taxCode()
    {
        return $this->belongsTo(TaxCode::class);
    }
}
