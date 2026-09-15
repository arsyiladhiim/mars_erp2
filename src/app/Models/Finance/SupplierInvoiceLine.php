<?php

namespace App\Models\Finance;

use App\Models\Master\Item;
use Illuminate\Database\Eloquent\Model;

class SupplierInvoiceLine extends Model
{
    protected $table = 'finance_supplier_invoice_lines';

    protected $fillable = [
        'supplier_invoice_id', 'item_id', 'description', 'quantity',
        'unit_price', 'tax_code_id', 'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function supplierInvoice()
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function taxCode()
    {
        return $this->belongsTo(TaxCode::class);
    }
}
