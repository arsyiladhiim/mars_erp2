<?php

namespace App\Models\Procurement;

use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierQuotation extends Model
{
    use SoftDeletes;

    protected $table = 'procurement_supplier_quotations';

    protected $fillable = [
        'company_id', 'number', 'rfq_id', 'business_partner_id', 'validity_date',
        'lead_time_days', 'payment_term_days', 'status',
    ];

    protected $casts = ['validity_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function businessPartner()
    {
        return $this->belongsTo(BusinessPartner::class);
    }

    public function lines()
    {
        return $this->hasMany(SupplierQuotationLine::class);
    }
}
