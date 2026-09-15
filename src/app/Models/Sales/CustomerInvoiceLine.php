<?php

namespace App\Models\Sales;

use App\Models\Finance\TaxCode;
use App\Models\Master\Item;
use Illuminate\Database\Eloquent\Model;

class CustomerInvoiceLine extends Model
{
    protected $table = 'sales_customer_invoice_lines';

    protected $fillable = [
        'customer_invoice_id', 'item_id', 'description', 'quantity',
        'unit_price', 'tax_code_id', 'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function customerInvoice()
    {
        return $this->belongsTo(CustomerInvoice::class);
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
