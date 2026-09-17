<?php

namespace App\Models\Sales;

use App\Models\Concerns\Approvable;
use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Branch;
use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesOrder extends Model
{
    use Approvable, GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

    protected $table = 'sales_orders';

    protected $fillable = [
        'company_id', 'branch_id', 'number', 'business_partner_id', 'sales_quotation_id',
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

    public function salesQuotation()
    {
        return $this->belongsTo(SalesQuotation::class);
    }

    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class);
    }

    public static function documentType(): string
    {
        return 'sales_order';
    }
}
