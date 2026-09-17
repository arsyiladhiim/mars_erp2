<?php

namespace App\Models\Sales;

use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerInvoice extends Model
{
    use GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

    protected $table = 'sales_customer_invoices';

    protected $fillable = [
        'company_id', 'number', 'business_partner_id', 'sales_order_id', 'delivery_id',
        'invoice_date', 'due_date', 'currency', 'subtotal', 'tax_total', 'grand_total',
        'paid_amount', 'status',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function businessPartner()
    {
        return $this->belongsTo(BusinessPartner::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function lines()
    {
        return $this->hasMany(CustomerInvoiceLine::class);
    }

    public function getOutstandingAttribute(): float
    {
        return (float) $this->grand_total - (float) $this->paid_amount;
    }

    public static function documentType(): string
    {
        return 'customer_invoice';
    }
}
