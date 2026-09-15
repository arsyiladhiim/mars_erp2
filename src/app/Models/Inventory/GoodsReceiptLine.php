<?php

namespace App\Models\Inventory;

use App\Models\Master\Item;
use App\Models\Master\Uom;
use App\Models\Procurement\PurchaseOrderLine;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptLine extends Model
{
    protected $table = 'inventory_goods_receipt_lines';

    protected $fillable = [
        'goods_receipt_id', 'purchase_order_line_id', 'item_id', 'quantity',
        'uom_id', 'unit_cost', 'batch_number', 'expiry_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function purchaseOrderLine()
    {
        return $this->belongsTo(PurchaseOrderLine::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function uom()
    {
        return $this->belongsTo(Uom::class);
    }
}
