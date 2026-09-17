<?php

namespace App\Models\Procurement;

use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

    protected $table = 'procurement_rfqs';

    protected $fillable = ['company_id', 'number', 'purchase_request_id', 'required_date', 'terms', 'status'];

    protected $casts = ['required_date' => 'date'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function suppliers()
    {
        return $this->hasMany(RfqSupplier::class);
    }

    public function lines()
    {
        return $this->hasMany(RfqLine::class);
    }

    public static function documentType(): string
    {
        return 'rfq';
    }
}
