<?php

namespace App\Models\Inventory;

use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Warehouse;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsReceipt extends Model
{
    use HasAuditTrail, SoftDeletes;

    protected $table = 'inventory_goods_receipts';

    protected $fillable = [
        'company_id', 'number', 'purchase_order_id', 'business_partner_id',
        'warehouse_id', 'receipt_date', 'supplier_reference', 'status',
    ];

    protected $casts = ['receipt_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function businessPartner()
    {
        return $this->belongsTo(BusinessPartner::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function lines()
    {
        return $this->hasMany(GoodsReceiptLine::class);
    }
}
