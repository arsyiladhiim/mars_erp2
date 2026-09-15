<?php

namespace App\Models\Procurement;

use App\Models\Finance\TaxCode;
use App\Models\Master\Item;
use App\Models\Master\Uom;
use Illuminate\Database\Eloquent\Model;

class SupplierQuotationLine extends Model
{
    protected $table = 'procurement_supplier_quotation_lines';

    protected $fillable = [
        'supplier_quotation_id', 'item_id', 'description', 'quantity', 'uom_id',
        'unit_price', 'discount_percent', 'tax_code_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:3',
    ];

    public function supplierQuotation()
    {
        return $this->belongsTo(SupplierQuotation::class);
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
