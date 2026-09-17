<?php

namespace App\Models\Sales;

use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use App\Models\Master\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

    protected $table = 'sales_deliveries';

    protected $fillable = [
        'company_id', 'number', 'sales_order_id', 'business_partner_id',
        'warehouse_id', 'delivery_date', 'tracking_number', 'status',
    ];

    protected $casts = ['delivery_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
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
        return $this->hasMany(DeliveryLine::class);
    }

    public static function documentType(): string
    {
        return 'delivery';
    }
}
