<?php

namespace App\Models\Sales;

use App\Models\Finance\TaxCode;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use Illuminate\Database\Eloquent\Model;

class SalesQuotationLine extends Model
{
    protected $table = 'sales_quotation_lines';

    protected $fillable = [
        'sales_quotation_id', 'item_id', 'description', 'quantity', 'uom_id',
        'unit_price', 'discount_percent', 'tax_code_id', 'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:3',
        'line_total' => 'decimal:2',
    ];

    public function salesQuotation()
    {
        return $this->belongsTo(SalesQuotation::class);
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
