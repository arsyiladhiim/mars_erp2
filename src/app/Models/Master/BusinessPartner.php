<?php

namespace App\Models\Master;

use App\Models\Finance\TaxCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessPartner extends Model
{
    use SoftDeletes;

    protected $table = 'master_business_partners';

    protected $fillable = [
        'code', 'name', 'type', 'tax_id', 'email', 'phone', 'billing_address',
        'shipping_address', 'payment_term_days', 'credit_limit', 'currency',
        'default_tax_code_id', 'contact_person', 'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function defaultTaxCode()
    {
        return $this->belongsTo(TaxCode::class, 'default_tax_code_id');
    }

    public function scopeCustomers($query)
    {
        return $query->whereIn('type', ['customer', 'both']);
    }

    public function scopeSuppliers($query)
    {
        return $query->whereIn('type', ['supplier', 'both']);
    }
}
