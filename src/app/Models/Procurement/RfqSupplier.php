<?php

namespace App\Models\Procurement;

use App\Models\Master\BusinessPartner;
use Illuminate\Database\Eloquent\Model;

class RfqSupplier extends Model
{
    protected $table = 'procurement_rfq_suppliers';

    protected $fillable = ['rfq_id', 'business_partner_id', 'response_status'];

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function businessPartner()
    {
        return $this->belongsTo(BusinessPartner::class);
    }
}
