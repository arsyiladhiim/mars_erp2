<?php

namespace App\Models\Procurement;

use App\Models\Finance\TaxCode;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLine extends Model
{
    protected $table = 'procurement_purchase_order_lines';

    protected $fillable = [
        'purchase_order_id', 'item_id', 'description', 'quantity', 'uom_id',
        'unit_price', 'discount_percent', 'tax_code_id', 'line_total', 'received_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:3',
        'line_total' => 'decimal:2',
        'received_quantity' => 'decimal:4',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
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
