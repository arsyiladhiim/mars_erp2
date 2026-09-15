<?php

namespace App\Models\Procurement;

use App\Models\Master\Item;
use App\Models\Master\Uom;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestLine extends Model
{
    protected $table = 'procurement_purchase_request_lines';

    protected $fillable = [
        'purchase_request_id', 'item_id', 'description', 'quantity',
        'uom_id', 'estimated_price', 'attachment_path',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'estimated_price' => 'decimal:2',
    ];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
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
