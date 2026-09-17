<?php

namespace App\Models\Finance;

use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use App\Models\Inventory\GoodsReceipt;
use App\Models\Master\BusinessPartner;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierInvoice extends Model
{
    use GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

    protected $table = 'finance_supplier_invoices';

    protected $fillable = [
        'company_id', 'number', 'supplier_invoice_number', 'business_partner_id',
        'purchase_order_id', 'goods_receipt_id', 'invoice_date', 'due_date', 'currency',
        'subtotal', 'tax_total', 'grand_total', 'paid_amount', 'status',
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

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function lines()
    {
        return $this->hasMany(SupplierInvoiceLine::class);
    }

    public function getOutstandingAttribute(): float
    {
        return (float) $this->grand_total - (float) $this->paid_amount;
    }

    public static function documentType(): string
    {
        return 'supplier_invoice';
    }
}
