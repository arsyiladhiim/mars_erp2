<?php

namespace App\Models\Sales;

use App\Models\Concerns\GeneratesDocumentNumber;
use App\Models\Concerns\HasAuditTrail;
use App\Models\Core\Branch;
use App\Models\Core\Company;
use App\Models\Master\BusinessPartner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalesQuotation extends Model
{
    use GeneratesDocumentNumber, HasAuditTrail, SoftDeletes;

    protected $table = 'sales_quotations';

    protected $fillable = [
        'company_id', 'branch_id', 'number', 'business_partner_id', 'quotation_date',
        'validity_date', 'terms', 'currency', 'subtotal', 'tax_total', 'grand_total', 'status',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'validity_date' => 'date',
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

    public function lines()
    {
        return $this->hasMany(SalesQuotationLine::class);
    }

    public static function documentType(): string
    {
        return 'sales_quotation';
    }
}
