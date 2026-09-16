<?php

namespace App\Models\Procurement;

use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Branch;
use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasAuditTrail, SoftDeletes;

    protected $table = 'procurement_purchase_orders';

    protected $fillable = [
        'company_id', 'branch_id', 'number', 'business_partner_id', 'supplier_quotation_id',
        'warehouse_id', 'order_date', 'delivery_date', 'payment_term_days', 'currency',
        'subtotal', 'tax_total', 'grand_total', 'status',
    ];

    protected $casts = [
        'order_date' => 'date',
        'delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function businessPartner()
    {
        return $this->belongsTo(BusinessPartner::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function supplierQuotation()
    {
        return $this->belongsTo(SupplierQuotation::class);
    }

    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }
}
